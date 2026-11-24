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
