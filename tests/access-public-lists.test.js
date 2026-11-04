// `node --test tests/*.test.js` - the catalog reads both show-list shapes (Module 4b of ACCESS_CONTROL_PLAN.md):
// full lists (today) and the trimmed lists the backend sends once ACCESS_ENFORCE is on.
const test = require("node:test");
const assert = require("node:assert");
const fs = require("fs");
const path = require("path");
const { execFileSync } = require("child_process");

const app = process.env.APP_ROOT || path.join(__dirname, "..", "httpd.www", "application");
const model = path.join(app, "models", "Admin_model.php");

// Loads Admin_model with a stub CI_Model and calls one of its static helpers.
const call = (fn, ...args) => JSON.parse(execFileSync("php", ["-r", `
  define('BASEPATH', 'test'); class CI_Model {} require ${JSON.stringify(model)};
  echo json_encode(Admin_model::${fn}(...json_decode(file_get_contents('php://stdin'), true)));`], { input: JSON.stringify(args), encoding: "utf8" }));

const screens = [{ firmNaming: "Music " }, { firmNaming: "Music" }, {}];

test("question count: trimmed list (numberOfScreens)", () => {
  assert.strictEqual(call("catalog_question_count", { numberOfScreens: 16 }, "quizForms"), 16);
});

test("REGRESSION question count: full lists as today (AudioQuiz quizForms, ImageMusic screens)", () => {
  assert.strictEqual(call("catalog_question_count", { quizForms: screens }, "quizForms"), 3);
  assert.strictEqual(call("catalog_question_count", { screens }, "screens"), 3);
  assert.strictEqual(call("catalog_question_count", {}, "quizForms"), 0);
});

test("Music Type: trimmed list (musicType) and full list (first screen) give the same", () => {
  assert.strictEqual(call("catalog_music_type", { musicType: "Music" }), "Music");
  assert.strictEqual(call("catalog_music_type", { quizForms: screens }), "Music");
  assert.strictEqual(call("catalog_music_type", {}), "");
});

test("all three list readers use the shared count helper", () => {
  const src = fs.readFileSync(model, "utf8");
  assert.strictEqual((src.match(/self::catalog_question_count\(\$show, '(quizForms|screens)'\)/g) || []).length, 3);
  assert.match(src, /\$raw_music_type = self::catalog_music_type\(\$show\);/);
});
