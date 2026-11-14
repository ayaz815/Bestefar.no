// `node --test tests/*.test.js` - client's revised registration form and new logo (7 Nov 2026).
// APP_ROOT can point at an older copy of httpd.www/application to prove the tests catch the old behaviour.
const test = require("node:test");
const assert = require("node:assert");
const fs = require("fs");
const path = require("path");

const root = path.join(__dirname, "..", "httpd.www");
const app = process.env.APP_ROOT || path.join(root, "application");
const read = (p) => fs.readFileSync(path.join(app, p), "utf8");
const form = read("views/frontend/register.php");

const inputTag = (name) => form.match(new RegExp(`<input[^>]*name="${name}"[^>]*>`))?.[0] || "";

test("heading says all fields must be filled in", () => {
  assert.match(form, /Registrering<\/b><\/h3>\s*<span>Alle feltene må fylles ut<\/span>/);
});

test("placeholders match the client's layout", () => {
  const expected = {
    institute: "Hvis du er privatperson, etternavn",
    address: "Må fylles inn",
    postal1: "Nr",
    postal2: "Sted",
    web: "Velg Enten Eller. Bruk URL-Adresse.",
    fname: "Fornavn",
    lname: "Etternavn",
    phone: "Institusjon/Forening",
    mobile: "Privat",
    email: "Må fylles inn",
    password: "Bruk minst 8 karakterer",
  };
  for (const [name, text] of Object.entries(expected)) {
    assert.ok(inputTag(name).includes(`placeholder="${text}"`), `${name}: ${inputTag(name)}`);
  }
});

test("placeholders keep the client's capitals (typed text is still capitalized)", () => {
  assert.match(form, /\.form_c\s*\{\s*text-transform: capitalize;/);
  assert.match(form, /\.form_c::placeholder\s*\{\s*text-transform: none;/);
});

test("projector and amplifier questions are both required Ja/Nei radios", () => {
  assert.match(form, /Vi har forsterker og høyttalere:/);
  for (const name of ["projector", "amplifier_speakers"]) {
    const radios = form.match(new RegExp(`<input[^>]*name="${name}"[^>]*>`, "g")) || [];
    assert.strictEqual(radios.length, 2, name);
    assert.ok(radios[0].includes('value="Yes"') && radios[1].includes('value="No"'), name);
    assert.ok(radios.some((r) => /\brequired\b/.test(r)), `${name} is required`);
  }
});

test("REGRESSION: every other field stays required and keeps its field name", () => {
  for (const name of ["institute", "address", "postal1", "postal2", "web", "fname", "lname", "phone", "mobile", "email", "password"]) {
    assert.match(inputTag(name), /\brequired\b/, name);
  }
  assert.match(inputTag("password"), /minlength="8"/);
  assert.match(form, /action="<\?php echo base_url\('login\/register\/create'\); \?>"/);
});

test("the amplifier answer is saved and shown to the admin", () => {
  assert.match(read("models/Admin_model.php"), /\$save_data\['amplifier_speakers'\] = \$this->input->post\('amplifier_speakers'\);/);
  assert.match(read("views/admin/modals/user_details.php"), /\$user->amplifier_speakers/);
});

test("migration only adds the column, nullable so existing customers are untouched", () => {
  const sql = fs.readFileSync(path.join(__dirname, "..", "database", "migrations", "2026-11-07_registration_amplifier_up.sql"), "utf8");
  assert.match(sql, /ADD COLUMN `amplifier_speakers` varchar\(3\) DEFAULT NULL/);
  assert.doesNotMatch(sql.replace(/^--.*$/gm, ""), /\b(DROP|DELETE|UPDATE)\b/i);
});

test("the header on every device uses the client's new logo, and the files exist", () => {
  for (const view of ["views/frontend/home.php", "views/frontend/theme/navigation.php"]) {
    const html = read(view);
    assert.doesNotMatch(html, /Logo-1/, view);
    assert.strictEqual((html.match(/width="982" height="333" (style="[^"]*" )?src="[^"]*logo_3000-982x333\.png"/g) || []).length, 2, view);
  }
  for (const file of ["logo_3000.png", "logo_3000-300x101.png", "logo_3000-768x260.png", "logo_3000-982x333.png"]) {
    assert.ok(fs.existsSync(path.join(root, "assets", "frontend", "images", "logo", file)), file);
  }
});

test("the confirmation email uses the new logo without stretching it", () => {
  assert.match(read("models/Admin_model.php"), /logo_3000-300x101\.png" width="140px" height="47px"/);
});

test("pointer (hand) cursor over every registration field, label and the send button", () => {
  const view = read("views/frontend/register.php");
  const m = view.match(/((?:#register_form [a-z]+,?\s*)+)\{\s*cursor:\s*pointer;/);
  assert.ok(m, "no pointer-cursor rule scoped to #register_form");
  for (const el of ["input", "textarea", "select", "label"]) {
    assert.match(m[1], new RegExp("#register_form " + el + "\\b"), el);
  }
  assert.match(view, /<form [^>]*id="register_form"/);
});

test("regression: pointer rule stays scoped to the registration form (header/menu untouched)", () => {
  const view = read("views/frontend/register.php");
  const css = view.slice(0, view.indexOf("</style>")).replace(/\/\*[\s\S]*?\*\//g, "");
  for (const [, selectors] of css.matchAll(/([^{}]+)\{[^}]*cursor:\s*pointer/g)) {
    for (const sel of selectors.split(",").map((x) => x.trim())) {
      assert.ok(sel === ".register_btn" || sel.startsWith("#register_form "), `unscoped pointer selector: ${sel}`);
    }
  }
  assert.match(view, /\.register_btn\{[^}]*cursor: pointer/);
});
