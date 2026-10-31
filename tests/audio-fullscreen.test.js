// `node --test tests/` - AudioQuiz auto-fullscreen (same behaviour as the
// BildeQuiz / BilderMusikk players: first tap/key on each screen).
const test = require("node:test");
const assert = require("node:assert");
const fs = require("fs");
const path = require("path");
const vm = require("vm");

const root = process.env.PLAYERS_ROOT || path.join(__dirname, "..", "httpd.www");
const src = fs.readFileSync(path.join(root, "bestefar-audio/data/script.js"), "utf8");
const block = src.slice(src.indexOf("// ── AUTO FULLSCREEN"), src.indexOf("// ── END AUTO FULLSCREEN"));

function fakeDoc({ request = "requestFullscreen", rejects = false, throws = false, grant = false } = {}) {
  const handlers = {}, calls = [];
  const el = {};
  if (request) el[request] = () => {
    calls.push(request);
    if (throws) throw new Error("denied");
    if (grant) doc.fullscreenElement = {};
    return rejects ? Promise.reject(new Error("denied")) : Promise.resolve();
  };
  const doc = {
    documentElement: el, fullscreenElement: null,
    addEventListener(type, fn, opts) { (handlers[type] ||= []).push({ fn, once: opts && opts.once }); },
    fire(type) { for (const h of (handlers[type] || []).splice(0)) { h.fn(); if (!h.once) (handlers[type] ||= []).push(h); } },
  };
  return { doc, calls, handlers };
}
const load = (doc) => {
  const sb = { document: doc, console };
  vm.createContext(sb);
  // Drop the trailing auto-run call so each test registers handlers once.
  const fnOnly = block.replace(/^setupAutoFullscreen\(\);$/m, "");
  vm.runInContext(fnOnly + "\nthis.setupAutoFullscreen = setupAutoFullscreen;", sb);
  return sb;
};

test("block is present in the player script", () => assert.ok(block.includes("setupAutoFullscreen")));

test("script runs it once at top level, so every screen requests fullscreen on load", () => {
  const { doc, calls } = fakeDoc({ grant: true });
  vm.runInContext(block, vm.createContext({ document: doc, console }));
  assert.deepStrictEqual(calls, ["requestFullscreen"]);
});

test("reached by tapping START (browser grants it): fullscreen at load, later taps don't re-request", () => {
  const { doc, calls } = fakeDoc({ grant: true });
  load(doc).setupAutoFullscreen(doc);
  assert.strictEqual(calls.length, 1);
  doc.fire("click"); doc.fire("click");
  assert.strictEqual(calls.length, 1);
});

test("browser refused at load: the first tap enters fullscreen", () => {
  const { doc, calls } = fakeDoc(); // grant:false = request has no effect
  load(doc).setupAutoFullscreen(doc);
  assert.strictEqual(calls.length, 1);
  doc.fire("click");
  assert.strictEqual(calls.length, 2);
});

test("first key press is a fallback too", () => {
  const { doc, calls } = fakeDoc();
  load(doc).setupAutoFullscreen(doc);
  doc.fire("keydown");
  assert.strictEqual(calls.length, 2);
});

test("tap fallback is one-shot, so the existing double-tap exit still works", () => {
  const { doc, calls } = fakeDoc();
  load(doc).setupAutoFullscreen(doc);
  doc.fire("click"); doc.fire("click"); doc.fire("click");
  assert.strictEqual(calls.length, 2);
});

test("does nothing when already fullscreen", () => {
  const { doc, calls } = fakeDoc();
  doc.fullscreenElement = {};
  load(doc).setupAutoFullscreen(doc);
  doc.fire("click");
  assert.strictEqual(calls.length, 0);
});

test("falls back to the webkit-prefixed API (older Safari / iPad)", () => {
  const { doc, calls } = fakeDoc({ request: "webkitRequestFullscreen", grant: true });
  load(doc).setupAutoFullscreen(doc);
  assert.deepStrictEqual(calls, ["webkitRequestFullscreen"]);
});

test("no Fullscreen API (iPhone Safari): silently does nothing, never throws", () => {
  const { doc } = fakeDoc({ request: null });
  assert.doesNotThrow(() => load(doc).setupAutoFullscreen(doc));
  assert.doesNotThrow(() => doc.fire("click"));
});

test("a rejected or throwing request never breaks the quiz", async () => {
  for (const opts of [{ rejects: true }, { throws: true }]) {
    const { doc, calls } = fakeDoc(opts);
    assert.doesNotThrow(() => load(doc).setupAutoFullscreen(doc));
    assert.doesNotThrow(() => doc.fire("click"));
    await new Promise((r) => setImmediate(r)); // no unhandled rejection
    assert.strictEqual(calls.length, 2);
  }
});

// ── "must work on all screens" ───────────────────────────────────────────────
const audioRoot = path.join(root, "bestefar-audio");
const screens = ["index.html", ...fs.readdirSync(path.join(audioRoot, "data/screens")).filter((f) => f.endsWith(".html")).map((f) => "data/screens/" + f)];

test("every AudioQuiz screen loads the shared script that contains the fullscreen code", () => {
  assert.ok(screens.length >= 6, "expected index + all screen pages, got " + screens.join(", "));
  for (const f of screens) {
    const html = fs.readFileSync(path.join(audioRoot, f), "utf8");
    assert.match(html, /<script[^>]+src="[^"]*script\.js(\?v=\d+)?"/, f + " does not load script.js");
  }
});

test("fullscreen code runs before anything else, so a later error/redirect can't skip it", () => {
  assert.ok(src.indexOf("// ── AUTO FULLSCREEN") < src.indexOf("AQ_STORAGE_KEY"));
  // Run the WHOLE script in a bare sandbox where the rest of it blows up:
  const { doc, calls } = fakeDoc({ grant: true });
  doc.getElementById = () => { throw new Error("boom"); };
  doc.querySelectorAll = () => { throw new Error("boom"); };
  const sb = vm.createContext({ document: doc, console, location: { search: "", pathname: "/" }, window: {}, localStorage: {}, sessionStorage: {} });
  try { vm.runInContext(src, sb); } catch {}
  assert.strictEqual(calls.length, 1);
});
