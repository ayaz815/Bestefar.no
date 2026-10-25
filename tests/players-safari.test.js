// Safari / iPhone: those browsers lack requestFullscreen (iPhone), only have
// webkitRequestFullscreen (older Safari) and BroadcastChannel (< 15.4).
// Unguarded use used to THROW at page load and stop the whole player.
const test = require("node:test");
const assert = require("node:assert");
const fs = require("fs");
const path = require("path");
const vm = require("vm");

const root = process.env.PLAYERS_ROOT || path.join(__dirname, "..", "httpd.www");
const read = (p) => fs.readFileSync(path.join(root, p), "utf8");
const IM = read("bestefar-imagemusic/data/screens/imageMusic.html");
const DQ = read("bestefar-image/data/screens/dynamicQuiz.html");

// The fullscreen IIFE of each page, cut out between its marker and the next section.
const imIife = IM.slice(IM.indexOf("(function () {\n        const overlay"), IM.indexOf("// ── State"));
const dqIife = DQ.slice(DQ.indexOf("(function () {\n        function tryFs"), DQ.indexOf("// ── STATE"));

function env(elOverrides) {
  const calls = [], listeners = {};
  const overlay = { parentNode: {}, addEventListener(t, f) { listeners["overlay:" + t] = f; }, remove() { this.parentNode = null; } };
  const el = {};
  for (const [k, v] of Object.entries(elOverrides)) el[k] = v === "spy" ? (() => { calls.push(k); return Promise.resolve(); }) : v;
  const document = {
    documentElement: el, fullscreenElement: null,
    getElementById: () => overlay,
    addEventListener(t, f) { listeners[t] = f; }, removeEventListener() {},
  };
  return { document, calls, listeners, overlay };
}
const pages = { "imageMusic.html": imIife, "dynamicQuiz.html": dqIife };

for (const [name, code] of Object.entries(pages)) {
  test(`${name}: page start-up does not throw when the browser has NO fullscreen API (iPhone Safari)`, () => {
    const e = env({});
    assert.doesNotThrow(() => vm.runInContext(code, vm.createContext({ document: e.document })));
    for (const l of Object.values(e.listeners)) assert.doesNotThrow(() => l({ stopPropagation() {} }));
  });

  test(`${name}: uses the webkit-prefixed API when that's all there is (older Safari)`, () => {
    const e = env({ webkitRequestFullscreen: "spy" });
    vm.runInContext(code, vm.createContext({ document: e.document }));
    assert.deepStrictEqual(e.calls, ["webkitRequestFullscreen"]);
  });

  test(`${name}: still uses the standard API where it exists (no regression)`, () => {
    const e = env({ requestFullscreen: "spy" });
    vm.runInContext(code, vm.createContext({ document: e.document }));
    assert.deepStrictEqual(e.calls, ["requestFullscreen"]);
  });

  test(`${name}: a throwing/rejecting request is swallowed`, async () => {
    const e = env({ requestFullscreen: () => { throw new Error("denied"); } });
    assert.doesNotThrow(() => vm.runInContext(code, vm.createContext({ document: e.document })));
    const r = env({ requestFullscreen: () => Promise.reject(new Error("denied")) });
    assert.doesNotThrow(() => vm.runInContext(code, vm.createContext({ document: r.document })));
    await new Promise((res) => setImmediate(res));
  });
}

test("ImageQuiz: BroadcastChannel is optional (missing before Safari 15.4)", () => {
  const stmt = DQ.slice(DQ.indexOf("const bc ="), DQ.indexOf("let displayWindow"));
  const without = vm.createContext({});
  assert.doesNotThrow(() => vm.runInContext(stmt + "\nthis.bc = bc;", without));
  assert.doesNotThrow(() => without.bc.postMessage({}));
  let made = 0;
  const withBc = vm.createContext({ BroadcastChannel: function (n) { made++; this.name = n; } });
  vm.runInContext(stmt + "\nthis.bc = bc;", withBc);
  assert.strictEqual(made, 1);
  assert.strictEqual(withBc.bc.name, "quiz-display");
});

test("no player page calls requestFullscreen unguarded any more", () => {
  for (const f of ["bestefar-image/index.html", "bestefar-image/data/screens/dynamicQuiz.html",
                   "bestefar-imagemusic/index.html", "bestefar-imagemusic/data/screens/imageMusic.html"]) {
    assert.doesNotMatch(read(f), /documentElement\.requestFullscreen\(\)/, f);
  }
});
