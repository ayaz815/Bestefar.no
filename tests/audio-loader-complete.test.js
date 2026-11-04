// Complete tests for the AudioQuiz data loader (data/script.js): freshness on
// every page, cache handling, failure modes, id parsing, fetch options, bfcache.
const test = require("node:test");
const assert = require("node:assert");
const fs = require("fs");
const path = require("path");
const vm = require("vm");

const root = process.env.PLAYERS_ROOT || path.join(__dirname, "..", "httpd.www");
const src = fs.readFileSync(path.join(root, "bestefar-audio/data/script.js"), "utf8");
const a = src.indexOf("const AQ_STORAGE_KEY");
const b = src.indexOf("// Plain <a href");
const g0 = src.indexOf("(function guardDirectDeepLinkEntry");
const g1 = src.indexOf("let _audioQuizDataPromise");
const code = src.slice(a, g0) + src.slice(g1, b);

const forms = (n, tag = "t") => Array.from({ length: n }, (_, i) => ({ page: i + 1, musicName: tag + (i + 1) }));
const count = (d) => Object.keys(d).filter((k) => /^screen\d+$/.test(k)).length;
const KEY = "aq_preview_json";

function run({ p = "/bestefar-audio/index.html", search = "?show=abc", href, cache, rawCache, api, apiStatus = 200, apiFails, apiHang, staticJson } = {}) {
  const store = {};
  if (cache) store[KEY] = JSON.stringify(cache);
  if (rawCache !== undefined) store[KEY] = rawCache;
  const calls = [];
  const listeners = {};
  let reloads = 0;
  const loc = { search, pathname: p, href: href || "https://x" + p + search, reload: () => reloads++ };
  const sb = {
    console, URLSearchParams, setTimeout, clearTimeout, AbortController, decodeURIComponent,
    window: { location: loc, addEventListener: (n, f) => (listeners[n] = f) },
    location: loc,
    localStorage: { getItem: (k) => store[k] ?? null, setItem: (k, v) => (store[k] = v) },
    fetch: async (url, opts) => {
      calls.push({ url: String(url), opts });
      if (String(url).includes("get-audio-quiz")) {
        if (apiFails) throw new Error("offline");
        if (apiHang) return new Promise((_, rej) => opts.signal.addEventListener("abort", () => rej(Object.assign(new Error("aborted"), { name: "AbortError" }))));
        return { ok: apiStatus === 200, status: apiStatus, json: async () => api };
      }
      return staticJson ? { ok: true, json: async () => staticJson } : { ok: false };
    },
  };
  vm.createContext(sb);
  vm.runInContext(code + "\nthis.load = loadAudioQuizData; this.map = quizFormsArrayToScreenMap; this.has = hasScreenData;", sb);
  return { sb, store, calls, listeners, reloads: () => reloads, load: () => sb.load() };
}
const apiCalls = (r) => r.calls.filter((c) => c.url.includes("get-audio-quiz"));
const stale = { id: "abc", screen1: { musicName: "old" }, screen2: {}, screen3: {} };
const fresh = { id: "abc", quizName: "Q", quizForms: forms(4, "new") };

// ── freshness on every page type ──────────────────────────────────────────
for (const p of ["/bestefar-audio/index.html", "/bestefar-audio/data/screens/show.html", "/bestefar-audio/data/screens/screen.html", "/bestefar-audio/data/screens/screen17.html"]) {
  test(`fresh data wins over cache on ${p}`, async () => {
    const r = run({ p, cache: stale, api: fresh });
    const d = await r.load();
    assert.strictEqual(count(d), 4);
    assert.strictEqual(d.screen1.musicName, "new1");
    assert.strictEqual(apiCalls(r).length, 1);
    assert.strictEqual(JSON.parse(r.store[KEY]).screen4.musicName, "new4");
  });
}
test("edited tune name/text is reflected on the next load (same count)", async () => {
  const r = run({ cache: { ...stale, screen1: { musicName: "OLD" } }, api: { id: "abc", quizForms: [{ page: 1, musicName: "EDITED" }, { page: 2 }, { page: 3 }] } });
  assert.strictEqual((await r.load()).screen1.musicName, "EDITED");
});
test("tune removed in the generator disappears (3 -> 2)", async () => {
  const r = run({ cache: stale, api: { id: "abc", quizForms: forms(2) } });
  const d = await r.load();
  assert.strictEqual(count(d), 2);
  assert.strictEqual(d.screen3, undefined);
});
test("loader is memoised per page: one fetch even when called repeatedly", async () => {
  const r = run({ cache: stale, api: fresh });
  await Promise.all([r.load(), r.load(), r.load()]);
  assert.strictEqual(apiCalls(r).length, 1);
});

// ── fetch options ─────────────────────────────────────────────────────────
test("API fetch bypasses the HTTP cache (no-store) and is abortable", async () => {
  const r = run({ api: fresh });
  await r.load();
  const c = apiCalls(r)[0];
  assert.strictEqual(c.opts.cache, "no-store");
  assert.ok(c.opts.signal);
  assert.ok(c.url.endsWith("/get-audio-quiz/abc"));
});

// ── show-id parsing ───────────────────────────────────────────────────────
test("show id recovered from href when location.search is mangled", async () => {
  const r = run({ search: "", href: "https://x/bestefar-audio/index.html#?x&show=abc", api: fresh });
  await r.load();
  assert.ok(apiCalls(r)[0].url.endsWith("/abc"));
});
test("show id is trimmed", async () => {
  const r = run({ search: "?show=%20abc%20", api: fresh });
  await r.load();
  assert.ok(apiCalls(r)[0].url.endsWith("/abc"));
});

// ── cache validity ────────────────────────────────────────────────────────
test("cache from a different show is discarded and never used as fallback", async () => {
  const r = run({ cache: { ...stale, id: "other" }, apiFails: true });
  assert.strictEqual((await r.load()).screen1, undefined);
});
test("poisoned (screen-less) cache is not reused", async () => {
  const r = run({ cache: { id: "abc", quizName: "Q" }, apiFails: true });
  assert.strictEqual(count(await r.load()), 0);
});
test("corrupt JSON in localStorage does not crash the loader", async () => {
  const r = run({ rawCache: "{not json", api: fresh });
  assert.strictEqual(count(await r.load()), 4);
});
test("localStorage.setItem throwing (quota/private mode) still returns fresh data", async () => {
  const r = run({ api: fresh });
  r.sb.localStorage.setItem = () => { throw new Error("quota"); };
  assert.strictEqual(count(await r.load()), 4);
});

// ── failure modes ─────────────────────────────────────────────────────────
test("offline: same-show cache is the fallback on every page", async () => {
  for (const p of ["/bestefar-audio/index.html", "/bestefar-audio/data/screens/show.html"]) {
    const r = run({ p, cache: stale, apiFails: true });
    assert.strictEqual(count(await r.load()), 3);
  }
});
test("API HTTP 500: falls back to cache, error recorded", async () => {
  const r = run({ cache: stale, api: {}, apiStatus: 500 });
  assert.strictEqual(count(await r.load()), 3);
  assert.match(r.sb.window._aqErr, /HTTP 500/);
});
test("API returns empty quizForms: not cached, existing cache kept as fallback", async () => {
  const r = run({ cache: stale, api: { id: "abc", quizForms: [] } });
  const d = await r.load();
  assert.strictEqual(count(d), 3);
  assert.strictEqual(JSON.parse(r.store[KEY]).screen1.musicName, "old");
  assert.match(r.sb.window._aqErr, /no screens/);
});
test("empty API response with no cache is not stored", async () => {
  const r = run({ api: { id: "abc", quizForms: [] } });
  await r.load();
  assert.strictEqual(r.store[KEY], undefined);
});
test("stalled request is aborted by the timeout and cache is used", { timeout: 15000 }, async () => {
  const r = run({ cache: stale, apiHang: true });
  assert.strictEqual(count(await r.load()), 3);
  assert.match(r.sb.window._aqErr, /AbortError/);
});
test("no cache and API down: static content.json is the last resort", async () => {
  const r = run({ apiFails: true, staticJson: { screen1: { musicName: "static" } } });
  assert.strictEqual((await r.load()).screen1.musicName, "static");
});
test("nothing available: resolves to {} (never throws/hangs)", async () => {
  assert.strictEqual(Object.keys(await run({ apiFails: true }).load()).length, 0);
});

// ── Preview (no show id) must not change ──────────────────────────────────
for (const p of ["/bestefar-audio/index.html", "/bestefar-audio/data/screens/show.html"]) {
  test(`generator Preview (no ?show=) uses localStorage, no API call, on ${p}`, async () => {
    const r = run({ p, search: "", cache: { screen1: { musicName: "p" }, screen2: {} }, api: fresh });
    const d = await r.load();
    assert.strictEqual(count(d), 2);
    assert.strictEqual(apiCalls(r).length, 0);
  });
}

// ── helpers ───────────────────────────────────────────────────────────────
test("quizFormsArrayToScreenMap keys by page and carries quizName", () => {
  const m = run().sb.map([{ page: 2, musicName: "b" }, { page: 5, musicName: "e" }], "Quiz");
  assert.deepStrictEqual(Object.keys(m).filter((k) => /^screen/.test(k)).sort(), ["screen2", "screen5"]);
  assert.strictEqual(m.screen5.quizName, "Quiz");
});
test("hasScreenData", () => {
  const { has } = run().sb;
  assert.ok(has({ screen1: {} }));
  assert.ok(!has({ id: "x", quizName: "q" }));
  assert.ok(!has(null));
});

// ── bfcache ───────────────────────────────────────────────────────────────
test("pageshow from bfcache reloads when a show id is present", () => {
  const r = run();
  r.listeners.pageshow({ persisted: true });
  assert.strictEqual(r.reloads(), 1);
});
test("normal pageshow, or no show id, does not reload", () => {
  const r = run();
  r.listeners.pageshow({ persisted: false });
  const r2 = run({ search: "" });
  r2.listeners.pageshow({ persisted: true });
  assert.strictEqual(r.reloads() + r2.reloads(), 0);
});
