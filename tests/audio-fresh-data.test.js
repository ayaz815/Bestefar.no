// AudioQuiz player must show the latest saved show, not a stale cached copy.
const test = require("node:test");
const assert = require("node:assert");
const fs = require("fs");
const path = require("path");
const vm = require("vm");

const root = process.env.PLAYERS_ROOT || path.join(__dirname, "..", "httpd.www");
const src = fs.readFileSync(path.join(root, "bestefar-audio/data/script.js"), "utf8");
const a = src.indexOf("const AQ_STORAGE_KEY");
const b = src.indexOf("// Plain <a href");
// Skip guardDirectDeepLinkEntry (needs sessionStorage); keep helpers + loader.
const g0 = src.indexOf("(function guardDirectDeepLinkEntry");
const g1 = src.indexOf("let _audioQuizDataPromise");
const code = src.slice(a, g0) + src.slice(g1, b);

const form = (n) => Array.from({ length: n }, (_, i) => ({ page: i + 1, musicName: "t" + (i + 1) }));
function run({ path: p = "/bestefar-audio/index.html", search = "?show=abc", cache, api, apiFails }) {
  const store = cache ? { aq_preview_json: JSON.stringify(cache) } : {};
  let fetched = 0;
  const sb = {
    console, URLSearchParams, setTimeout, clearTimeout, AbortController,
    window: { location: { pathname: p, search, href: "https://x" + p + search }, addEventListener() {} },
    location: { search },
    localStorage: { getItem: (k) => store[k] ?? null, setItem: (k, v) => (store[k] = v) },
    fetch: async (url) => {
      if (String(url).includes("get-audio-quiz")) {
        fetched++;
        if (apiFails) throw new Error("offline");
        return { ok: true, json: async () => api };
      }
      return { ok: false };
    },
  };
  vm.createContext(sb);
  vm.runInContext(code + "\nthis.load = loadAudioQuizData;", sb);
  return sb.load().then((d) => ({ d, fetched: () => fetched, store }));
}
const stale = { id: "abc", quizName: "Q", screen1: {}, screen2: {}, screen3: {} };
const fresh = { id: "abc", quizName: "Q", quizForms: form(4) };

test("title screen with ?show= fetches fresh data even when a cache exists (4 tunes, not 3)", async () => {
  const r = await run({ cache: stale, api: fresh });
  assert.strictEqual(Object.keys(r.d).filter((k) => /^screen\d+$/.test(k)).length, 4);
  assert.strictEqual(r.fetched(), 1);
  assert.ok(JSON.parse(r.store.aq_preview_json).screen4, "cache is refreshed");
});
test("melody list / deep screens also fetch fresh (4 tunes, not 3)", async () => {
  const r = await run({ path: "/bestefar-audio/data/screens/show.html", cache: stale, api: fresh });
  assert.strictEqual(Object.keys(r.d).filter((k) => /^screen\d+$/.test(k)).length, 4);
  assert.strictEqual(r.fetched(), 1);
  assert.ok(JSON.parse(r.store.aq_preview_json).screen4, "cache is refreshed");
});
test("deep screen without ?show= (generator Preview) still uses the cache, no fetch", async () => {
  const r = await run({ path: "/bestefar-audio/data/screens/screen.html", search: "", cache: stale, api: fresh });
  assert.strictEqual(Object.keys(r.d).filter((k) => /^screen\d+$/.test(k)).length, 3);
  assert.strictEqual(r.fetched(), 0);
});
test("deep screen offline falls back to cache", async () => {
  const r = await run({ path: "/bestefar-audio/data/screens/show.html", cache: stale, apiFails: true });
  assert.strictEqual(r.d.screen3 !== undefined, true);
});
test("offline: falls back to cached copy of the same show", async () => {
  const r = await run({ cache: stale, apiFails: true });
  assert.strictEqual(r.d.screen3 !== undefined, true);
});
test("cache of a different show is never used as fallback", async () => {
  const r = await run({ cache: { ...stale, id: "other" }, apiFails: true });
  assert.strictEqual(r.d.screen1, undefined);
});
test("generator Preview (no ?show=) still uses localStorage and does not fetch", async () => {
  const r = await run({ search: "", cache: stale, api: fresh });
  assert.strictEqual(r.fetched(), 0);
  assert.ok(r.d.screen3);
});
