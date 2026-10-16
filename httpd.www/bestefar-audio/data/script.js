// ── Shareable-link support ──────────────────────────────────────────────
// Mirrors the same pattern already used for image-music and image-quiz:
// localStorage is checked FIRST (instant, no network — this is what makes
// internal Preview smooth), verified against ?show=<id> so a stale cache
// from a DIFFERENT show/link is never silently reused, falling back to a
// fresh API fetch by ID, and finally to the static content.json file as
// a last resort for standalone/offline use.
//
// The real API returns { quizForms: [...] } — an ARRAY of screen objects
// keyed by `page`. Every consumer in this file expects an OBJECT keyed by
// "screen1", "screen2", etc. (matching data.screenN lookups throughout).
// That conversion happens once, here, so nothing downstream needs to
// change.
const AQ_STORAGE_KEY = "aq_preview_json";
const AQ_API_BASE = "https://api.bestefar.no/api/audio-quiz";

// ── Always enter through the title screen ──────────────────────────────
// A shared link is only ever supposed to point at index.html (the title
// screen) — but someone mid-quiz can end up copying the address bar's
// CURRENT url instead of using the dedicated Share button (every internal
// "next" click rewrites the url to that screen's file, e.g.
// data/screens/show.html?show=<id>). Sharing THAT drops the recipient
// straight into the middle of the quiz instead of the start.
//
// navigateToScreen() (below) marks every internal hop with a one-shot
// sessionStorage flag right before navigating. If a deep screen file
// loads WITHOUT that flag present, it wasn't reached by clicking through
// the quiz — it was a fresh typed/pasted/bookmarked entry — so send it
// back to the title screen instead. The flag is consumed (removed)
// immediately so browser back/forward and refresh on a mid-quiz page
// still behave like a normal page (no flag → would redirect) is the one
// accepted tradeoff: back/forward on a deep screen also lands on the
// title screen rather than resuming mid-quiz, which is still correct
// for how this quiz is meant to be taken (start to finish, not resumed).
(function guardDirectDeepLinkEntry() {
  try {
    const onDeepScreen = window.location.pathname.includes("/data/screens/");
    if (!onDeepScreen) return;
    const cameFromInternalNav =
      sessionStorage.getItem("aq_internal_nav") === "1";
    sessionStorage.removeItem("aq_internal_nav");
    if (!cameFromInternalNav) {
      window.location.replace("../../index.html" + window.location.search);
    }
  } catch {}
})();

let _audioQuizDataPromise = null;

function getAudioQuizJsonPath() {
  return window.location.pathname.includes("/data/screens/")
    ? "../content/content.json"
    : "./data/content/content.json";
}

function quizFormsArrayToScreenMap(quizForms, quizName) {
  const map = { quizName: quizName || "" };
  (quizForms || []).forEach((f) => {
    if (f && f.page != null) {
      map[`screen${f.page}`] = { ...f, quizName: quizName || f.quizName || "" };
    }
  });
  return map;
}

// True only if `data` actually has at least one screenN entry. A response
// (or a cached copy of one) that resolves successfully but carries no
// screens — seen intermittently from the API, cause not yet identified —
// used to get treated as valid data and cached forever under the show's
// id, so every later load of that same quiz reused the broken, empty
// result (matching id ⇒ never treated as stale) instead of ever retrying.
// That's why the bug came and went unpredictably, including on the same
// device/browser: whichever load happened to hit it first poisoned every
// load after it until localStorage was cleared by hand.
function hasScreenData(data) {
  return !!data && Object.keys(data).some((k) => /^screen\d+$/.test(k));
}

// Calling .pause() on an HTMLMediaElement while a previous .play() call is
// still pending (still buffering/starting up — play() is asynchronous) does
// NOT reliably stop playback in every browser: the delayed play() can still
// audibly start even after pause() was already called, leaving the UI
// showing "stopped" while sound keeps playing. Used everywhere in this file
// that plays/pauses audio, instead of calling .play()/.pause() directly, so
// a pause is never issued until any in-flight play() has actually settled.
function safePlay(audioEl) {
  const p = audioEl.play();
  audioEl._pendingPlay = p && p.catch ? p.catch(() => {}) : null;
  return p;
}

function safePause(audioEl) {
  if (audioEl._pendingPlay) {
    audioEl._pendingPlay.then(() => audioEl.pause());
  } else {
    audioEl.pause();
  }
}

// Single shared loader — called by every consumer in this file instead of
// each fetching content.json independently. Resolves to the SAME object
// shape content.json always provided ({ screen1: {...}, screen2: {...} }),
// so no other code in this file needs to change.
// Some mobile networks / carrier proxies silently swallow a cross-origin
// request instead of erroring it — the connection just never completes,
// so a bare `fetch()` hangs forever and the page is stuck on "Loading..."
// with no error ever reaching a .catch(). Race every fetch against a hard
// timeout so a stalled request fails fast and the static content.json
// fallback (or the "could not load" message) actually gets a chance to run.
function fetchWithTimeout(url, ms = 8000) {
  const controller = new AbortController();
  const timer = setTimeout(() => controller.abort(), ms);
  return fetch(url, { signal: controller.signal }).finally(() =>
    clearTimeout(timer)
  );
}

function loadAudioQuizData() {
  if (_audioQuizDataPromise) return _audioQuizDataPromise;

  _audioQuizDataPromise = (async () => {
    // Primary: standard query-string parsing. Fallback: some in-app
    // browsers (WhatsApp/Instagram/Messenger link previews) or a stray
    // copy-paste can mangle location.search (extra whitespace, encoding,
    // a "show" param that ends up outside the recognized query string) —
    // in that case fall back to a plain regex over the full URL so a
    // technically-malformed but still-present ?show=<id> still works.
    let expectedId = (new URLSearchParams(location.search).get("show") || "").trim();
    if (!expectedId) {
      const m = window.location.href.match(/[?&]show=([^&#\s]+)/);
      if (m) {
        try {
          expectedId = decodeURIComponent(m[1]).trim();
        } catch {
          expectedId = m[1].trim();
        }
      }
    }
    if (!expectedId) {
      window._aqErr = "no show id in URL (" + window.location.href + ")";
    }

    let data = null;
    try {
      const stored = JSON.parse(localStorage.getItem(AQ_STORAGE_KEY));
      if (stored) data = stored;
    } catch {}

    // Stale cache from a different show/link — discard, force a fresh fetch.
    if (expectedId && data && data.id && data.id !== expectedId) {
      data = null;
    }

    // Cached data for the RIGHT id but with no actual screens in it —
    // a previously-poisoned cache (see hasScreenData above). Discard and
    // force a fresh fetch instead of reusing it.
    if (data && !hasScreenData(data)) {
      data = null;
    }

    if (!data && expectedId) {
      try {
        const r = await fetchWithTimeout(
          `${AQ_API_BASE}/get-audio-quiz/${expectedId}`
        );
        if (!r.ok) window._aqErr = "API HTTP " + r.status;
        if (r.ok) {
          const json = await r.json();
          const mapped = quizFormsArrayToScreenMap(json.quizForms, json.quizName);
          mapped.id = json.id || expectedId;
          if (hasScreenData(mapped)) {
            data = mapped;
            try {
              localStorage.setItem(AQ_STORAGE_KEY, JSON.stringify(data));
            } catch {}
          } else {
            // Don't cache an empty result — let the next load retry the
            // API instead of getting stuck on this forever.
            window._aqErr =
              "API returned no screens for " + expectedId +
              " (quizForms: " + JSON.stringify(json.quizForms) + ")";
          }
        }
      } catch (err) {
        console.warn("Audio quiz API fetch failed/timed out:", err);
        window._aqErr = "API " + (err && err.name) + ": " + (err && err.message);
      }
    }

    if (!data) {
      try {
        const r = await fetchWithTimeout(getAudioQuizJsonPath());
        if (r.ok) data = await r.json();
      } catch (err) {
        console.warn("Audio quiz static content.json fetch failed:", err);
        window._aqErr = (window._aqErr || "") + " | static " + (err && err.name) + ": " + (err && err.message);
      }
    }

    return data || {};
  })();

  return _audioQuizDataPromise;
}

// Plain <a href="..."> links (not driven by navigateToScreen()) don't
// automatically carry the current ?show=<id> forward — e.g. screen.html's
// "Start quiz" link. Rewrite any same-origin, relative .html link on load
// to include the current query string, so a shared-link session survives
// navigation through links as well as onclick handlers, without having
// to edit every individual screen file's markup.
document.addEventListener("DOMContentLoaded", () => {
  const search = window.location.search;
  if (!search) return; // nothing to preserve (normal in-app flow)

  document.querySelectorAll('a[href$=".html"]').forEach((a) => {
    const href = a.getAttribute("href");
    if (href && !href.includes("?") && !href.startsWith("http")) {
      a.setAttribute("href", href + search);
    }
  });
});

// Same reasoning as navigateToScreen()'s markInternalNav — a plain <a>
// click is also a legitimate in-quiz hop, not a fresh external entry, so
// it needs to set the same flag before the browser follows the link.
document.addEventListener("click", (e) => {
  const a = e.target.closest('a[href$=".html"]');
  if (!a) return;
  try {
    sessionStorage.setItem("aq_internal_nav", "1");
  } catch {}
});

document.addEventListener("DOMContentLoaded", async function () {
  // ---------------------- Intro Overlay Logic -------------------------
  window.addEventListener("load", () => {
    const overlay = document.getElementById("introOverlay");
    setTimeout(() => {
      if (overlay) overlay.style.display = "none";
    }, 3000); // 3 seconds
  });

  // ---------------------- Fetch JSON Data and Initialize Screen -------------------------
  loadAudioQuizData()
    .then((data) => {
      console.log("JSON Data Loaded:", data);

      // Get the current screen ID (assuming it's in the body `id`)
      const screenId = document.body.id; // e.g., "screen1", "screen2"
      const screenData = data[screenId];

      // ---------------------- Type Label Mapping -------------------------
      // Map form value -> Norwegian label
      const TYPE_LABEL = { Music: "Melodi", Saying: "Stemme", Sound: "Lyd" };

      function labelForType(type) {
        // Accept "music"/"Music" etc, fall back to Melodi
        if (!type) return "Melodi";
        const key = String(type).trim().toLowerCase();
        if (key === "music") return TYPE_LABEL.Music;
        if (key === "saying") return TYPE_LABEL.Saying;
        if (key === "sound") return TYPE_LABEL.Sound;
        return "Melodi";
      }

      function screenIndexFromId(screenId) {
        // "screen12" -> 12 ; default 1
        const m = /screen(\d+)/i.exec(screenId || "");
        return m ? parseInt(m[1], 10) || 1 : 1;
      }

      console.log(screenId, screenData);

      if (screenData) {
        // ---------------------- Update Music Type Element -------------------------
        const musicTypeElement = document.getElementById("music-type-name");
        const typeLabel = labelForType(screenData.firmNaming);
        if (musicTypeElement) {
          musicTypeElement.textContent = typeLabel; // "Melodi"/"Stemme"/"Lyd"
        }

        // --- Dynamic "Spill av ..." line under the big play button ---
        function definiteFor(label) {
          // Convert the type label to a definite form for the sentence
          switch (label) {
            case "Melodi":
              return "melodien";
            case "Stemme":
              return "stemmen";
            case "Lyd":
              return "lyden";
            default:
              // safe fallback: lowercase the label
              return String(label || "").toLowerCase();
          }
        }

        const playInstructionEl = document.getElementById("play-instruction");
        if (playInstructionEl) {
          // Example: "Spill av melodien" | "Spill av stemmen" | "Spill av lyden"
          playInstructionEl.textContent = `Spill av ${definiteFor(typeLabel)}`;
        }

        // ---------------------- MUSIC Playback Logic -------------------------
        const musicUrl = screenData.musicFileUrl;
        const musicBtn = document.getElementById("playBtn1");
        const musicImg = document.getElementById("playImg1");
        const seekBar1 = document.getElementById("seekBar1");
        const musicAudio = new Audio(musicUrl);
        let musicPlaying = false;
        // Set only when the speaker (voice) interrupts music that was
        // actually playing — so music resumes automatically once the voice
        // stops, but a music track that was never playing doesn't start
        // itself just because the voice happened to play.
        let musicPausedForSpeaker = false;

        if (seekBar1) seekBar1.value = 0;

        // ---------------------- SPEAKER (Voice) Playback Logic -------------------------
        const speakerUrl = screenData.audioFileUrl;
        const speakerBtn = document.querySelector(".speaker-btn");
        const speakerImg = document.querySelector(".speaker-btn img");
        const speakerAudio = new Audio(speakerUrl);
        let speakerPlayedOnce = false;
        let speakerMuted = true;

        function playMusic() {
          safePlay(musicAudio);
          musicPlaying = true;
          if (musicImg) musicImg.src = "../assets/play-red2.png";
        }

        function pauseMusic() {
          safePause(musicAudio);
          musicPlaying = false;
          if (musicImg) musicImg.src = "../assets/play-red1.png";
        }

        // Music and voice are mutually exclusive and this is enforced via
        // the audio elements' own play/pause events (not just inside the
        // click handlers), so it holds regardless of which one triggers
        // playback. Voice always wins immediately; music resumes on its own
        // once the voice stops, whether it finished naturally or was paused.
        musicAudio.addEventListener("play", () => {
          if (speakerAudio && !speakerAudio.paused) {
            safePause(speakerAudio);
          }
        });

        speakerAudio.addEventListener("play", () => {
          if (musicPlaying) {
            musicPausedForSpeaker = true;
            pauseMusic();
          }
        });

        function resumeMusicIfInterruptedBySpeaker() {
          if (musicPausedForSpeaker) {
            musicPausedForSpeaker = false;
            playMusic();
          }
        }
        speakerAudio.addEventListener("pause", resumeMusicIfInterruptedBySpeaker);
        speakerAudio.addEventListener("ended", resumeMusicIfInterruptedBySpeaker);

        // ---------------------- Music Button Event Handler -------------------------
        if (musicBtn && musicImg) {
          musicBtn.addEventListener("click", (e) => {
            e.stopPropagation();
            // A deliberate tap on music always means "play this", so don't
            // let it auto-resume itself again once the voice next stops.
            musicPausedForSpeaker = false;
            if (musicPlaying) {
              pauseMusic();
            } else {
              playMusic();
            }
          });

          // Music audio event listeners
          musicAudio.addEventListener("timeupdate", () => {
            if (seekBar1 && musicAudio.duration) {
              seekBar1.value =
                (musicAudio.currentTime / musicAudio.duration) * 100;
            }
          });

          musicAudio.addEventListener("ended", () => {
            musicImg.src = "../assets/play-red1.png";
            musicPlaying = false;
            if (seekBar1) seekBar1.value = 0;
          });

          if (seekBar1) {
            seekBar1.addEventListener("input", () => {
              musicAudio.currentTime =
                (seekBar1.value / 100) * musicAudio.duration;
            });
          }
        }

        // ---------------------- Speaker Button Event Handler -------------------------
        if (speakerBtn && speakerImg) {
          speakerBtn.addEventListener("click", (e) => {
            e.stopPropagation();

            // Pause music immediately and synchronously on the click itself
            // — don't rely solely on speakerAudio's own 'play' event below.
            // If audioFileUrl is missing/invalid (e.g. no real quiz loaded),
            // speakerAudio.play() fails and that event never fires at all,
            // which used to mean music kept playing right through it.
            if (musicPlaying) {
              musicPausedForSpeaker = true;
              pauseMusic();
            }

            // First play or toggle
            if (!speakerPlayedOnce) {
              safePlay(speakerAudio);
              speakerImg.style.opacity = "1";
              speakerPlayedOnce = true;
              speakerMuted = false;
            } else {
              speakerMuted = !speakerMuted;
              if (speakerMuted) {
                safePause(speakerAudio);
                speakerImg.style.opacity = "0.7";
              } else {
                safePlay(speakerAudio);
                speakerImg.style.opacity = "1";
              }
            }
          });

          speakerAudio.addEventListener("ended", () => {
            speakerImg.style.opacity = "0.7";
            speakerMuted = true;
          });
        }

        // ---------------------- Update Quiz Name -------------------------
        const quizNameElement = document.getElementById("quizName");
        console.log(quizNameElement);
        if (quizNameElement) {
          quizNameElement.textContent =
            (data.screen1 && data.screen1.quizName) || data.quizName || "Default Quiz Name";

          // Clicking the quiz name advances to the instructions screen
          // ("Start quiz" lives there, which then goes to show.html).
          // navigateToScreen() automatically preserves ?show=<id> if
          // present, so a shared-link session keeps resolving correctly.
          quizNameElement.addEventListener("click", (e) => {
            e.stopPropagation();
            const base = window.location.pathname.includes("/data/screens/")
              ? "./screen.html"
              : "./data/screens/screen.html";
            navigateToScreen(base);
          });
        }
        console.log(data.screen1 && data.screen1.quizName);

        // ---------------------- Update Question Text -------------------------
        const questionTextElement = document.getElementById("question-text");
        if (questionTextElement) {
          questionTextElement.textContent =
            screenData.question || "Default Question";
        } else {
          console.warn("⚠️ 'question-text' element not found.");
        }

        // ---------------------- Update Answer Text -------------------------
        const answerTextElement = document.getElementById("answer-text");
        if (answerTextElement) {
          answerTextElement.textContent = screenData.answer || "Default Answer";
        } else {
          console.warn("⚠️ 'answer-text' element not found.");
        }

        // ---------------------- Update Artist Name -------------------------
        // Shown alongside the answer (see the answer-toggle logic below,
        // which reveals it together with the answer text, not before).
        const artistNameDisplay = document.getElementById("artist-name-display");
        if (artistNameDisplay) {
          artistNameDisplay.textContent = screenData.artistName
            ? "Artist: " + screenData.artistName
            : "";
        }

        // ---------------------- Update Firm Naming -------------------------
        const firmNamingElement = document.querySelector(
          ".title-section h1:nth-child(2)"
        );
        if (firmNamingElement) {
          firmNamingElement.textContent =
            screenData.firmNaming || "Default Firm Naming";
        }

        // ---------------------- Update Fact Info Drawer (Additional Notes) -------------------------
        const factDrawerContent = document.getElementById("factDrawerContent");

        console.log(factDrawerContent, "factDrawerContent");
        console.log(screenData.additionalNotes, " <<<<<<<additionalNotes ");

        if (factDrawerContent) {
          factDrawerContent.innerHTML =
            screenData.additionalNotes ||
            "<p>No additional information available.</p>";
        }
      } else {
        console.warn(`No data found for screen: ${screenId}`);
        // Both the API fetch and the static content.json fallback failed
        // (e.g. a restrictive in-app browser blocking the cross-origin
        // request) — surface that instead of leaving "Loading..." forever
        // with no indication anything went wrong.
        const quizNameElement = document.getElementById("quizName");
        if (quizNameElement) {
          quizNameElement.textContent = "Could not load quiz — check connection" + (window._aqErr ? " (" + window._aqErr + ")" : "");
        }
      }
    })
    .catch((error) => {
      console.error("Error loading JSON file:", error);
      const quizNameElement = document.getElementById("quizName");
      if (quizNameElement) {
        quizNameElement.textContent = "Could not load quiz — check connection (" + (error && error.name) + ": " + (error && error.message) + ")";
      }
    });

  // ---------------------- Screen Number Logic -------------------------
  // Fetch the screen number from the URL or assign manually
  const screenNumber = parseInt(
    (window.location.href.match(/screen(\d+)/) || [])[1] || 1
  ); // Defaults to screen1

  // ---------------------- Dynamic Music Elements Generation -------------------------
  try {
    const data = await loadAudioQuizData();
    console.log("✅ JSON Data Loaded:", data);

    // ✅ Dynamically Generate Music Elements Using JSON Data
    const musicContainer = document.getElementById("musicContainer");
    if (musicContainer) {
      musicContainer.innerHTML = ""; // Clear existing content

      for (let i = 1; i <= 16; i++) {
        const screenKey = `screen${i}`;
        // Only render tracks that actually exist - a quiz with just 1 page
        // filled in used to still show all 16 slots as "Music Track N /
        // Unknown Artist" placeholders, since this loop never checked
        // whether the screen was real before falling back to defaults.
        if (!data[screenKey]) continue;
        const screenData = data[screenKey];
        const musicName = screenData.musicName || `Music Track ${i}`;
        const artistName = screenData.artistName || `Unknown Artist`;
        const formattedNumber = i.toString().padStart(2) + ".";

        // Create music entry dynamically
        const musicElement = document.createElement("div");
        musicElement.className =
          "flex flex-col items-center justify-start bg-black";
        const base = window.location.pathname.includes("/data/screens/")
          ? "./"
          : "./data/screens/";

        musicElement.innerHTML = `


  <div class="w-full cursor-pointer group" data-screen-target="${base}show.html?screen=${i}">
            <p class="sm:text-xl text-xl text-center px-[5%] pt-1 text-yellow-400 font-['Arial_Narrow']">
              ${formattedNumber}
            </p>
            <p class="sm:text-xl text-xl text-center px-[5%] pt-1 text-yellow-400 font-['Arial_Narrow']" id="music-name-${i}">
              ${musicName}
            </p>
            <p class="sm:text-2xl text-2xl text-center px-[5%] pt-1 text-purple-400 font-['Rockwell']" id="artist-name-${i}">
              ${artistName}
            </p>
            
            <div class="flex flex-col items-center pt-1">
              <button
                id="playBtn${i}"
                class="play-btn bg-transparent hover:border-none border-none focus-visible:outline-none focus:outline-none transition-transform duration-300 hover:scale-105"
              >
                <img
                  id="playImg${i}"
                  src="../assets/play-red1.png"
                  alt="Play"
                  class="py-5 sm:py-2 sm:w-17 w-16"
                />
              </button>
              <input
                type="range"
                id="seekBar${i}"
                class="seek-bar w-[80%] h-[3px] my-2 bg-gray-300 accent-gray-500 rounded-lg cursor-pointer focus:outline-none"
              />
            </div>
          </div>
        `;

        // Append to music container
        musicContainer.appendChild(musicElement);
      }
    }

    console.log("✅ Music Elements Generated Successfully!");

    // ✅ Initialize Music Player Logic AFTER Elements Exist
    // Only when this page actually has the generated track list — this used
    // to run unconditionally on every screen. The single-track question
    // screens (show.html/show1.html) have their own dedicated music button
    // with id="playBtn1", which ALSO carries class="play-btn" — so on those
    // pages this was matching that same button via .play-btn, attaching a
    // SECOND, entirely independent click handler and its own separate Audio
    // object to it. One click then started two unrelated Audio instances
    // playing the same track; the dedicated player's mutex-with-the-voice
    // logic only ever knew about its own instance, so pausing it left the
    // second, invisible instance still audibly playing — the "icon shows
    // stopped but sound keeps playing" bug.
    if (musicContainer) {
      initializeMusicControls(data);
    }
  } catch (error) {
    console.error("❌ Error loading JSON file:", error);
  }

  // ---------------------- Music Controls Initialization Function -------------------------
  function initializeMusicControls(data) {
    const playButtons = document.querySelectorAll(".play-btn");
    const playImages = document.querySelectorAll("[id^=playImg]");
    const seekBars = document.querySelectorAll(".seek-bar");

    // iOS Safari enforces a hard cap on how many <audio>/<video> elements a
    // page may create, and simply refuses to play once it's exceeded — no
    // error, play() just does nothing. Creating all 16 tracks' Audio objects
    // up front (as this used to do) hit that cap and silently broke playback
    // on iPhone, while desktop browsers have no such limit and worked fine.
    // Fix: create each track's Audio object lazily, the first time it's
    // actually played, and reuse it after that. Also avoids firing 16
    // simultaneous mp3 downloads on page load, which is wasteful on mobile
    // data.
    const audioFiles = Array(16).fill(null);
    let isPlaying = Array(16).fill(false);

    function getAudio(index) {
      if (!audioFiles[index]) {
        // The DOM's button order no longer lines up 1:1 with screen number
        // once empty screens are skipped above (e.g. only screens 1 and 5
        // exist → buttons[0]=screen1, buttons[1]=screen5, not screen2) -
        // each button's own id ("playBtn5") still carries its real screen
        // number, so read that instead of assuming index+1.
        const button = playButtons[index];
        const realPage =
          button && button.id ? parseInt(button.id.replace("playBtn", ""), 10) : index + 1;
        const screenKey = `screen${realPage}`;
        const screenData = data[screenKey] || {};
        const audio = new Audio(screenData.musicFileUrl);
        const playImage = playImages[index];
        const seekBar = seekBars[index];

        audio.addEventListener("timeupdate", () => {
          if (seekBar && audio.duration) {
            seekBar.value = (audio.currentTime / audio.duration) * 100;
          }
        });

        if (seekBar) {
          seekBar.addEventListener("input", () => {
            audio.currentTime = (seekBar.value / 100) * audio.duration;
          });
        }

        audio.addEventListener("ended", () => {
          if (playImage) playImage.src = "../assets/play-red1.png";
          isPlaying[index] = false;
          if (seekBar) seekBar.value = 0;
        });

        audioFiles[index] = audio;
      }
      return audioFiles[index];
    }

    // Initialize seek bars
    seekBars.forEach((seekBar) => {
      seekBar.value = 0;
    });

    playButtons.forEach((button, index) => {
      const playImage = playImages[index];

      button.addEventListener("click", () => {
        const audio = getAudio(index);

        if (isPlaying[index]) {
          safePause(audio);
          playImage.src = "../assets/play-red1.png";
        } else {
          audioFiles.forEach((otherAudio, i) => {
            if (otherAudio && i !== index) {
              safePause(otherAudio);
              if (playImages[i]) playImages[i].src = "../assets/play-red1.png";
              isPlaying[i] = false;
            }
          });
          safePlay(audio).catch((err) => console.error("Audio play error:", err));
          playImage.src = "../assets/play-red2.png";
        }
        isPlaying[index] = !isPlaying[index];
      });
    });
  }

  // ---------------------- Screen Navigation Logic -------------------------
  document.querySelectorAll("[data-screen-target]").forEach((el) => {
    el.addEventListener("click", (e) => {
      // Allow navigation ONLY if the clicked element is a <p> tag
      // and matches one of the intended elements
      const targetTag = e.target;
      const tagName = targetTag.tagName.toLowerCase();

      const isValidClick =
        tagName === "p" &&
        ((targetTag.id || "").startsWith("music-name-") ||
          (targetTag.id || "").startsWith("artist-name-") ||
          /^[0-9]+\./.test(targetTag.textContent.trim())); // Page number match

      if (!isValidClick) {
        // Prevent navigation if clicked on anything else (seekbar, button, img, etc)
        return;
      }

      let target = el.getAttribute("data-screen-target");
      // Carry ?show=<id> forward, same as every other navigation path in
      // this file. Without this, a shared-link session loses its quiz data
      // on this specific click (it falls back to localStorage alone, which
      // in-app browsers like WhatsApp/Instagram/Messenger — and Safari
      // private mode — often don't persist across this navigation), so the
      // next screen silently shows placeholder content instead of the real
      // quiz, including the real music/speaker URLs.
      if (target && !target.includes("?show=") && !target.includes("&show=") && window.location.search) {
        target += target.includes("?")
          ? "&" + window.location.search.slice(1)
          : window.location.search;
      }
      console.log("🔗 Navigating to quiz screen:", target);
      // Same flag as navigateToScreen() / the <a> click handler above —
      // this is a legitimate in-quiz hop, not a fresh external entry.
      try {
        sessionStorage.setItem("aq_internal_nav", "1");
      } catch {}
      window.location.href = target;
    });
  });

  // ---------------------- Answer Toggle Logic -------------------------
  const answerDiv = document.querySelector(".toggle-answer-area");

  // Select all paragraphs inside the answer area
  const answerText = answerDiv
    ? answerDiv.querySelectorAll(".answer-text")
    : [];

  // The artist name reveal (added separately from .answer-text so it
  // doesn't shift the `answerText[1]` indexing above) fades in/out in sync
  // with the answer text - same click, same toggle.
  const artistNameDisplay = document.getElementById("artist-name-display");

  // Add the click event listener to the parent for toggling
  if (answerDiv) {
    answerDiv.addEventListener("click", (event) => {
      event.stopPropagation(); // Prevent conflicts with other elements

      if (answerText.length > 1) {
        // Toggle between showing and hiding the second paragraph with fade effect
        const secondParagraph = answerText[1];
        const nowHidden = secondParagraph.classList.contains("opacity-0");
        if (nowHidden) {
          secondParagraph.classList.remove("opacity-0", "pointer-events-none");
        } else {
          secondParagraph.classList.add("opacity-0", "pointer-events-none");
        }
        if (artistNameDisplay) {
          artistNameDisplay.classList.toggle("opacity-0", !nowHidden);
          artistNameDisplay.classList.toggle("pointer-events-none", !nowHidden);
        }
      }
    });
  }

  // Ensure the second paragraph (and the artist name with it) is hidden initially
  if (answerText.length > 1) {
    answerText[1].classList.add("opacity-0", "pointer-events-none");
  }
  if (artistNameDisplay) {
    artistNameDisplay.classList.add("opacity-0", "pointer-events-none");
  }

  // ---------------------- Tooltip Logic -------------------------
  // Select all tooltips for speaker and play buttons
  const tooltips = document.querySelectorAll(".tooltip");

  // Function to handle tooltip visibility with timeout logic
  tooltips.forEach((tooltip) => {
    const parentButton = tooltip.closest(".group");

    parentButton.addEventListener("mouseenter", () => {
      // Show the tooltip
      tooltip.classList.remove("opacity-0");
      tooltip.classList.add("opacity-100");

      // Set a timeout to hide the tooltip after 3 seconds
      setTimeout(() => {
        tooltip.classList.remove("opacity-100");
        tooltip.classList.add("opacity-0");
      }, 3000); // Tooltip disappears after 3 seconds
    });
  });

  // ---------------------- Top Navigation Logic -------------------------
  // Ensure top navigation elements are clickable
  function enableTopNavigation() {
    const leftTopNav = document.getElementById("leftTopNav");
    const rightTopNav = document.getElementById("rightTopNav");
    const fullScreenContainer = document.getElementById("screen2FullScreen");

    if (leftTopNav && rightTopNav && fullScreenContainer) {
      console.log("✅ Ensuring Top Navigation is Clickable");

      // Force pointer events on navigation areas
      leftTopNav.style.pointerEvents = "auto";
      rightTopNav.style.pointerEvents = "auto";

      // Increase z-index to be above fullscreen container
      leftTopNav.style.zIndex = "101";
      rightTopNav.style.zIndex = "101";

      // Ensure fullscreen container does NOT overlap
      fullScreenContainer.style.zIndex = "100";
      fullScreenContainer.style.position = "relative";

      console.log("✅ Navigation areas updated.");
    } else {
      console.warn("⚠️ Navigation elements not found!");
    }
  }

  enableTopNavigation();

  // Attach event listeners for navigation clicks
  var leftTopNavEl = document.getElementById("leftTopNav");
  if (leftTopNavEl) leftTopNavEl.addEventListener("click", function () {
    const base = window.location.pathname.includes("/data/screens/")
      ? "./screen1.html"
      : "./data/screens/screen1.html";
    navigateToScreen(base);
  });
  var rightTopNavEl = document.getElementById("rightTopNav");
  if (rightTopNavEl)
    rightTopNavEl.addEventListener("click", function () {
      // Always navigate to the correct path based on screen number logic
      const currentPath = window.location.pathname;
      let nextScreenPath;

      if (currentPath.includes("/data/screens/")) {
        nextScreenPath = "./screen18.html"; // relative to current screen
      } else {
        nextScreenPath = "./data/screens/screen18.html"; // used when on index.html
      }

      console.log("Navigating to:", nextScreenPath);
      navigateToScreen(nextScreenPath);
    });

  // NOTE: navigateToScreen is intentionally NOT (re)declared here. A local
  // `function navigateToScreen(url) { window.location.href = url; }` used to
  // live in this exact spot, inside this DOMContentLoaded callback. Because
  // function declarations are scoped to their enclosing function, that local
  // definition shadowed the real, global navigateToScreen (declared further
  // down in this file, the one that preserves ?show=<id> across navigation)
  // for every call made from within this callback — which is most of them:
  // the quizName click, the top-nav arrows, etc. That's why a shared-link
  // session kept losing its ?show=<id> on nearly every internal click. Do
  // not add a navigateToScreen definition back in this scope.

  // ---------------------- Fullscreen Logic with Top 10% Interactive Area -------------------------
  function setupDoubleTapListeners(panelId) {
    const panel = document.getElementById(panelId);
    if (!panel) return;

    // Create an interactive top 10% area for fullscreen activation
    const topRegion = document.createElement("div");
    topRegion.style.position = "absolute";
    topRegion.style.top = "0";
    topRegion.style.left = "0";
    topRegion.style.width = "100%";
    topRegion.style.height = "10%";
    topRegion.style.cursor = "pointer";
    topRegion.style.zIndex = "100";

    // Attach double-click event to trigger fullscreen
    topRegion.addEventListener("dblclick", toggleFullScreen);

    // Add hover message
    const hoverMessage = document.createElement("div");
    hoverMessage.style.position = "absolute";
    hoverMessage.style.transform = "translate(-50%, -50%)";
    hoverMessage.style.borderRadius = "8px";
    hoverMessage.style.fontSize = "14px";
    hoverMessage.style.opacity = "0";
    hoverMessage.style.transition = "opacity 0.5s";

    // Show the hover message only on hover
    topRegion.addEventListener("mouseenter", () => {
      hoverMessage.style.opacity = "1";
    });
    topRegion.addEventListener("mouseleave", () => {
      hoverMessage.style.opacity = "0";
    });

    // Add the hover message and top area to the panel
    topRegion.appendChild(hoverMessage);
    panel.style.position = "relative";
    panel.appendChild(topRegion);
  }

  // Function to toggle fullscreen mode
  function toggleFullScreen() {
    if (!document.fullscreenElement) {
      document.documentElement.requestFullscreen().catch((err) => {
        console.error("Error entering fullscreen:", err);
      });
    } else {
      document.exitFullscreen();
    }
  }

  // Apply fullscreen listeners to multiple panels
  const panels = [
    "screen2FullScreen",
    "middlePanel",
    "rightPanel",
    "lefttPanel",
    "leftttPanel",
  ];
  panels.forEach((panelId) => setupDoubleTapListeners(panelId));
});

// ---------------------- Interactive Zones Setup -------------------------
// Function to set up top, left, and right zones for each panel
function setupInteractiveZones(panelId) {
  const panel = document.getElementById(panelId);
  if (!panel) return; // Prevent errors if the panel does not exist

  panel.style.position = "relative"; // Ensure relative positioning for absolute child elements

  // ---------------- Top 10% Zone (Fullscreen Toggle) ----------------
  const topRegion = createInteractionZone(
    "0%",
    "0%",
    "100%",
    "10%",
    "Double-click to Maximize"
  );
  topRegion.addEventListener("dblclick", toggleFullScreen);
  panel.appendChild(topRegion);

  // ---------------- Left 10% Zone (Left Navigation) ----------------
  const leftRegion = createInteractionZone(
    "0%",
    "0%",
    "10%",
    "100%",
    "← Navigate Left"
  );
  leftRegion.addEventListener("click", () => navigateLeft(panelId));
  panel.appendChild(leftRegion);

  // ---------------- Right 10% Zone (Right Navigation) ----------------
  const rightRegion = createInteractionZone(
    "90%",
    "0%",
    "10%",
    "100%",
    "→ Navigate Right"
  );
  rightRegion.addEventListener("click", () => navigateRight(panelId));
  panel.appendChild(rightRegion);
}

// Function to create a reusable interactive zone
function createInteractionZone(left, top, width, height, hoverText) {
  const region = document.createElement("div");
  region.style.position = "absolute";
  region.style.left = left;
  region.style.top = top;
  region.style.width = width;
  region.style.height = height;
  region.style.cursor = "pointer";
  region.style.zIndex = "100";

  // Hover Message
  const hoverMessage = document.createElement("div");
  hoverMessage.textContent = hoverText;
  hoverMessage.style.position = "absolute";
  hoverMessage.style.top = "50%";
  hoverMessage.style.left = "50%";
  hoverMessage.style.transform = "translate(-50%, -50%)";
  hoverMessage.style.color = "white";
  hoverMessage.style.backgroundColor = "rgba(0, 0, 0, 0.7)";
  hoverMessage.style.padding = "5px 10px";
  hoverMessage.style.borderRadius = "8px";
  hoverMessage.style.fontSize = "14px";
  hoverMessage.style.opacity = "0";
  hoverMessage.style.transition = "opacity 0.5s";

  // Show message on hover
  region.addEventListener("mouseenter", () => {
    hoverMessage.style.opacity = "1";
  });
  region.addEventListener("mouseleave", () => {
    hoverMessage.style.opacity = "0";
  });

  region.appendChild(hoverMessage);
  return region;
}

// ---------------------- Navigation Functions -------------------------
// (Only one definition now — a duplicate simple version used to sit here
// too; being declared before this one, it was harmless — this one always
// won — but kept two definitions of the same function in one file, which is
// exactly the kind of thing that caused the shadowing bug fixed above.)
function navigateToScreen(url) {
  // Auto-preserve the current ?show=<id> query string on every
  // navigation, unless the target URL already specifies its own query.
  // This means every existing onclick="navigateToScreen('...')" call
  // across every screen file keeps a shared-link session alive without
  // each one needing to be edited individually.
  if (!url.includes("?") && window.location.search) {
    url += window.location.search;
  }

  // Mark this as a legitimate in-quiz hop so guardDirectDeepLinkEntry()
  // (top of this file) doesn't bounce the next page back to the title
  // screen — see that function for why this exists.
  const markInternalNav = () => {
    try {
      sessionStorage.setItem("aq_internal_nav", "1");
    } catch {}
  };

  const mainContainer = document.getElementById("screen2FullScreen");

  if (mainContainer) {
    // Add Tailwind classes for fade-out effect
    mainContainer.classList.add(
      "opacity-0",
      "transition-opacity",
      "duration-500"
    );

    // Redirect after transition completes
    setTimeout(() => {
      markInternalNav();
      window.location.href = url;
    }, 500); // Matches Tailwind's 500ms duration
  } else {
    // Fallback if container not found
    markInternalNav();
    window.location.href = url;
  }
}

// Ensure the screen fades in on load
window.addEventListener("load", () => {
  const mainContainer = document.getElementById("screen2FullScreen");
  if (mainContainer) {
    mainContainer.classList.remove("opacity-0");
    mainContainer.classList.add(
      "opacity-100",
      "transition-opacity",
      "duration-500"
    );
  }
});

// ---------------------- Drawer Logic -------------------------
document.addEventListener("DOMContentLoaded", () => {
  const showButton = document.querySelector(
    "[data-drawer-show='drawer-bottom-example']"
  );
  const drawer = document.getElementById("drawer-bottom-example");

  if (showButton && drawer) {
    const closeButton = drawer.querySelector(
      "[data-drawer-hide='drawer-bottom-example']"
    );

    // Show drawer when the button is clicked
    showButton.addEventListener("click", () => {
      drawer.classList.remove("transform-none");
      drawer.classList.add("translate-y-0");
    });

    // Hide drawer when the close button is clicked
    if (closeButton) {
      closeButton.addEventListener("click", () => {
        drawer.classList.remove("translate-y-0");
        drawer.classList.add("transform-none");
      });
    }
  }
});

// ---------------------- Fullscreen Button Logic -------------------------
function enterFullscreen() {
  const elem = document.documentElement; // Use full document (recommended)

  if (elem.requestFullscreen) {
    elem.requestFullscreen();
  } else if (elem.webkitRequestFullscreen) {
    elem.webkitRequestFullscreen(); // Safari
  } else if (elem.msRequestFullscreen) {
    elem.msRequestFullscreen(); // IE11
  } else {
    alert("Fullscreen not supported on this browser.");
  }
}

document.addEventListener("DOMContentLoaded", () => {
  const fullscreenBtn = document.getElementById("fullscreenBtn");
  if (fullscreenBtn) {
    fullscreenBtn.addEventListener("click", () => {
      enterFullscreen();

      const target = document.getElementById("screen2FullScreen");
      if (target) {
        target.classList.remove("opacity-0");
        target.classList.add("opacity-100");
      }
    });
  }
});
