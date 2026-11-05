// `node --test tests/*.test.js` - admin UI fixes: icon fonts, one-time messages, page headings, visitors chart.
const test = require("node:test");
const assert = require("node:assert");
const fs = require("fs");
const path = require("path");
const { execFileSync } = require("child_process");

const site = process.env.SITE_ROOT || path.join(__dirname, "..", "httpd.www");
const read = (p) => fs.readFileSync(path.join(site, p), "utf8");

test("feather icons point at codes that exist in the feather font (phone menu button was invisible)", () => {
  const svg = read("assets/admin/assets/icon/feather/fonts/feather.svg");
  const font = new Map([...svg.matchAll(/unicode="&#x([0-9a-f]+);" glyph-name="([^"]+)"/g)].map((m) => [m[2], m[1]]));
  const css = read("assets/admin/assets/icon/feather/css/feather.css");
  for (const name of ["menu", "toggle-right", "toggle-left", "more-horizontal", "maximize", "minus", "trash-2", "search", "x", "user", "log-out"]) {
    const m = css.match(new RegExp(`\\.icon-${name}:before \\{ content: "\\\\([0-9a-f]+)"; \\}`));
    assert.ok(m, `icon-${name} missing from css`);
    assert.strictEqual(m[1], font.get(name), `icon-${name} code is not in the font`);
  }
});

test("icofont icons (font files gone) are drawn with Font Awesome; no broken icofont stylesheets", () => {
  const top = read("application/views/admin/theme/top.php");
  assert.doesNotMatch(top, /icofont\.css|icofont\.min\.css/);
  for (const [icon, code] of [["ui-volume", "f040"], ["list", "f03a"], ["ui-check", "f046"], ["search", "f002"]]) {
    assert.match(top, new RegExp(`\\.icofont-${icon}:before\\{content:"\\\\${code}"\\}`));
  }
  assert.match(top, /\.icofont\{font-family:FontAwesome!important/);
});

test("one-time messages ('Welcome') are cleared on PHP 8 (they showed on every page)", () => {
  // Runs CodeIgniter's own flashdata cleanup on a session holding an 'old' message.
  const src = read("system/libraries/Session/Session.php");
  const body = src.match(/foreach \(\$_SESSION\['__ci_vars'\] as \$key => &\$value\)\s*\{[\s\S]*?\n\t\t\t\}/)[0];
  const php = `$_SESSION=['msg_success'=>'Welcome','__ci_vars'=>['msg_success'=>'old','fresh'=>'new']];$_SESSION['fresh']=1;$current_time=time();${body} echo json_encode($_SESSION);`;
  const out = JSON.parse(execFileSync("php", ["-r", php], { encoding: "utf8" }));
  assert.ok(!("msg_success" in out), "old message must be removed");
  assert.strictEqual(out.__ci_vars.fresh, "old", "a new message survives exactly one more page");
  assert.doesNotMatch(read("application/controllers/Login.php"), /Wellcome/);
});

test("page headings match the page (were copied from other pages)", () => {
  const admin = read("application/controllers/Admin.php");
  assert.match(admin, /'Add New Blog';\s*\$data\['page_sub_title'\] = 'Write And Publish A New Blog';\s*\$data\['page_name'\] = 'blogs_add';/);
  assert.match(admin, /'Manage Admin Emails';\s*\$data\['page_sub_title'\] = 'Update, Delete, And View All Admin Emails';/);
  const emails = read("application/views/admin/admin_email.php");
  assert.match(emails, /<h5>Listing emails<\/h5>/);
  assert.doesNotMatch(emails, /> Users<\/a>/);
  assert.doesNotMatch(read("application/views/admin/visitors.php"), /> Users<\/a>/);
});

test("visitors chart fills a fixed-height box (was squashed on phones) and Chart.js is pinned", () => {
  const v = read("application/views/admin/visitors.php");
  assert.match(v, /height:60vh;min-height:300px/);
  assert.match(v, /maintainAspectRatio: false/);
  assert.match(v, /npm\/chart\.js@4\.4\.1\//);
});
