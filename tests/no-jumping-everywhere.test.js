// `node --test tests/*.test.js` - no "jumping" on the other pages either (client,
// 24 Nov 2026: iPhone screenshot of the AudioQuiz player + "check jumping on all
// projects"). Measured with slow, uncached files at phone + desktop width:
//  - AudioQuiz player: the frame followed the visible screen height (100dvh), so on
//    iPhone every screen was laid out again whenever the browser bars slid in/out
//    (everything moved 40-90 px, icons changed size at the 700 px breakpoint).
//  - Article page: the cover picture had no size, so the text dropped ~280 px on a
//    phone when it arrived.
// PLAYERS_ROOT can point at an older copy of httpd.www to prove the tests catch the old files.
const test = require("node:test");
const assert = require("node:assert");
const fs = require("fs");
const os = require("os");
const path = require("path");
const { execFileSync } = require("child_process");

const root = process.env.PLAYERS_ROOT || path.join(__dirname, "..", "httpd.www");
const read = (p) => fs.readFileSync(path.join(root, p), "utf8");
const cssBlock = (html, selector) => {
  const m = html.match(new RegExp(selector.replace(/[.*+?^${}()|[\]\\#]/g, "\\$&") + "\\s*\\{([^}]*)\\}"));
  assert.ok(m, `rule ${selector}`);
  return m[1];
};
// The height declarations of a rule, in order (the last one the browser understands wins).
const heights = (block) => [...block.matchAll(/(?:^|[;\s])height\s*:\s*([^;!]+?)\s*(!important)?\s*;/g)].map((m) => m[1]);

// ── AudioQuiz player ────────────────────────────────────────────────────────
test("AudioQuiz frame keeps one height while the browser bars slide in and out (svh, vh fallback first)", () => {
  const h = heights(cssBlock(read("bestefar-audio/player.html"), "#aqFrame"));
  assert.deepStrictEqual(h, ["100vh", "100svh"]);
});

test("AudioQuiz question screen uses the same stable height and no dvh anywhere in the player", () => {
  const show = read("bestefar-audio/data/screens/show.html");
  assert.deepStrictEqual(heights(cssBlock(show, ".app-vh")), ["100vh", "100svh"]);
  for (const f of ["bestefar-audio/player.html", "bestefar-audio/index.html", "bestefar-audio/data/screens/show.html",
    "bestefar-audio/data/screens/screen17.html", "bestefar-audio/data/screens/screen18.html"]) {
    const css = (read(f).match(/<style[\s\S]*?<\/style>/g) || []).join("\n");
    assert.doesNotMatch(css.replace(/\/\*[\s\S]*?\*\//g, ""), /\d+dvh/, `${f} must not size anything with dvh`);
  }
});

test("regression: the frame still fills the width and the player shell still never scrolls", () => {
  const html = read("bestefar-audio/player.html");
  assert.match(cssBlock(html, "#aqFrame"), /width:\s*100%/);
  const page = cssBlock(html, "body");
  assert.match(page, /overflow:\s*hidden/);
  assert.match(page, /background:\s*#ffc000/i, "theme yellow behind the frame");
});

// ── AudioQuiz question screen: same section sizes on every question ──────────
// Client 25 Nov 2026: "the black containers ... sizes must be consistent and same in
// all pages". The two black boxes share what is left of the screen; the answer (up to
// 75 characters, 1-3 lines) and the artist line (empty until the data arrives) changed
// that. Measured 390x664 (live shows): before, black boxes 200 -> 176 px when the data
// arrived and 176 vs 164 px between short and long answers; after, 176 on every
// question from the first frame (also 320x568, 375x553, 428x746, 820x1100).
const showHtml = () => read("bestefar-audio/data/screens/show.html");
const fitScript = () => {
  const m = showHtml().match(/<script id="aq-fit-text">([\s\S]*?)<\/script>/);
  assert.ok(m, "#aq-fit-text script");
  return m[1];
};

test("answer and artist boxes have a fixed size, so the black boxes are the same on every question", () => {
  const html = showHtml();
  const answer = cssBlock(html, "#answer-text");
  assert.match(answer, /height:\s*3\.5rem/, "2 lines in the height the old 1-line answer took (76 px section)");
  assert.match(answer, /overflow:\s*hidden/);
  assert.match(answer, /box-sizing:\s*border-box/);
  const artist = cssBlock(html, "#artist-name-display");
  assert.match(artist, /height:\s*3rem/, "'Utøver:' + name, kept even when empty");
  assert.match(artist, /white-space:\s*nowrap/);
  assert.match(artist, /flex-shrink:\s*0/, "not squeezed on short screens");
  assert.match(html, /<img src="\.\.\/assets\/vite\.jpg" alt="" width="555" height="128" class="w-40" \/>/, "'Nyttig å vite' keeps its space before the picture loads");
  // the script must come after both boxes (it looks them up straight away)
  const at = html.indexOf('<script id="aq-fit-text">');
  assert.ok(at > html.indexOf('id="answer-text"') && at > html.indexOf('id="artist-name-display"'));
});

test("regression: the black boxes still share the rest of the screen equally (and keep their minimum)", () => {
  const html = showHtml();
  assert.match(html, /class="bg-black flex flex-col items-center justify-between music-section flex-1 min-h-\[11rem\]"/);
  assert.match(html, /mic-section toggle-question-area cursor-pointer flex flex-1 min-h-\[11rem\]"/);
  assert.match(html, /class="p-4 px-4 text-center answer-text opacity-0 pointer-events-none/, "answer still hidden until tapped");
});

// The fit script against a tiny fake page: text length -> lines -> height, like a browser.
function fitPage() {
  let observer, frames = [], resize;
  const box = (id, { width, height, wrap }) => ({
    id, text: "", style: { fontSize: "" }, clientWidth: width, clientHeight: height,
    get font() { return this.style.fontSize ? parseFloat(this.style.fontSize) : 16; },
    get scrollHeight() { if (!wrap) return this.clientHeight; const perLine = Math.floor(this.clientWidth / (0.55 * this.font)); return Math.ceil(this.text.length / perLine) * 1.5 * this.font; },
    get scrollWidth() { return wrap ? this.clientWidth : Math.ceil(this.text.length * 0.55 * this.font); },
  });
  const els = { "answer-text": box("answer-text", { width: 340, height: 48, wrap: true }), "artist-name-display": box("artist-name-display", { width: 358, height: 48, wrap: false }) };
  const env = {
    document: { getElementById: (id) => els[id], fonts: { ready: { then: () => {} } } },
    window: { addEventListener: (ev, fn) => { if (ev === "resize") resize = fn; } },
    getComputedStyle: (el) => ({ fontSize: el.font + "px" }),
    MutationObserver: class { constructor(fn) { observer = { fn, watched: [] }; } observe(el, opts) { observer.watched.push([el.id, opts]); } },
    requestAnimationFrame: (fn) => frames.push(fn),
  };
  new Function(...Object.keys(env), fitScript())(...Object.values(env));
  const flush = () => { const f = frames; frames = []; f.forEach((fn) => fn()); };
  flush();
  return {
    els, observer, resize, flush,
    fill: (id, text) => { els[id].text = text; observer.fn(); flush(); return els[id].style.fontSize; },
    frames: () => frames.length,
  };
}
const fits = (el) => el.scrollHeight <= el.clientHeight + 1 && el.scrollWidth <= el.clientWidth + 1;

test("short answer and artist keep the normal text size", () => {
  const p = fitPage();
  assert.strictEqual(p.fill("answer-text", "Priscilla Presley"), "");
  assert.strictEqual(p.fill("artist-name-display", "Utøver:Elvis Presley"), "");
});

test("long answer gets the largest smaller text that fits; the box never grows", () => {
  const p = fitPage();
  const size = parseFloat(p.fill("answer-text", "Heinrich Hoffmann von Fallersleben ca. 1835. Til Norsk ved Johan Nicolaisen, og mer tekst her"));
  assert.ok(size < 16 && size >= 11, `got ${size}px`);
  assert.ok(fits(p.els["answer-text"]), "fits");
  p.els["answer-text"].style.fontSize = size + 1 + "px";
  assert.ok(!fits(p.els["answer-text"]), "one size bigger would not fit");
});

test("very long text stops at 11px (still readable)", () => {
  const p = fitPage();
  assert.strictEqual(p.fill("answer-text", "x".repeat(2000)), "11px");
});

test("next text that is short goes back to the normal size", () => {
  const p = fitPage();
  assert.notStrictEqual(p.fill("answer-text", "y".repeat(150)), "");
  assert.strictEqual(p.fill("answer-text", "Tjeld"), "");
});

test("long artist name shrinks to fit the width (one line)", () => {
  const p = fitPage();
  const size = parseFloat(p.fill("artist-name-display", "Det Norske Solistkor og Kringkastingsorkesteret og venner"));
  assert.ok(size < 16 && size >= 11);
  assert.ok(fits(p.els["artist-name-display"]));
});

test("fit runs when script.js fills the boxes (any change, nested too), once per frame, and again on rotation", () => {
  const p = fitPage();
  assert.deepStrictEqual(p.observer.watched.map(([id]) => id), ["answer-text", "artist-name-display"]);
  for (const [, opts] of p.observer.watched) assert.deepStrictEqual(opts, { childList: true, characterData: true, subtree: true });
  p.observer.fn(); p.observer.fn(); p.observer.fn();
  assert.strictEqual(p.frames(), 1, "three changes in one frame -> one fit");
  p.flush();
  assert.strictEqual(typeof p.resize, "function", "refits on resize / rotation");
  assert.strictEqual(p.fill("answer-text", "z".repeat(60)), "", "fits a 340 px box");
  p.els["answer-text"].clientWidth = 200; // narrower after rotation
  p.resize(); p.flush();
  assert.ok(parseFloat(p.els["answer-text"].style.fontSize) < 16 && fits(p.els["answer-text"]));
});

// ── Article cover picture ───────────────────────────────────────────────────
const helper = path.join(root, "application", "helpers", "image_size_helper.php");
const attrs = (files) => JSON.parse(execFileSync("php", ["-r", `
  define('BASEPATH', 'test');
  require $argv[1];
  $out = array();
  foreach (json_decode($argv[2], true) as $f) { $out[] = image_size_attrs($f); }
  echo json_encode($out);`, helper, JSON.stringify(files)], { encoding: "utf8" }));

test("cover picture size is read from the file (PNG, JPG, name with a space)", () => {
  const dir = fs.mkdtempSync(path.join(os.tmpdir(), "covers-"));
  const spaced = path.join(dir, "1786845054-BILDE ALLE.png");
  fs.copyFileSync(path.join(root, "assets/frontend/images/logo/logo_3000-982x333.png"), spaced);
  assert.deepStrictEqual(attrs([
    path.join(root, "assets/frontend/images/quiz-pdf-icon.png"),
    path.join(root, "assets/frontend/images/blue.jpg"),
    spaced,
  ]), [' width="376" height="488"', ' width="1024" height="1024"', ' width="982" height="333"']);
});

test("no size (the <img> stays exactly as before) when there is no usable picture", () => {
  const dir = fs.mkdtempSync(path.join(os.tmpdir(), "covers-"));
  const fake = path.join(dir, "not-a-picture.jpg");
  fs.writeFileSync(fake, "hello");
  const empty = path.join(dir, "empty.png");
  fs.writeFileSync(empty, "");
  assert.deepStrictEqual(attrs([
    path.join(dir, "missing.jpg"), // file not uploaded
    dir + path.sep,                // article without a cover: path is just the folder
    fake,                          // not a picture
    empty,                         // 0 bytes
    "",                            // nothing at all
  ]), ["", "", "", "", ""]);
});

test("article page prints the size on the cover picture in both layouts (with and without a link)", () => {
  const view = read("application/views/frontend/blog_details.php");
  assert.match(view, /\$this->load->helper\('image_size'\)/);
  assert.match(view, /\$cover_size\s*=\s*image_size_attrs\(FCPATH\.'uploads\/admin\/blogs\/'\.\$blog->c_image\)/);
  const covers = view.match(/<img src="<\?php echo base_url\(\); \?>uploads\/admin\/blogs\/<\?php echo \$blog->c_image; \?>"[^\n]*>/g) || [];
  assert.strictEqual(covers.length, 2, "linked + plain cover");
  for (const tag of covers) {
    assert.ok(tag.includes('"<?php echo $cover_size; ?> class="img-responsive"'), "size attributes right after src");
    assert.match(tag, /style="width:100%;display:block;"/, "regression: still full width");
  }
});

test("the cover keeps its shape before it loads: pictures get height:auto from the shared styles", () => {
  const head = read("application/views/frontend/theme/head.php");
  assert.match(head, /img\s*\{\s*max-width:\s*100%;\s*height:\s*auto;\s*\}/);
});

// ── Quiz list moving sideways (Windows scrollbar) ─────────────────────────────
// Client, 25 Nov 2026: "the catalog jump sidewise. See left edge". The page scrolls
// inside <body> (html, body: height 100% + overflow-x hidden). The quiz list is the
// one page that is only as tall as its rows, so while it arrived it was shorter than
// the screen, had no scrollbar, and moved ~8 px sideways when the scrollbar came.
// Measured (1366x657, page sent in two parts): before 1366 -> 1349 px wide, list
// edge 113 -> 105; after 1349 / 105 from the first paint.
test("every page always has its scrollbar, on body (the element that scrolls), so nothing moves sideways", () => {
  const head = read("application/views/frontend/theme/head.php");
  const fixes = head.match(/<style id="responsive-fixes">([\s\S]*?)<\/style>/);
  assert.ok(fixes, "responsive-fixes block");
  assert.match(fixes[1], /(^|\n)body\s*\{\s*overflow-y:\s*scroll;\s*\}/);
  // why it is body and not html: these two shared rules make body the scroller
  assert.match(head, /html, body \{\s*height: 100%;/);
  assert.match(head, /html, body \{\s*overflow-x: hidden;/);
  // a second always-on scrollbar on html would show two bars
  assert.doesNotMatch(head.replace(/\/\*[\s\S]*?\*\//g, ""), /(^|[\s,}])html\s*\{[^}]*overflow-y\s*:\s*scroll/);
  // regression: the quiz list still sizes the page to its rows (the sticky footer)
  assert.match(read("assets/frontend/css/sporreleker.css"), /#page\.full-height\{ height:auto; min-height:100vh;/);
});

// ── New uploads reach the browser ─────────────────────────────────────────────
// one.com sends no cache headers, so browsers kept old copies for a day or more after
// an upload (client 25 Nov 2026: generator "Tilgang - cannot see it", player "still
// jumps"). Checked on a local Apache with the site's own root .htaccess: these folders
// answer "no-cache, must-revalidate" (cheap 304 when unchanged), other files untouched.
const noCache = (file, filesRe) => {
  const txt = read(file);
  const block = txt.match(/<IfModule mod_headers\.c>([\s\S]*?)<\/IfModule>/);
  assert.ok(block, `${file}: rule inside <IfModule mod_headers.c> (no 500 error if the module is missing)`);
  const files = block[1].match(/<FilesMatch "([^"]+)">([\s\S]*?)<\/FilesMatch>/);
  assert.ok(files, `${file}: FilesMatch`);
  assert.strictEqual(files[1], filesRe);
  assert.match(files[2], /Header set Cache-Control "no-cache, must-revalidate"/);
  assert.doesNotMatch(txt, /Rewrite|Deny|Require|Options/, `${file}: nothing else (the root .htaccess does the routing)`);
};
test("generator page is always checked with the server (its JS/CSS names change with every build)", () => {
  noCache("html-generator-app/.htaccess", "\\.html$");
});
test("AudioQuiz player pages and script are always checked with the server", () => {
  noCache("bestefar-audio/.htaccess", "\\.(html|js)$");
});

// ── Admin login message ─────────────────────────────────────────────────────
test("wrong admin login: the message is the red error pop-up, not the green success one", () => {
  const login = read("application/controllers/Login.php");
  const fail = login.match(/function admin_login_varify\(\)[\s\S]*?\}else\{([\s\S]*?)\}/);
  assert.ok(fail, "failure branch of admin_login_varify");
  assert.match(fail[1], /set_flashdata\('msg_error', ' Invalid Username and Password\.'\)/);
  assert.match(fail[1], /redirect\('admin', 'refresh'\)/, "regression: still back to the login page");
  const script = read("application/views/admin/theme/script.php");
  assert.match(script, /flashdata\('msg_error'\)[\s\S]*?notify\([^)]*'danger'/, "the admin theme shows msg_error as a danger pop-up");
  assert.match(read("application/views/admin/login.php"), /load->view\('admin\/theme\/script'\)/, "the login page shows the pop-ups");
});
