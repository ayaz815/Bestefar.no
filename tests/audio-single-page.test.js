// AudioQuiz single-page shell: player.html hosts every screen in an iframe so
// the top document never navigates and fullscreen survives screen changes.
const test = require("node:test");
const assert = require("node:assert");
const fs = require("fs");
const path = require("path");
const vm = require("vm");

const root = process.env.PLAYERS_ROOT || path.join(__dirname, "..", "httpd.www");
const audio = path.join(root, "bestefar-audio");
const read = (f) => fs.readFileSync(path.join(audio, f), "utf8");
const script = read("data/script.js");
const indexHtml = read("index.html");
const shell = fs.existsSync(path.join(audio, "player.html")) ? read("player.html") : "";
const inlineScripts = (html) => [...html.matchAll(/<script>([\s\S]*?)<\/script>/g)].map((m) => m[1]);

// ── player.html (shell) ───────────────────────────────────────────────────
test("shell exists and hosts exactly one full-size iframe", () => {
  assert.ok(shell, "player.html missing");
  assert.strictEqual((shell.match(/<iframe/g) || []).length, 1);
  assert.match(shell, /id="aqFrame"/);
  assert.match(shell, /allow="autoplay; fullscreen"/);
  assert.match(shell, /100dvh/);
  assert.match(shell, /name="viewport"/);
});
function runShell(search, hash = "") {
  const frame = { src: "" };
  const sb = { window: { location: { search, hash } }, document: { getElementById: (id) => (id === "aqFrame" ? frame : null) } };
  vm.createContext(sb);
  vm.runInContext(inlineScripts(shell)[0], sb);
  return frame.src;
}
test("shell forwards the query string (?show=, ?screen=) to the title screen", () => {
  assert.strictEqual(runShell("?screen=1&show=abc&autostart=1"), "index.html?screen=1&show=abc&autostart=1");
  assert.strictEqual(runShell(""), "index.html");
  assert.strictEqual(runShell("?show=abc", "#x"), "index.html?show=abc#x");
});
test("shell never navigates the top document by itself", () => {
  assert.doesNotMatch(inlineScripts(shell).join("\n"), /location\.(href|replace|assign)\s*[=(]/);
  assert.doesNotMatch(shell, /<meta[^>]+http-equiv="refresh"/i);
});

// ── index.html entry redirect ─────────────────────────────────────────────
function runIndexHead(framed, search = "?show=abc", hash = "") {
  const win = { location: { search, hash, replaced: null, replace(u) { this.replaced = u; } } };
  win.self = win;
  win.top = framed ? {} : win;
  const style = {};
  const sb = { window: win, document: { documentElement: { style } } };
  vm.createContext(sb);
  const s = inlineScripts(indexHtml).find((x) => x.includes("player.html"));
  assert.ok(s, "index.html entry redirect missing");
  vm.runInContext(s, sb);
  return { replaced: win.location.replaced, style };
}
test("index.html opened at top level redirects to the shell, keeping ?show=", () => {
  const r = runIndexHead(false, "?screen=1&show=abc&autostart=1");
  assert.strictEqual(r.replaced, "player.html?screen=1&show=abc&autostart=1");
  assert.strictEqual(r.style.visibility, "hidden");
});
test("index.html inside the shell iframe does not redirect (no loop)", () => {
  const r = runIndexHead(true);
  assert.strictEqual(r.replaced, null);
});
test("the entry redirect is the first script in index.html (runs before render)", () => {
  assert.ok(indexHtml.indexOf("player.html") < indexHtml.indexOf("cdn.tailwindcss.com") + 0 || indexHtml.indexOf("player.html") < indexHtml.indexOf("./data/script.js"));
});

// ── fullscreen targets the TOP document ───────────────────────────────────
const fsBlock = script.slice(script.indexOf("// The quiz runs inside player.html"), script.indexOf("// ── END AUTO FULLSCREEN"));
function mkDoc(name, extra = {}) {
  const handlers = {};
  const d = { name, fullscreenElement: null, calls: [], addEventListener(t, fn, o) { (handlers[t] ||= []).push({ fn, once: o && o.once }); },
    fire(t) { for (const h of (handlers[t] || []).splice(0)) { h.fn(); if (!h.once) (handlers[t] ||= []).push(h); } },
    ...extra };
  d.documentElement = { requestFullscreen() { d.calls.push("enter"); d.fullscreenElement = d.documentElement; return Promise.resolve(); } };
  return d;
}
function frame({ crossOrigin = false } = {}) {
  const topDoc = mkDoc("top");
  const inner = mkDoc("inner");
  const topWin = { document: topDoc };
  const innerWin = { top: crossOrigin ? new Proxy({}, { get() { throw new Error("SecurityError"); } }) : topWin };
  if (crossOrigin) innerWin.top = new Proxy({}, { get(_, k) { if (k === "document") throw new Error("SecurityError"); } });
  inner.defaultView = innerWin;
  topDoc.defaultView = topWin;
  return { topDoc, inner };
}
function loadFs(doc) {
  const sb = { document: doc, console };
  vm.createContext(sb);
  vm.runInContext(fsBlock.replace(/^setupAutoFullscreen\(\);$/m, "") + "\nthis.set = setupAutoFullscreen; this.get = getFullscreenDoc;", sb);
  return sb;
}
test("framed screen: getFullscreenDoc returns the top document", () => {
  const { topDoc, inner } = frame();
  assert.strictEqual(loadFs(inner).get(inner), topDoc);
});
test("unframed (top-level) screen: getFullscreenDoc returns its own document", () => {
  const d = mkDoc("solo");
  d.defaultView = {}; d.defaultView.top = d.defaultView;
  assert.strictEqual(loadFs(d).get(d), d);
});
test("cross-origin / inaccessible top falls back to own document without throwing", () => {
  const { inner } = frame({ crossOrigin: true });
  assert.strictEqual(loadFs(inner).get(inner), inner);
});
test("first tap in the iframe fullscreens the TOP document, not the iframe's", () => {
  const { topDoc, inner } = frame();
  const sb = loadFs(inner);
  sb.set(inner);
  inner.fire("click");
  assert.deepStrictEqual(topDoc.calls, ["enter"]);
  assert.deepStrictEqual(inner.calls, []);
});
test("already fullscreen on top (arrived from a previous screen): later screens do nothing", () => {
  const { topDoc, inner } = frame();
  topDoc.fullscreenElement = {};
  const sb = loadFs(inner);
  sb.set(inner);
  inner.fire("click"); inner.fire("keydown");
  assert.deepStrictEqual(topDoc.calls, []);
});
test("fullscreen persists across 5 simulated screen loads with one tap total", () => {
  const { topDoc } = frame();
  let enters = 0;
  for (let i = 0; i < 5; i++) {
    const inner = mkDoc("inner" + i);
    inner.defaultView = { top: topDoc.defaultView };
    const sb = loadFs(inner);
    sb.set(inner);
    if (i === 0) inner.fire("click"); // user taps once on the title screen
    enters = topDoc.calls.length;
  }
  assert.strictEqual(enters, 1);
});

// ── double-tap toggle + button also use the top document ─────────────────
test("toggleFullScreen / enterFullscreen resolve the top document", () => {
  assert.match(script, /function toggleFullScreen\(\)\s*\{\s*const target = getFullscreenDoc\(document\);/);
  assert.match(script, /target\.exitFullscreen\(\)/);
  assert.match(script, /function enterFullscreen\(\)\s*\{\s*const elem = getFullscreenDoc\(document\)\.documentElement/);
});

// ── deep-link guard ───────────────────────────────────────────────────────
const g0 = script.indexOf("(function guardDirectDeepLinkEntry");
const g1 = script.indexOf("let _audioQuizDataPromise");
function runGuard({ framed, flag, path: p = "/bestefar-audio/data/screens/show.html" }) {
  const store = flag ? { aq_internal_nav: "1" } : {};
  const win = { location: { pathname: p, search: "?show=abc", replaced: null, replace(u) { this.replaced = u; } } };
  win.self = win; win.top = framed ? {} : win;
  const sb = { window: win, sessionStorage: { getItem: (k) => store[k] ?? null, removeItem: (k) => delete store[k] } };
  vm.createContext(sb);
  vm.runInContext(script.slice(g0, g1), sb);
  return win.location.replaced;
}
test("guard: framed + internal nav flag stays on the screen", () => assert.strictEqual(runGuard({ framed: true, flag: true }), null));
test("guard: framed without flag (pasted/refresh) goes back to the title screen with ?show=", () => assert.strictEqual(runGuard({ framed: true, flag: false }), "../../index.html?show=abc"));
test("guard: deep screen opened at top level is sent through index.html -> shell, even with the flag", () => assert.strictEqual(runGuard({ framed: false, flag: true }), "../../index.html?show=abc"));
test("guard: title screen (not a deep screen) is never redirected", () => assert.strictEqual(runGuard({ framed: false, flag: false, path: "/bestefar-audio/index.html" }), null));

// ── link integrity: every screen target exists, every screen loads script.js ─
const screens = fs.readdirSync(path.join(audio, "data/screens")).filter((f) => f.endsWith(".html"));
test("every screen and the title load data/script.js", () => {
  for (const f of ["index.html", ...screens.map((s) => "data/screens/" + s)]) {
    assert.match(read(f), /script\.js/, f);
  }
});
// show1.html / screen.html are an old, unused pair (nothing in the live flow
// links to them; screen.html points at a screen3.html that never existed).
const LEGACY = ["show1.html", "screen.html"];
const liveScreens = screens.filter((s) => !LEGACY.includes(s));
test("the live flow never links to the legacy show1.html / screen.html pair", () => {
  for (const f of ["index.html", "data/script.js", ...liveScreens.map((s) => "data/screens/" + s)]) {
    assert.doesNotMatch(read(f).replace(/^\s*\/\/.*$/gm, ""), /(?:\/|["'`])(?:show1|screen)\.html/, f);
  }
});
test("every literal .html navigation target in screens/index/script resolves to a real file", () => {
  const files = ["index.html", ...liveScreens.map((s) => "data/screens/" + s)];
  const bad = [];
  for (const f of files) {
    const txt = read(f);
    const re = /(?:navigateToScreen\(\s*|href=)["'`]((?:\.\.?\/)[^"'`?$]*\.html)/g;
    let m;
    while ((m = re.exec(txt))) {
      if (!fs.existsSync(path.join(audio, path.dirname(f), m[1]))) bad.push(f + " -> " + m[1]);
    }
  }
  assert.deepStrictEqual(bad, []);
});
test("no screen breaks out of the iframe (no top/parent navigation, no target=_top)", () => {
  for (const f of ["index.html", ...screens.map((s) => "data/screens/" + s), "data/script.js"]) {
    const t = read(f);
    assert.doesNotMatch(t, /window\.(top|parent)\.location/, f);
    assert.doesNotMatch(t, /target=["']_(top|parent)["']/, f);
  }
});
test("nothing external still links to a screen file directly (entry is index.html / player.html)", () => {
  const cat = fs.readFileSync(path.join(root, "assets/frontend/js/sporreleker.js"), "utf8");
  assert.match(cat, /bestefar-audio\/index\.html\?/);
  assert.doesNotMatch(cat, /bestefar-audio\/data\/screens/);
});
