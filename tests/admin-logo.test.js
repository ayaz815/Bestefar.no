// `node --test tests/*.test.js` - admin tab icon is the Bestefar icon. The header logo and
// profile picture stay as uploaded in admin System Setting / My Profile (uploads/admin/).
const test = require("node:test");
const assert = require("node:assert");
const fs = require("fs");
const path = require("path");

const site = process.env.SITE_ROOT || path.join(__dirname, "..", "httpd.www");
const theme = path.join(site, "application", "views", "admin", "theme");
const read = (file) => fs.readFileSync(path.join(theme, file), "utf8");

test("admin tab icon is the Bestefar icon and the file exists", () => {
  assert.match(read("top.php"), /<link rel="icon" href="<\?php echo base_url\(\); \?>assets\/frontend\/images\/fevicon\.png"/);
  assert.ok(fs.existsSync(path.join(site, "assets/frontend/images/fevicon.png")));
});

test("REGRESSION: header logo and profile picture still come from the Settings uploads", () => {
  const html = read("header.php");
  assert.match(html, /uploads\/admin\/system_image\.jpg/);
  assert.match(html, /uploads\/admin\/admin_<\?php echo \$this->session->userdata\('admin_id'\); \?>\.jpg/);
});

test("uploaded admin logo is capped to the top bar height and width on every screen size", () => {
  const css = read("top.php");
  assert.match(css, /\.navbar-logo > a img\.img-fluid\{max-height:36px;max-width:100%;width:auto;height:auto;object-fit:contain\}/);
  assert.match(css, /@media \(max-width:991px\)\{[^}]*max-width:calc\(100% - 120px\)\}/, "leaves room for the phone menu buttons");
  assert.ok(css.indexOf("max-height:36px") > css.indexOf("css/style.css"), "must come after the theme's style.css to win");
});
