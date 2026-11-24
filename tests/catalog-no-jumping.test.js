// `node --test tests/*.test.js` - quiz list must not "jump" while the page loads
// (client video 24 Nov 2026: "Can you get rid of the «jumping»").
// Before: sporreleker.css was linked in the middle of the page body (and it also
// changes the page layout), the Agency font had no preload, and the list was
// painted in pieces (page top first, then the list, then taller rows once the
// font arrived). Now: stylesheet + font preload in <head> on the catalog page,
// and the list stays invisible (space kept) until its rows and font are ready.
// PLAYERS_ROOT can point at an older copy of httpd.www to prove the tests catch the old page.
const test = require("node:test");
const assert = require("node:assert");
const fs = require("fs");
const path = require("path");
const { execFileSync } = require("child_process");

const root = process.env.PLAYERS_ROOT || path.join(__dirname, "..", "httpd.www");
const app = path.join(root, "application");
const css = fs.readFileSync(path.join(root, "assets", "frontend", "css", "sporreleker.css"), "utf8");
const fontCss = fs.readFileSync(path.join(root, "assets", "frontend", "agencyfb_font", "stylesheet.css"), "utf8");
const render = (page) => execFileSync("php", [path.join(__dirname, "support", "page_harness.php"), app, page], { encoding: "utf8" });

const catalogPage = render("sporreleker");
const homePage = render("home");
const head = (html) => html.slice(html.indexOf("<head>"), html.indexOf("</head>"));
const script = (html, id) => {
  const m = html.match(new RegExp('<script id="' + id + '">([\\s\\S]*?)</script>'));
  assert.ok(m, `inline script #${id} not found`);
  return m[1];
};

// Runs inline scripts against a tiny fake browser: <html> class list, timers, document.fonts.
function browser({ fonts } = {}) {
  const classes = new Set();
  const timers = [];
  const document = { documentElement: { classList: { add: (c) => classes.add(c), remove: (c) => classes.delete(c) } } };
  if (fonts) document.fonts = fonts;
  return {
    classes, timers,
    run: (code) => new Function("document", "setTimeout", code)(document, (fn, ms) => timers.push({ fn, ms })),
  };
}
const tick = () => new Promise((r) => setImmediate(r));

test("catalog stylesheet loads in <head>, after the theme styles it overrides, and only once", () => {
  const h = head(catalogPage);
  const link = h.match(/<link\b[^>]*href="[^"]*assets\/frontend\/css\/sporreleker\.css"[^>]*>/);
  assert.ok(link, "sporreleker.css must be linked inside <head>");
  assert.match(link[0], /rel="stylesheet"/);
  assert.ok(h.indexOf(link[0]) > h.indexOf("<!--view:frontend/theme/head-->"), "must come after theme/head.php (it overrides #page.full-height)");
  assert.strictEqual((catalogPage.match(/sporreleker\.css/g) || []).length, 1, "linked once, not again in the body");
});

test("Agency font is fetched early: <head> preloads the same file the @font-face uses", () => {
  const face = fontCss.match(/font-family:\s*'agency_fbregular';\s*src:\s*url\('([^']+)'\)\s*format\('woff2'\)/);
  assert.ok(face, "agency_fbregular @font-face with a woff2 source");
  const fontUrl = "https://bestefar.no/assets/frontend/agencyfb_font/" + face[1];
  const preload = head(catalogPage).match(/<link\b[^>]*rel="preload"[^>]*>/);
  assert.ok(preload, "font preload in <head>");
  assert.ok(preload[0].includes('href="' + fontUrl + '"'), `preload must point at ${fontUrl}`);
  assert.match(preload[0], /as="font"/);
  assert.match(preload[0], /type="font\/woff2"/);
  assert.match(preload[0], /\bcrossorigin\b/, "without crossorigin the browser downloads the font twice");
});

test("while loading, the list is invisible but keeps its space, so nothing below it moves", () => {
  const m = css.match(/\.qc-wait \.quiz-catalog\s*\{([^}]*)\}/);
  assert.ok(m, "rule .qc-wait .quiz-catalog");
  assert.match(m[1], /visibility\s*:\s*hidden/);
  assert.doesNotMatch(m[1], /display\s*:\s*none/, "display:none would collapse the space and make the page jump when it appears");
  assert.match(css, /\.quiz-catalog\{[^}]*transition\s*:\s*opacity/, "fades in instead of popping in");
});

test("head script hides the list and always shows it again within 3 seconds", () => {
  const b = browser();
  b.run(script(head(catalogPage), "qc-wait"));
  assert.ok(b.classes.has("qc-wait"), "list hidden while the page loads");
  assert.strictEqual(b.timers.length, 1, "one safety timer");
  assert.ok(b.timers[0].ms <= 3000, `safety timer ${b.timers[0].ms} ms`);
  b.timers[0].fn();
  assert.ok(!b.classes.has("qc-wait"), "safety timer shows the list even if the rest never loads");
});

test("list is shown when all its rows are on the page and the Agency font is ready", async () => {
  const reveal = script(catalogPage, "qc-ready");
  const at = catalogPage.indexOf('<script id="qc-ready">');
  assert.ok(at > catalogPage.lastIndexOf('class="quiz-row"') && at > catalogPage.indexOf("</table>"), "runs only after the whole table");
  const catalogScript = catalogPage.search(/<script src="[^"]*sporreleker\.js"/);
  assert.ok(catalogScript > 0 && at < catalogScript, "does not wait for the slow catalog script");

  let resolveFont;
  let asked;
  const b = browser({ fonts: { load: (spec) => { asked = spec; return new Promise((r) => { resolveFont = r; }); } } });
  b.run(script(head(catalogPage), "qc-wait"));
  b.run(reveal);
  assert.match(asked, /agency_fbregular/);
  await tick();
  assert.ok(b.classes.has("qc-wait"), "still hidden until the font is ready");
  resolveFont([]);
  await tick();
  assert.ok(!b.classes.has("qc-wait"), "shown once the font is ready");
});

test("list is shown even if the font fails or the browser has no font loading API", async () => {
  const reveal = script(catalogPage, "qc-ready");
  const failing = browser({ fonts: { load: () => Promise.reject(new Error("404")) } });
  failing.run(script(head(catalogPage), "qc-wait"));
  failing.run(reveal);
  await tick();
  assert.ok(!failing.classes.has("qc-wait"), "font error: shown anyway");

  const old = browser();
  old.run(script(head(catalogPage), "qc-wait"));
  old.run(reveal);
  assert.ok(!old.classes.has("qc-wait"), "no document.fonts: shown at once");
});

test("sort/filter swaps (AJAX) are visible at once: the hiding class is on <html>, not on the list", () => {
  const partial = render("partial");
  assert.match(partial, /<div class="quiz-catalog">/);
  assert.strictEqual((partial.match(/class="quiz-row"/g) || []).length, 2, "partial still holds the whole list");
  assert.doesNotMatch(partial, /sporreleker\.css/, "stylesheet comes from <head>, not from the partial");
});

test("other pages are unchanged: no catalog stylesheet, font preload or hiding", () => {
  const h = head(homePage);
  assert.doesNotMatch(h, /sporreleker\.css/);
  assert.doesNotMatch(h, /rel="preload"/);
  assert.doesNotMatch(homePage, /qc-wait|qc-ready/);
});

test("regression: catalog page layout override (sticky footer) is still in the catalog stylesheet", () => {
  assert.match(css, /#page\.full-height\{\s*height:auto;\s*min-height:100vh;\s*display:flex;\s*flex-direction:column;\s*\}/);
});

// Second jump found in the slow-load replay: the header grew ~50 px when the logo
// and key pictures arrived, pushing the list down. Their links were inline-block
// (as wide as the picture) while the picture is a % of the link, so both were 0 px
// until the picture loaded. Shared header, all pages.
const themeHead = fs.readFileSync(path.join(app, "views", "frontend", "theme", "head.php"), "utf8");
const nav = fs.readFileSync(path.join(app, "views", "frontend", "theme", "navigation.php"), "utf8");

test("header logo and key links are full-width blocks, so the pictures' space is kept before they load", () => {
  const block = themeHead.match(/<style id="responsive-fixes">([\s\S]*?)<\/style>/);
  assert.ok(block, "shared responsive-fixes styles");
  assert.match(block[1], /#masthead \.elementor-widget-container > a\s*\{\s*display:\s*block;\s*\}/);
});

test("the rule reaches the logo and key in both the desktop and the phone header, and the pictures keep their sizes", () => {
  // Tag contents may hold PHP (`<?php echo base_url(); ?>`), so "?>" is not the end of a tag.
  const inTag = "(?:\\?>|[^>])*";
  for (const [pic, w, h] of [["logo_3000-982x333.png", 982, 333], ["key_3-1.png", 951, 408]]) {
    const src = 'src="[^"]*' + pic.replace(/\./g, "\\.") + '"';
    const linked = nav.match(new RegExp('<div class="\\s*elementor-widget-container\\s*">\\s*(?:<style>[\\s\\S]*?</style>\\s*)?<a\\b' + inTag + '>\\s*<img\\b' + inTag + src, "g")) || [];
    assert.strictEqual(linked.length, 2, `${pic}: a link directly inside .elementor-widget-container, desktop + phone header`);
    const tags = nav.match(new RegExp('<img\\b' + inTag + src + inTag + '>', "g")) || [];
    assert.strictEqual(tags.length, 2);
    for (const tag of tags) {
      assert.match(tag, new RegExp('width="' + w + '"'), `${pic} keeps width=${w}`);
      assert.match(tag, new RegExp('height="' + h + '"'), `${pic} keeps height=${h}`);
    }
  }
});
