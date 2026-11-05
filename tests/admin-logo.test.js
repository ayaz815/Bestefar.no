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
