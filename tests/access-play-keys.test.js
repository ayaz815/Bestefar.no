// `node --test tests/*.test.js` - players send the play key (Module 4a of ACCESS_CONTROL_PLAN.md).
const test = require("node:test");
const assert = require("node:assert");
const fs = require("fs");
const path = require("path");
const vm = require("vm");

const root = process.env.PLAYERS_ROOT || path.join(__dirname, "..", "httpd.www");
const read = (f) => fs.readFileSync(path.join(root, f), "utf8");

const PLAYERS = [
  { file: "bestefar-audio/data/script.js", type: "audio", storage: "aq_play_key" },
  { file: "bestefar-image/index.html", type: "image", storage: "iq_play_key" },
  { file: "bestefar-image/data/screens/dynamicQuiz.html", type: "image", storage: "iq_play_key" },
  { file: "bestefar-imagemusic/index.html", type: "imagemusic", storage: "im_play_key" },
];

// Pulls playKeyQuery() out of a player file and runs it with a fake URL/sessionStorage.
function loadPlayKeyQuery(file, { search = "", stored = {}, storageThrows = false } = {}) {
  const src = read(file);
  const start = src.indexOf("function playKeyQuery(");
  const end = src.indexOf("}", src.indexOf("\n", src.indexOf("return key ?", start))) + 1;
  const sessionStorage = storageThrows
    ? { getItem() { throw new Error("blocked"); }, setItem() { throw new Error("blocked"); } }
    : { getItem: (k) => (k in stored ? stored[k] : null), setItem: (k, v) => { stored[k] = v; } };
  const sb = { location: { search }, URLSearchParams, sessionStorage };
  vm.createContext(sb);
  vm.runInContext(src.slice(start, end).replace(/^\s+/gm, "") + "\nthis.playKeyQuery = playKeyQuery;", sb);
  return { playKeyQuery: sb.playKeyQuery, stored };
}

for (const { file, type, storage } of PLAYERS) {
  test(`${file}: reads the show from /api/play/${type}/ with the key`, () => {
    const src = read(file);
    assert.match(src, new RegExp(`"https://api\\.bestefar\\.no/api/play/${type}"`));
    assert.match(src, /\$\{playKeyQuery\((id|expectedId)\)\}/);
  });

  test(`${file}: key from the Start link is used and kept for later screens`, () => {
    const a = loadPlayKeyQuery(file, { search: "?show=s1&key=abc.def" });
    assert.strictEqual(a.playKeyQuery("s1"), "?key=abc.def");
    assert.strictEqual(a.stored[`${storage}:s1`], "abc.def");
    // A later screen has no key in its URL: it gets the saved one, for that show only.
    const b = loadPlayKeyQuery(file, { search: "?show=s1", stored: a.stored });
    assert.strictEqual(b.playKeyQuery("s1"), "?key=abc.def");
    assert.strictEqual(b.playKeyQuery("s2"), "");
  });

  test(`${file}: REGRESSION no key (switch off / Preview) adds nothing; blocked storage never breaks it`, () => {
    assert.strictEqual(loadPlayKeyQuery(file, { search: "?show=s1" }).playKeyQuery("s1"), "");
    assert.strictEqual(loadPlayKeyQuery(file, { search: "?key=k", storageThrows: true }).playKeyQuery("s1"), "?key=k");
  });
}

test("no player still reads from the old open endpoints", () => {
  for (const { file } of PLAYERS) {
    assert.doesNotMatch(read(file), /get-audio-quiz\/|get-image-quiz\/\$|get-image-music-show\/\$/, file);
  }
});

test("AudioQuiz pages load the new script version, so browsers don't use the cached old one", () => {
  for (const f of ["index.html", "data/screens/show.html", "data/screens/screen17.html", "data/screens/screen18.html"]) {
    assert.match(read("bestefar-audio/" + f), /script\.js\?v=20261105"/, f);
  }
});
