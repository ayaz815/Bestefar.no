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

// Single shared loader — called by every consumer in this file instead of
// each fetching content.json independently. Resolves to the SAME object
// shape content.json always provided ({ screen1: {...}, screen2: {...} }),
// so no other code in this file needs to change.
function loadAudioQuizData() {
  if (_audioQuizDataPromise) return _audioQuizDataPromise;

  _audioQuizDataPromise = (async () => {
    const expectedId = new URLSearchParams(location.search).get("show");

    let data = null;
    try {
      const stored = JSON.parse(localStorage.getItem(AQ_STORAGE_KEY));
      if (stored) data = stored;
    } catch {}

    // Stale cache from a different show/link — discard, force a fresh fetch.
    if (expectedId && data && data.id && data.id !== expectedId) {
      data = null;
    }

    if (!data && expectedId) {
      try {
        const r = await fetch(`${AQ_API_BASE}/get-audio-quiz/${expectedId}`);
        if (r.ok) {
          const json = await r.json();
          data = quizFormsArrayToScreenMap(json.quizForms, json.quizName);
          data.id = json.id || expectedId;
          try {
            localStorage.setItem(AQ_STORAGE_KEY, JSON.stringify(data));
          } catch {}
        }
      } catch {}
    }

    if (!data) {
      try {
        const r = await fetch(getAudioQuizJsonPath());
        if (r.ok) data = await r.json();
      } catch {}
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

        if (seekBar1) seekBar1.value = 0;

        // ---------------------- SPEAKER (Voice) Playback Logic -------------------------
        const speakerUrl = screenData.audioFileUrl;
        const speakerBtn = document.querySelector(".speaker-btn");
        const speakerImg = document.querySelector(".speaker-btn img");
        const speakerAudio = new Audio(speakerUrl);
        let speakerPlayedOnce = false;
        let speakerMuted = true;

        // ---------------------- Music Button Event Handler -------------------------
        if (musicBtn && musicImg) {
          musicBtn.addEventListener("click", (e) => {
            e.stopPropagation();

            // FIXED: Pause speaker audio first if needed (similar to how speaker pauses music)
            if (!speakerMuted && speakerAudio && !speakerAudio.paused) {
              speakerAudio.pause();
              if (speakerImg) speakerImg.style.opacity = "0.7";
              speakerMuted = true;
            }

            musicPlaying = !musicPlaying;
            if (musicPlaying) {
              musicAudio.play().catch(console.error);
              musicImg.src = "../assets/play-red2.png";
            } else {
              musicAudio.pause();
              musicImg.src = "../assets/play-red1.png";
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

            // Pause music first if needed
            if (musicPlaying) {
              musicAudio.pause();
              musicPlaying = false;
              if (musicImg) musicImg.src = "../assets/play-red1.png";
            }

            // First play or toggle
            if (!speakerPlayedOnce) {
              speakerAudio.play().catch(console.error);
              speakerImg.style.opacity = "1";
              speakerPlayedOnce = true;
              speakerMuted = false;
            } else {
              speakerMuted = !speakerMuted;
              if (speakerMuted) {
                speakerAudio.pause();
                speakerImg.style.opacity = "0.7";
              } else {
                speakerAudio.play().catch(console.error);
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
            data.screen1?.quizName || data.quizName || "Default Quiz Name";

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
        console.log(data.screen1?.quizName);

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
      }
    })
    .catch((error) => {
      console.error("Error loading JSON file:", error);
    });

  // ---------------------- Screen Number Logic -------------------------
  // Fetch the screen number from the URL or assign manually
  const screenNumber = parseInt(
    window.location.href.match(/screen(\d+)/)?.[1] || 1
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
        const screenData = data[screenKey] || {}; // Get screen data (fallback to empty object)
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
    initializeMusicControls(data);
  } catch (error) {
    console.error("❌ Error loading JSON file:", error);
  }

  // ---------------------- Music Controls Initialization Function -------------------------
  function initializeMusicControls(data) {
    const playButtons = document.querySelectorAll(".play-btn");
    const playImages = document.querySelectorAll("[id^=playImg]");
    const seekBars = document.querySelectorAll(".seek-bar");

    const audioFiles = Array.from({ length: 16 }, (_, index) => {
      const screenKey = `screen${index + 1}`;
      const screenData = data[screenKey] || {};
      const url = screenData.musicFileUrl;
      return new Audio(url);
    });
    let isPlaying = Array(16).fill(false);

    // Initialize seek bars
    seekBars.forEach((seekBar) => {
      seekBar.value = 0;
    });

    playButtons.forEach((button, index) => {
      const audio = audioFiles[index];
      const playImage = playImages[index];
      const seekBar = seekBars[index];

      button.addEventListener("click", () => {
        if (isPlaying[index]) {
          audio.pause();
          playImage.src = "../assets/play-red1.png";
        } else {
          audioFiles.forEach((otherAudio, i) => {
            if (i !== index) {
              otherAudio.pause();
              playImages[i].src = "../assets/play-red1.png";
              isPlaying[i] = false;
            }
          });
          audio.play().catch((err) => console.error("Audio play error:", err));
          playImage.src = "../assets/play-red2.png";
        }
        isPlaying[index] = !isPlaying[index];
      });

      audio.addEventListener("timeupdate", () => {
        seekBar.value = (audio.currentTime / audio.duration) * 100;
      });

      seekBar.addEventListener("input", () => {
        audio.currentTime = (seekBar.value / 100) * audio.duration;
      });

      audio.addEventListener("ended", () => {
        playImage.src = "../assets/play-red1.png";
        isPlaying[index] = false;
        seekBar.value = 0;
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
        (targetTag.id?.startsWith("music-name-") ||
          targetTag.id?.startsWith("artist-name-") ||
          /^[0-9]+\./.test(targetTag.textContent.trim())); // Page number match

      if (!isValidClick) {
        // Prevent navigation if clicked on anything else (seekbar, button, img, etc)
        return;
      }

      const target = el.getAttribute("data-screen-target");
      console.log("🔗 Navigating to quiz screen:", target);
      window.location.href = target;
    });
  });

  // ---------------------- Answer Toggle Logic -------------------------
  const answerDiv = document.querySelector(".toggle-answer-area");

  // Select all paragraphs inside the answer area
  const answerText = answerDiv
    ? answerDiv.querySelectorAll(".answer-text")
    : [];

  // Add the click event listener to the parent for toggling
  if (answerDiv) {
    answerDiv.addEventListener("click", (event) => {
      event.stopPropagation(); // Prevent conflicts with other elements

      if (answerText.length > 1) {
        // Toggle between showing and hiding the second paragraph with fade effect
        const secondParagraph = answerText[1];
        if (secondParagraph.classList.contains("opacity-0")) {
          secondParagraph.classList.remove("opacity-0", "pointer-events-none");
        } else {
          secondParagraph.classList.add("opacity-0", "pointer-events-none");
        }
      }
    });
  }

  // Ensure the second paragraph is hidden initially
  if (answerText.length > 1) {
    answerText[1].classList.add("opacity-0", "pointer-events-none");
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
  document.getElementById("leftTopNav")?.addEventListener("click", function () {
    const base = window.location.pathname.includes("/data/screens/")
      ? "./screen1.html"
      : "./data/screens/screen1.html";
    navigateToScreen(base);
  });
  document
    .getElementById("rightTopNav")
    ?.addEventListener("click", function () {
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

  function navigateToScreen(url) {
    console.log(`📢 Navigating to: ${url}`);
    window.location.href = url;
  }

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
function navigateToScreen(targetScreen) {
  window.location.href = targetScreen;
}

// Smooth Navigation with Tailwind
function navigateToScreen(url) {
  // Auto-preserve the current ?show=<id> query string on every
  // navigation, unless the target URL already specifies its own query.
  // This means every existing onclick="navigateToScreen('...')" call
  // across every screen file keeps a shared-link session alive without
  // each one needing to be edited individually.
  if (!url.includes("?") && window.location.search) {
    url += window.location.search;
  }

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
      window.location.href = url;
    }, 500); // Matches Tailwind's 500ms duration
  } else {
    // Fallback if container not found
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
