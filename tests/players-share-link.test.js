// Runs the real inline <script> of the ImageQuiz / ImageMusic share-link
// pages in a sandbox (no browser needed): `node --test tests/`
// Media elements here NEVER finish loading - what phones effectively do for
// preloaded audio/video - so these tests prove the opening screen can't hang.
const test = require("node:test");
const assert = require("node:assert");
const fs = require("fs");
const path = require("path");
const vm = require("vm");

const root = process.env.PLAYERS_ROOT || path.join(__dirname, "..", "httpd.www");
const read = (p) => fs.readFileSync(path.join(root, p), "utf8");
const lastScript = (html) => [...html.matchAll(/<script>([\s\S]*?)<\/script>/g)].pop()[1];

function el() {
  const classes = new Set();
  return {
    style: {}, textContent: "", attrs: {},
    classList: { add: (c) => classes.add(c), remove: (c) => classes.delete(c), has: (c) => classes.has(c) },
    addEventListener() {}, click() {},
    set href(v) { this.attrs.href = v; }, get href() { return this.attrs.href; },
    setAttribute(k, v) { this.attrs[k] = v; }, getAttribute(k) { return this.attrs[k]; },
  };
}

// instantMedia=false: media never fires load events (stuck phone preload).
function run(html, { showId, api, instantMedia = false }) {
  const els = {}, timers = [], store = {}, preloaded = [];
  const doc = {
    title: "",
    getElementById: (id) => (els[id] ||= el()),
    createElement: () => { const v = el(); return { ...v, set src(u) { preloaded.push(u); if (instantMedia) setImmediate(() => v.oncanplaythrough?.() || this.oncanplaythrough?.()); }, }; },
    addEventListener() {},
  };
  const mk = (kind) => class {
    set src(u) { preloaded.push(u); if (instantMedia) setImmediate(() => (this.onload || this.oncanplaythrough)?.()); }
  };
  const sandbox = {
    document: doc, location: { search: `?show=${showId}`, href: "" },
    window: { location: {} }, URLSearchParams, console,
    localStorage: { getItem: (k) => store[k] ?? null, setItem: (k, v) => { store[k] = v; } },
    fetch: async (url) => ({ ok: true, json: async () => api(url) }),
    Image: mk("img"), Audio: mk("audio"),
    setTimeout: (fn, ms) => { timers.push({ fn, ms }); return timers.length; },
    clearTimeout() {}, encodeURIComponent,
  };
  sandbox.window.document = doc;
  vm.createContext(sandbox);
  vm.runInContext(lastScript(html), sandbox);
  const flush = () => new Promise((r) => setImmediate(r));
  return { els, store, timers, preloaded, flush,
    fireTimers: async () => { for (const t of timers.splice(0)) t.fn(); await flush(); await flush(); } };
}

const quiz = {
  id: "abc", quizName: "Testquiz",
  screens: Array.from({ length: 5 }, (_, i) => ({
    question: `Q${i + 1}`, answer: `A${i + 1}`, imageQuestionType: "single-question",
    mediaFileUrl: `https://cdn/img${i + 1}.jpg`, audioFileUrl: `https://cdn/a${i + 1}.mp3`,
  })),
};
const musicShow = { id: "m1", quizName: "Show", sharedMp3Url: "https://cdn/shared.mp3",
  screens: quiz.screens.map((s) => ({ mediaFileUrl: s.mediaFileUrl, perSlideMp3Url: "" })) };

test("ImageQuiz: opening screen enables Start even when media never finishes loading", async () => {
  const r = run(read("bestefar-image/index.html"), { showId: "abc", api: () => quiz });
  await r.flush(); await r.flush();
  assert.strictEqual(r.els.quizLink.href, undefined, "waiting on first-question media");
  await r.fireTimers();
  assert.match(r.els.quizLink.href, /dynamicQuiz\.html\?show=abc$/);
  assert.ok(!r.els["share-loading"].classList.has("visible"));
  const cached = JSON.parse(r.store.iq_preview_json);
  assert.strictEqual(cached.questions.length, 5);
});

test("ImageQuiz: only the first question's media is preloaded (not the whole quiz)", async () => {
  const r = run(read("bestefar-image/index.html"), { showId: "abc", api: () => quiz });
  await r.flush(); await r.flush();
  assert.deepStrictEqual(r.preloaded.sort(), ["https://cdn/a1.mp3", "https://cdn/img1.jpg"]);
});

test("ImageQuiz: proceeds immediately (no timer needed) when media loads fine", async () => {
  const r = run(read("bestefar-image/index.html"), { showId: "abc", api: () => quiz, instantMedia: true });
  for (let i = 0; i < 6; i++) await r.flush();
  assert.match(r.els.quizLink.href || "", /dynamicQuiz\.html\?show=abc$/);
});

test("ImageQuiz: maps question types and options correctly", async () => {
  const q = { ...quiz, screens: [
    { question: "m", imageQuestionType: "multiple-questions", optionA: "a", optionB: "b", optionC: "", answer: "a" },
    { question: "b", imageQuestionType: "bit-by-bit", bitSize: 4, bitRemovalDuration: 48 },
  ] };
  const r = run(read("bestefar-image/index.html"), { showId: "abc", api: () => q });
  await r.flush(); await r.flush(); await r.fireTimers();
  const qs = JSON.parse(r.store.iq_preview_json).questions;
  assert.strictEqual(qs[0].type, "multiple"); assert.deepStrictEqual(qs[0].options, ["a", "b"]);
  assert.strictEqual(qs[1].type, "bit"); assert.strictEqual(qs[1].bitSize, 4);
});

test("ImageMusic: opening screen no longer hangs on stuck media, still caches the show", async () => {
  const r = run(read("bestefar-imagemusic/index.html"), { showId: "m1", api: () => musicShow });
  await r.flush(); await r.flush();
  assert.ok(r.els["share-loading"].classList.has("visible"), "still loading before the cap");
  assert.strictEqual(r.store.im_preview_json, undefined);
  await r.fireTimers();
  assert.ok(!r.els["share-loading"].classList.has("visible"));
  assert.strictEqual(JSON.parse(r.store.im_preview_json).quizName, "Show");
});

test("ImageMusic: still preloads every slide + the shared track (feature preserved)", async () => {
  const r = run(read("bestefar-imagemusic/index.html"), { showId: "m1", api: () => musicShow });
  await r.flush(); await r.flush();
  assert.strictEqual(r.preloaded.length, 6); // 5 images + shared mp3
  assert.ok(r.preloaded.includes("https://cdn/shared.mp3"));
});

// ── dynamicQuiz.html: direct open / no localStorage ─────────────────────────
test("dynamicQuiz: maps raw API data itself when nothing is cached (was 'No questions found')", async () => {
  const html = read("bestefar-image/data/screens/dynamicQuiz.html");
  const src = html.slice(html.indexOf("async function loadQuiz()"), html.indexOf("// Preloads a single question's"));
  let shown = null, qtext = "";
  const sandbox = {
    location: { search: "?show=abc" }, URLSearchParams, console, document: { title: "" },
    localStorage: { getItem: () => null, setItem() {} },
    fetch: async () => ({ ok: true, json: async () => quiz }),
    containerSingle: { querySelector: () => ({ set textContent(v) { qtext = v; } }) },
    singleContainer: { classList: { remove() {} } },
    IQ_STORAGE_KEY: "iq_preview_json", IQ_API_BASE: "https://api/x",
    buildDrawerList() {}, preloadFirstQuestionMedia: async () => {}, showQuestion: (i) => { shown = i; },
  };
  vm.createContext(sandbox);
  vm.runInContext(src + "\nthis.loadQuiz = loadQuiz;", sandbox);
  await sandbox.loadQuiz();
  assert.strictEqual(qtext, "");
  assert.strictEqual(shown, 0);
  assert.strictEqual(sandbox.questions.length, 5);
});
