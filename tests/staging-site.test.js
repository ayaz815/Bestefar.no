// `node --test tests/*.test.js` - online test site staging.bestefar.no (Module 6 of ACCESS_CONTROL_PLAN.md).
const test = require("node:test");
const assert = require("node:assert");
const fs = require("fs");
const path = require("path");
const { execFileSync } = require("child_process");

const repo = path.join(__dirname, "..");
const site = process.env.SITE_ROOT || path.join(repo, "httpd.www");
const app = path.join(site, "application");
const harness = path.join(__dirname, "support", "access_harness.php");
const run = (action, scenario) => JSON.parse(execFileSync("php", [harness, app, action], { input: JSON.stringify(scenario), encoding: "utf8" }));
const STAGING = "https://staging.bestefar.no/";

// Runs the ENVIRONMENT lines of index.php for a given host and returns the result.
const environmentFor = (host, ciEnv) => {
  const src = fs.readFileSync(path.join(site, "index.php"), "utf8");
  const line = src.split(/\r?\n/).filter((l) => /\$host = |define\('ENVIRONMENT'/.test(l)).join("\n");
  const server = `$_SERVER['HTTP_HOST'] = ${JSON.stringify(host)};` + (ciEnv ? `$_SERVER['CI_ENV'] = '${ciEnv}';` : "");
  return execFileSync("php", ["-r", `${server}\n${line}\necho ENVIRONMENT;`], { encoding: "utf8" });
};

test("index.php: staging.* runs as testing", () => {
  assert.strictEqual(environmentFor("staging.bestefar.no"), "testing");
});

test("REGRESSION: index.php: the live site and localhost run as before (development)", () => {
  assert.strictEqual(environmentFor("bestefar.no"), "development");
  assert.strictEqual(environmentFor("www.bestefar.no"), "development");
  assert.strictEqual(environmentFor("localhost:8888"), "development");
  assert.strictEqual(environmentFor("staging.bestefar.no", "production"), "production"); // CI_ENV still wins
});

test("config/testing: own address, switch on, database file kept out of git", () => {
  const cfg = fs.readFileSync(path.join(app, "config", "testing", "config.php"), "utf8");
  assert.match(cfg, /\$config\['base_url'\] = 'https:\/\/staging\.bestefar\.no\/';/);
  assert.match(fs.readFileSync(path.join(app, "config", "testing", "access.php"), "utf8"), /access_control_enabled'\] = TRUE;/);
  assert.match(fs.readFileSync(path.join(app, "config", "access.php"), "utf8"), /access_control_enabled'\] = FALSE;/, "live switch stays off");
  assert.match(fs.readFileSync(path.join(repo, ".gitignore"), "utf8"), /^httpd\.www\/application\/config\/testing\/database\.php$/m);
  const example = fs.readFileSync(path.join(app, "config", "testing", "database.example.php"), "utf8");
  assert.match(example, /'database' => 'TEST_DATABASE_NAME'/);
  assert.doesNotMatch(example, /'bestefar_no'|bestefar_m3test/);
});

test("config/testing: the test site stops instead of using the live database when its database file is missing", () => {
  const cfg = path.join(app, "config", "testing", "config.php");
  const out = execFileSync("php", ["-r", `define('BASEPATH', 1); include '${cfg}'; echo 'still running';`], { encoding: "utf8" });
  const hasDb = fs.existsSync(path.join(app, "config", "testing", "database.php"));
  assert.strictEqual(out, hasDb ? "still running" : "Test site: application/config/testing/database.php is missing.");
});

test("test site: open_quiz sends the customer to the test site's own players", () => {
  const out = run("open", { enabled: false, base_url: STAGING, type: "audio", id: "q1" });
  assert.strictEqual(out.decision.target_url, STAGING + "bestefar-audio/index.html?screen=1&show=q1&autostart=1");
  const image = run("open", { enabled: false, base_url: STAGING, type: "imagemusic", id: "m1" });
  assert.strictEqual(image.decision.target_url, STAGING + "bestefar-imagemusic/index.html?show=m1");
});

test("test site: catalog Start links (switch off) point at the test site's players", () => {
  const quizzes = [{ quiz_type: "BildeQuiz", external_id: "i1", name: "X", question_count: 3, date: "2026-10-01 00:00:00", info: "", music_type: "", tier: "restricted" }];
  const html = run("render", { base_url: STAGING, vars: { quizzes, quiz_type: "", sort: "date", dir: "desc" } }).html;
  assert.match(html, /href="https:\/\/staging\.bestefar\.no\/bestefar-image\/index\.html\?show=i1"/);
  assert.doesNotMatch(html, /https:\/\/bestefar\.no\/bestefar-/);
});

test("staging/.htaccess: missing uploads come from the live site, the rest as the live .htaccess", () => {
  const ht = fs.readFileSync(path.join(repo, "staging", ".htaccess"), "utf8");
  const uploads = ht.indexOf("RewriteRule ^uploads/(.*)$ https://bestefar.no/uploads/$1 [R=302,L]");
  const front = ht.indexOf("RewriteRule ^(.*)$ index.php/$1 [L]");
  assert.ok(uploads > 0 && front > uploads, "uploads rule must come before the index.php rule");
});
