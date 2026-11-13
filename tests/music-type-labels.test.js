// `node --test tests/*.test.js` - Norwegian Music Type labels shown in the catalog popup.
// Client correction (8 Nov 2026): "Saying" is "Stemme", not "Talemåte".
const test = require("node:test");
const assert = require("node:assert");
const fs = require("fs");
const path = require("path");
const { execFileSync } = require("child_process");

const app = process.env.APP_ROOT || path.join(__dirname, "..", "httpd.www", "application");
const model = path.join(app, "models", "Admin_model.php");

// Reads the $firm_naming_labels array straight out of the model and evaluates it in PHP.
const labels = () => {
  const src = fs.readFileSync(model, "utf8");
  const m = src.match(/\$firm_naming_labels = (array\([\s\S]*?\));/);
  assert.ok(m, "label table not found in Admin_model.php");
  return JSON.parse(execFileSync("php", ["-r", `echo json_encode(${m[1]});`], { encoding: "utf8" }));
};

test("Saying is shown as Stemme", () => {
  assert.strictEqual(labels().Saying, "Stemme");
});

test("REGRESSION Music and Sound labels unchanged", () => {
  const l = labels();
  assert.strictEqual(l.Music, "Musikk");
  assert.strictEqual(l.Sound, "Lyd");
  assert.deepStrictEqual(Object.keys(l).sort(), ["Music", "Saying", "Sound"]);
});

test("old label Talemåte is gone from the model", () => {
  assert.doesNotMatch(fs.readFileSync(model, "utf8"), /Talemåte/);
});
