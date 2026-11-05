// `node --test tests/*.test.js` - admin header shows the Bestefar logo, tab icon and profile icon.
const test = require("node:test");
const assert = require("node:assert");
const fs = require("fs");
const path = require("path");

const site = process.env.SITE_ROOT || path.join(__dirname, "..", "httpd.www");
const theme = path.join(site, "application", "views", "admin", "theme");
const imagePaths = (file) => [...fs.readFileSync(path.join(theme, file), "utf8").matchAll(/<\?php echo base_url\(\); \?>\/?([^"']+\.(?:png|jpg))/g)].map((m) => m[1]);

test("admin tab icon is the Bestefar icon and the file exists", () => {
  const icons = imagePaths("top.php");
  assert.ok(icons.includes("assets/frontend/images/fevicon.png"));
  for (const p of icons) assert.ok(fs.existsSync(path.join(site, p)), `${p} is missing`);
});

test("admin header logo and profile icon point at files that exist (no Theme-Logo / User-Profile-Image)", () => {
  const html = fs.readFileSync(path.join(theme, "header.php"), "utf8");
  assert.doesNotMatch(html, /alt="(Theme-Logo|User-Profile-Image)"/);
  for (const p of ["assets/frontend/images/logo/Logo-1-300x82.jpg", "assets/frontend/images/fevicon.png"]) {
    assert.ok(imagePaths("header.php").includes(p), `${p} not used`);
    assert.ok(fs.existsSync(path.join(site, p)), `${p} is missing`);
  }
});
