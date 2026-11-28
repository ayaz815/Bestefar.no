// `node --test tests/*.test.js` - AudioQuiz: each screen is shown only when it is complete.
// User, 28 Nov 2026: "when i start audio quiz then the audio bar comes back to 0 ... it
// seems to be moving when i navigate to the next page ... all elements must be at there
// place". Measured on live at 390x664 (Historiske stemmer I, question 2): the screen had
// faded in fully at ~1.2 s, the quiz data arrived at ~1.35 s, and only then the audio bar
// knob jumped from the middle (a range input with no value starts at 50) to 0, the title
// changed "2. Melodi" -> "2. Stemme", "Spill av melodien" -> "Spill av stemmen", and the
// question and artist appeared. Box sizes did not change (fixed 25 Nov).
// PLAYERS_ROOT can point at an older copy of httpd.www to prove the tests catch the old files.
const test = require("node:test");
const assert = require("node:assert");
const fs = require("fs");
const path = require("path");
const vm = require("vm");

const root = process.env.PLAYERS_ROOT || path.join(__dirname, "..", "httpd.www");
const read = (p) => fs.readFileSync(path.join(root, "bestefar-audio", p), "utf8");
const script = read("data/script.js");

// The code that fades a screen in: the new block, or the old "on load" one.
function revealCode() {
  let start = script.indexOf("// ── SCREEN REVEAL");
  if (start < 0) start = script.indexOf("// Ensure the screen fades in on load");
  const end = script.indexOf("// ---------------------- Drawer Logic", start);
  assert.ok(start > 0 && end > start, "fade-in code in data/script.js");
  return script.slice(start, end);
}

const flush = () => new Promise((r) => setImmediate(r));

// A page: the container (class list), the load event, the data promise, timers.
function page({ container = true } = {}) {
  let resolveData, rejectData;
  const data = new Promise((res, rej) => { resolveData = res; rejectData = rej; });
  data.catch(() => {});
  const classes = new Set(["opacity-0", "transition-opacity", "duration-500"]);
  let adds = 0;
  const el = container && {
    classList: {
      remove: (...c) => c.forEach((x) => classes.delete(x)),
      add: (...c) => { adds++; c.forEach((x) => classes.add(x)); },
      contains: (c) => classes.has(c),
    },
  };
  const loadHandlers = [], timers = [];
  const shown = () => !classes.has("opacity-0");
  // What script.js does on DOMContentLoaded: the screen-filling code is attached first.
  const filled = [];
  data.then(() => filled.push({ shownWhileFilling: shown() }), () => {});
  const sb = {
    console,
    document: { getElementById: (id) => (id === "screen2FullScreen" ? el : null) },
    window: { addEventListener: (ev, fn) => { if (ev === "load") loadHandlers.push(fn); } },
    setTimeout: (fn, ms) => { timers.push({ fn, ms }); return timers.length; },
    loadAudioQuizData: () => data,
  };
  vm.runInContext(revealCode(), vm.createContext(sb));
  return {
    classes, timers, filled, shown, adds: () => adds,
    load: () => loadHandlers.forEach((fn) => fn()),
    resolveData, rejectData,
  };
}

test("the audio bar starts at 0 in the page itself (its knob never sits in the middle)", () => {
  const tag = read("data/screens/show.html").match(/<input[^>]*id="seekBar1"[^>]*>/);
  assert.ok(tag, "question screen audio bar");
  assert.match(tag[0], /type="range"/);
  assert.match(tag[0], /\svalue="0"/, "without a value a range input starts at 50 (middle)");
  const list = script.match(/<input[^>]*id="seekBar\$\{i\}"[^>]*>/);
  assert.ok(list, "melody list audio bars");
  assert.match(list[0], /\svalue="0"/);
});

test("after load the screen stays hidden until the quiz data is filled in, then fades in", async () => {
  const p = page();
  p.load();
  await flush();
  assert.ok(!p.shown(), "not shown on load alone (data not there yet)");
  p.resolveData({ screen2: {} });
  await flush();
  assert.deepStrictEqual(p.filled, [{ shownWhileFilling: false }], "texts filled while still hidden");
  assert.ok(p.shown(), "shown once filled");
  assert.ok(p.classes.has("opacity-100") && p.classes.has("transition-opacity") && p.classes.has("duration-500"), "same 0.5 s fade as before");
});

test("data already there at load: shown straight away", async () => {
  const p = page();
  p.resolveData({ screen2: {} });
  await flush();
  p.load();
  await flush();
  assert.ok(p.shown());
});

test("data failed: the screen is still shown (not left blank)", async () => {
  const p = page();
  p.load();
  p.rejectData(new Error("offline"));
  await flush();
  assert.ok(p.shown());
});

test("data never arrives: shown after 3 s, not before", async () => {
  const p = page();
  p.load();
  await flush();
  assert.ok(!p.shown());
  assert.deepStrictEqual(p.timers.map((t) => t.ms), [3000]);
  p.timers[0].fn();
  assert.ok(p.shown());
});

test("faded in only once (late data after the 3 s limit does nothing)", async () => {
  const p = page();
  p.load();
  p.timers[0].fn();
  p.resolveData({});
  await flush();
  assert.strictEqual(p.adds(), 1);
});

test("regression: a page without the screen container does nothing and has no error", async () => {
  const p = page({ container: false });
  p.load();
  p.resolveData({});
  await flush();
  assert.strictEqual(p.timers.length, 0);
});

test("regression: screens still start hidden, and going to the next screen still fades out first", () => {
  for (const f of ["index.html", "data/screens/show.html"]) {
    assert.match(read(f), /id="screen2FullScreen"/, f);
    assert.match(read(f).match(/<div\s+class="[^"]*"\s+id="screen2FullScreen"/)[0], /\bopacity-0\b/, f + " starts hidden");
  }
  const nav = script.slice(script.indexOf("function navigateToScreen(url) {"), script.indexOf("// ── SCREEN REVEAL"));
  assert.match(nav, /mainContainer\.classList\.add\(\s*"opacity-0",\s*"transition-opacity",\s*"duration-500"\s*\)/);
  assert.match(nav, /window\.location\.href = url;\s*\}, 500\);/);
});

test("regression: the audio bar still follows the music and goes back to 0 when it ends", () => {
  assert.match(script, /seekBar1\.value =\s*\(musicAudio\.currentTime \/ musicAudio\.duration\) \* 100;/);
  assert.match(script, /musicAudio\.addEventListener\("ended", \(\) => \{[\s\S]{0,160}seekBar1\.value = 0;/);
  assert.match(script, /seekBar1\.addEventListener\("input", \(\) => \{\s*musicAudio\.currentTime =/);
});
