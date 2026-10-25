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

function fakeDoc({ request = "requestFullscreen", rejects = false, throws = false } = {}) {
  const handlers = {}, calls = [];
  const el = {};
  if (request) el[request] = () => {
    calls.push(request);
    if (throws) throw new Error("denied");
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

test("it is wired up on load of every screen (script runs it once at top level)", () => {
  const { doc, calls } = fakeDoc();
  vm.runInContext(block, vm.createContext({ document: doc, console }));
  doc.fire("click");
  assert.strictEqual(calls.length, 1);
});

test("first tap enters fullscreen", () => {
  const { doc, calls } = fakeDoc();
  load(doc).setupAutoFullscreen(doc);
  doc.fire("click");
  assert.deepStrictEqual(calls, ["requestFullscreen"]);
});

test("first key press enters fullscreen too", () => {
  const { doc, calls } = fakeDoc();
  load(doc).setupAutoFullscreen(doc);
  doc.fire("keydown");
  assert.strictEqual(calls.length, 1);
});

test("one-shot: later taps do not re-request (so the double-tap exit still works)", () => {
  const { doc, calls } = fakeDoc();
  load(doc).setupAutoFullscreen(doc);
  doc.fire("click"); doc.fire("click"); doc.fire("click");
  assert.strictEqual(calls.length, 1);
});

test("does nothing when already fullscreen", () => {
  const { doc, calls } = fakeDoc();
  doc.fullscreenElement = {};
  load(doc).setupAutoFullscreen(doc);
  doc.fire("click");
  assert.strictEqual(calls.length, 0);
});

test("falls back to the webkit-prefixed API (older Safari / iPad)", () => {
  const { doc, calls } = fakeDoc({ request: "webkitRequestFullscreen" });
  load(doc).setupAutoFullscreen(doc);
  doc.fire("click");
  assert.deepStrictEqual(calls, ["webkitRequestFullscreen"]);
});

test("no Fullscreen API (iPhone Safari): silently does nothing, never throws", () => {
  const { doc } = fakeDoc({ request: null });
  load(doc).setupAutoFullscreen(doc);
  assert.doesNotThrow(() => doc.fire("click"));
});

test("a rejected or throwing request never breaks the quiz", async () => {
  for (const opts of [{ rejects: true }, { throws: true }]) {
    const { doc, calls } = fakeDoc(opts);
    load(doc).setupAutoFullscreen(doc);
    assert.doesNotThrow(() => doc.fire("click"));
    await new Promise((r) => setImmediate(r)); // no unhandled rejection
    assert.strictEqual(calls.length, 1);
  }
});
