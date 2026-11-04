// `node --test tests/*.test.js` - catalog access (Module 3 of ACCESS_CONTROL_PLAN.md).
// Runs Access_model and the catalog view through tests/support/access_harness.php
// with fake session/database: no MySQL, no live API.
const test = require("node:test");
const assert = require("node:assert");
const fs = require("fs");
const os = require("os");
const path = require("path");
const { execFileSync } = require("child_process");

const repo = path.join(__dirname, "..");
const app = process.env.APP_ROOT || path.join(repo, "httpd.www", "application");
const harness = path.join(__dirname, "support", "access_harness.php");
const run = (action, scenario) => JSON.parse(execFileSync("php", [harness, app, action], { input: JSON.stringify(scenario), encoding: "utf8" }));

const quiz = (o) => ({ quiz_type: "AudioQuiz", external_id: "q1", name: "Elvis", question_count: 16, date: "2026-10-01 00:00:00", info: "", music_type: "", tier: "restricted", ...o });
const customer = (o) => ({ users_id: 7, status: "Active", approval_status: 1, client_type: "test", plan: null, payment_registered: 0, access_start: null, access_end: null, allow_audio: 0, allow_image: 0, allow_imagemusic: 0, allow_demo: 0, ...o });
const loggedIn = (user, grants = []) => ({ enabled: true, session: { users_id: user.users_id }, tables: { users: [user], user_show_grants: grants } });
const renderVars = (quizzes) => ({ quizzes, quiz_type: "", sort: "date", dir: "desc" });
const render = (quizzes) => run("render", { vars: renderVars(quizzes) }).html;

// ── switch off: nothing changes ──────────────────────────────────────────────
test("REGRESSION: switch off, rows are returned untouched", () => {
  const rows = [quiz(), quiz({ quiz_type: "TekstQuiz", external_id: "c1" })];
  assert.deepStrictEqual(run("annotate", { enabled: false, quizzes: rows }).quizzes, rows);
});

test("REGRESSION: switch off, the catalog HTML matches main's view (ignoring indentation)", () => {
  const oldView = path.join(fs.mkdtempSync(path.join(os.tmpdir(), "catalog-")), "sporreleker.php");
  fs.writeFileSync(oldView, execFileSync("git", ["show", "main:httpd.www/application/views/frontend/sporreleker.php"], { cwd: repo }));
  const rows = [quiz(), quiz({ quiz_type: "BildeQuiz", external_id: "i1" }), quiz({ quiz_type: "TekstQuiz", external_id: "c1" })];
  const before = run("render", { view: oldView, vars: renderVars(rows) }).html;
  const squash = (html) => html.replace(/\s+/g, " ");
  assert.strictEqual(squash(render(rows)), squash(before));
});

test("REGRESSION: switch off, open_quiz still opens the player for anyone", () => {
  const out = run("open", { enabled: false, type: "audio", id: "q1" });
  assert.strictEqual(out.decision.allowed, true);
  assert.strictEqual(out.decision.player_url, "https://bestefar.no/bestefar-audio/index.html?screen=1&show=q1&autostart=1");
  assert.deepStrictEqual(out.updates, []);
});

// ── switch on: rows ──────────────────────────────────────────────────────────
test("switch on: each controlled row gets a decision and an open_quiz link; crosswords are left alone", () => {
  const user = customer({ allow_audio: 1 });
  const rows = [quiz(), quiz({ quiz_type: "BildeQuiz", external_id: "i1" }), quiz({ quiz_type: "TekstQuiz", external_id: "c1" })];
  const out = run("annotate", { ...loggedIn(user), quizzes: rows }).quizzes;
  assert.deepStrictEqual(out.map((q) => q.access && [q.access.allowed, q.access.reason]), [[true, "type_allowed"], [false, "not_allowed"], undefined]);
  assert.strictEqual(out[0].open_url, "https://bestefar.no/home/open_quiz/audio/q1");
});

test("switch on: tags from the API are used (registered and demo shows)", () => {
  const rows = [quiz({ external_id: "r", tier: "registered" }), quiz({ external_id: "d", tier: "demo" })];
  const out = run("annotate", { ...loggedIn(customer({ approval_status: 0 })), quizzes: rows }).quizzes;
  assert.deepStrictEqual(out.map((q) => q.access.reason), ["open_to_registered", "not_approved"]);
});

test("allowed row: Start goes through open_quiz, PDF stays", () => {
  const html = render([{ ...quiz(), access: { allowed: true, reason: "type_allowed" }, open_url: "https://bestefar.no/home/open_quiz/audio/q1" }]);
  assert.match(html, /class="quiz-start" href="https:\/\/bestefar\.no\/home\/open_quiz\/audio\/q1"/);
  assert.match(html, /class="quiz-pdf" href=/);
  assert.match(html, /data-start-url="https:\/\/bestefar\.no\/home\/open_quiz\/audio\/q1"/);
});

test("locked row: a lock with the message, no Start and no PDF link", () => {
  const html = render([{ ...quiz(), access: { allowed: false, reason: "not_allowed" }, open_url: "x" }]);
  assert.match(html, /class="quiz-lock"/);
  assert.match(html, /title="Du har ikke tilgang til denne quizen\."/);
  assert.match(html, /class="quiz-lock-text">Låst</);
  assert.match(html, /data-locked="1"/);
  assert.doesNotMatch(html, /class="quiz-start"|class="quiz-pdf"/);
});

test("expired row shows the client's renewal text", () => {
  const html = render([{ ...quiz(), access: { allowed: false, reason: "expired" }, open_url: "x" }]);
  assert.match(html, /data-lock-message="Ditt abonnement har utgått\. Vil du fornye det\?"/);
  assert.match(html, /class="quiz-lock-text">Utgått</);
});

test("a refused open shows its message at the top of the catalog", () => {
  const html = run("render", { vars: { ...renderVars([]), access_message: "Ditt abonnement har utgått. Vil du fornye det?" } }).html;
  assert.match(html, /class="quiz-access-message">Ditt abonnement har utgått/);
});

test("refused message: from the ?access= reason, only when the switch is on", () => {
  assert.strictEqual(run("refused", { enabled: true, reason: "expired" }).message, "Ditt abonnement har utgått. Vil du fornye det?");
  assert.strictEqual(run("refused", { enabled: true, reason: "<script>" }).message, "Du har ikke tilgang til denne quizen.");
  assert.strictEqual(run("refused", { enabled: true, reason: null }).message, "");
  assert.strictEqual(run("refused", { enabled: false, reason: "expired" }).message, "");
});

// ── switch on: open_quiz ─────────────────────────────────────────────────────
test("open: not logged in is refused", () => {
  const out = run("open", { enabled: true, type: "audio", id: "q1" });
  assert.deepStrictEqual([out.decision.allowed, out.decision.reason], [false, "login_required"]);
});

test("open: unknown type (e.g. crossword) is refused", () => {
  assert.strictEqual(run("open", { ...loggedIn(customer({ client_type: "full" })), type: "crossword", id: "c1" }).decision.reason, "unknown_type");
});

test("open: the show's tag from the API is used", () => {
  const out = run("open", { ...loggedIn(customer({ approval_status: 0 })), tier: "registered", type: "image", id: "i1" });
  assert.strictEqual(out.decision.allowed, true);
  assert.strictEqual(out.decision.player_url, "https://bestefar.no/bestefar-image/index.html?show=i1");
});

test("open: birthday show's first open saves first_opened_at and expires_at = +3 days, only if not started", () => {
  const user = customer({ client_type: "paid", plan: "per_show", payment_registered: 1 });
  const grant = { grant_id: 5, users_id: 7, quiz_type: "audio", quiz_id: "q1", days_valid: 3, first_opened_at: null, expires_at: null };
  const out = run("open", { ...loggedIn(user, [grant]), type: "audio", id: "q1" });
  assert.strictEqual(out.decision.allowed, true);
  assert.strictEqual(out.updates.length, 1);
  const { table, where, data } = out.updates[0];
  assert.strictEqual(table, "user_show_grants");
  assert.deepStrictEqual(where, [{ grant_id: 5 }, "first_opened_at IS NULL"]);
  const ms = new Date(data.expires_at.replace(" ", "T")) - new Date(data.first_opened_at.replace(" ", "T"));
  assert.strictEqual(ms, 3 * 24 * 3600 * 1000);
});

test("open: a running grant is not restarted", () => {
  const user = customer({ client_type: "paid", plan: "per_show", payment_registered: 1 });
  const grant = { grant_id: 5, users_id: 7, quiz_type: "audio", quiz_id: "q1", days_valid: 3, first_opened_at: "2099-01-01 00:00:00", expires_at: "2099-01-04 00:00:00" };
  assert.deepStrictEqual(run("open", { ...loggedIn(user, [grant]), type: "audio", id: "q1" }).updates, []);
});

test("open: another customer's grant does not count", () => {
  const user = customer({ client_type: "paid", plan: "per_show", payment_registered: 1 });
  const grant = { grant_id: 9, users_id: 99, quiz_type: "audio", quiz_id: "q1", days_valid: 3, first_opened_at: null, expires_at: null };
  const out = run("open", { ...loggedIn(user, [grant]), type: "audio", id: "q1" });
  assert.deepStrictEqual([out.decision.allowed, out.updates.length], [false, 0]);
});

// ── wiring ───────────────────────────────────────────────────────────────────
test("controller: catalog asks for login when the switch is on, and annotates rows", () => {
  const home = fs.readFileSync(path.join(app, "controllers", "Home.php"), "utf8");
  const fn = home.slice(home.indexOf("public function sporreleker("), home.indexOf("public function open_quiz("));
  assert.match(fn, /enabled\(\) && !\$this->Access_model->is_logged_in\(\)[\s\S]*redirect\('login'/);
  assert.match(fn, /annotate_catalog\(\$quizzes\)/);
  assert.match(home, /public function open_quiz\(\$type = '', \$quiz_id = ''\)/);
  assert.match(home, /redirect\('home\/sporreleker\?access=' \. rawurlencode\(\$decision\['reason'\]\)\)/);
});
