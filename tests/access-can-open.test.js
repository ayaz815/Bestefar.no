// `node --test tests/*.test.js` - the can-open rule (Module 2 of ACCESS_CONTROL_PLAN.md).
// APP_ROOT can point at another copy of httpd.www/application.
const test = require("node:test");
const assert = require("node:assert");
const fs = require("fs");
const path = require("path");
const { execFileSync } = require("child_process");

const app = process.env.APP_ROOT || path.join(__dirname, "..", "httpd.www", "application");
const helper = path.join(app, "helpers", "access_helper.php");
const NOW = "2026-11-10 12:00:00";

const base = { status: "Active", approval_status: 1, client_type: null, plan: null, payment_registered: 0, access_start: null, access_end: null, allow_audio: 0, allow_image: 0, allow_imagemusic: 0, allow_demo: 0 };
const U = (o) => ({ ...base, ...o });
const Q = (type, tier, id = "q1") => ({ type, id, tier });
const grant = (o = {}) => ({ quiz_type: "audio", quiz_id: "q1", days_valid: 3, first_opened_at: null, expires_at: null, ...o });

// One PHP process runs all cases at once.
function decide(cases, enabled = true) {
  const code = `require ${JSON.stringify(helper)};
    $cases = json_decode(file_get_contents('php://stdin'), true); $now = new DateTime(${JSON.stringify(NOW)});
    echo json_encode(array_map(function($c) use ($now) { $d = access_can_open($c[0], $c[1], $c[2], $now, ${enabled ? "true" : "false"}); return [$d['allowed'], $d['reason'], $d['starts_grant']]; }, $cases));`;
  return JSON.parse(execFileSync("php", ["-r", code], { input: JSON.stringify(cases), encoding: "utf8" }));
}

// [name, user, quiz, grants, expectedAllowed, expectedReason]
const matrix = [
  ["not logged in", null, Q("audio", "registered"), [], false, "login_required"],
  ["registered-tier show: any logged-in customer", U({ approval_status: 0 }), Q("audio", "registered"), [], true, "open_to_registered"],
  ["registered-tier show: even an unapproved old customer", U({ approval_status: 0, status: "Inactive" }), Q("image", "registered"), [], true, "open_to_registered"],
  ["unapproved customer, restricted show", U({ approval_status: 0, allow_audio: 1 }), Q("audio", null), [], false, "not_approved"],
  ["inactive customer", U({ status: "Inactive", allow_audio: 1 }), Q("audio", null), [], false, "inactive"],
  ["full access: restricted", U({ client_type: "full" }), Q("imagemusic", "restricted"), [], true, "full_access"],
  ["full access: demo", U({ client_type: "full" }), Q("audio", "demo"), [], true, "full_access"],
  ["full access but unapproved", U({ client_type: "full", approval_status: 0 }), Q("audio", null), [], false, "not_approved"],
  ["demo show with demo tick", U({ allow_demo: 1 }), Q("audio", "demo"), [], true, "demo_allowed"],
  ["demo show without demo tick", U({}), Q("audio", "demo"), [], false, "demo_not_allowed"],
  ["test user: ticked type", U({ client_type: "test", allow_image: 1 }), Q("image", null), [], true, "type_allowed"],
  ["test user: other type not ticked", U({ client_type: "test", allow_image: 1 }), Q("audio", null), [], false, "not_allowed"],
  ["test user: picked show", U({ client_type: "test" }), Q("audio", null), [grant({ days_valid: null })], true, "grant_active"],
  ["test user: picked show, other id", U({ client_type: "test" }), Q("audio", null, "q2"), [grant()], false, "not_allowed"],
  ["old customer (no type) with no ticks", U({}), Q("audio", null), [], false, "not_allowed"],
  ["old customer (no type) with a tick", U({ allow_audio: 1 }), Q("audio", null), [], true, "type_allowed"],
  ["paid all shows, in dates", U({ client_type: "paid", plan: "all_shows", payment_registered: 1, access_start: "2026-11-01 00:00:00", access_end: "2027-11-01 00:00:00" }), Q("imagemusic", null), [], true, "paid_plan"],
  ["paid all shows, expired", U({ client_type: "paid", plan: "all_shows", payment_registered: 1, access_end: "2026-11-09 23:59:59" }), Q("audio", null), [], false, "expired"],
  ["paid all shows, not started", U({ client_type: "paid", plan: "all_shows", payment_registered: 1, access_start: "2026-11-11 00:00:00" }), Q("audio", null), [], false, "not_started"],
  ["paid all shows, payment not registered", U({ client_type: "paid", plan: "all_shows", access_end: "2027-11-01 00:00:00" }), Q("audio", null), [], false, "payment_missing"],
  ["paid without a plan", U({ client_type: "paid", payment_registered: 1 }), Q("audio", null), [], false, "no_plan"],
  ["paid audio+simple: audio", U({ client_type: "paid", plan: "audio_simple", payment_registered: 1, access_end: "2027-11-01 00:00:00" }), Q("audio", null), [], true, "paid_plan"],
  ["paid audio+simple: image is excluded", U({ client_type: "paid", plan: "audio_simple", payment_registered: 1, access_end: "2027-11-01 00:00:00" }), Q("image", null), [], false, "plan_excludes_type"],
  ["paid audio+simple: expired", U({ client_type: "paid", plan: "audio_simple", payment_registered: 1, access_end: "2026-01-01 00:00:00" }), Q("audio", null), [], false, "expired"],
  ["birthday: first open starts the clock", U({ client_type: "paid", plan: "per_show", payment_registered: 1 }), Q("audio", null), [grant()], true, "grant_first_open"],
  ["birthday: inside the 3 days", U({ client_type: "paid", plan: "per_show", payment_registered: 1 }), Q("audio", null), [grant({ first_opened_at: "2026-11-09 10:00:00", expires_at: "2026-11-12 10:00:00" })], true, "grant_active"],
  ["birthday: after the 3 days", U({ client_type: "paid", plan: "per_show", payment_registered: 1 }), Q("audio", null), [grant({ first_opened_at: "2026-11-06 10:00:00", expires_at: "2026-11-09 10:00:00" })], false, "expired"],
  ["birthday: payment not registered", U({ client_type: "paid", plan: "per_show" }), Q("audio", null), [grant()], false, "payment_missing"],
  ["birthday: a show that was not picked", U({ client_type: "paid", plan: "per_show", payment_registered: 1 }), Q("audio", null, "other"), [grant()], false, "not_allowed"],
  ["birthday: same id but other show type", U({ client_type: "paid", plan: "per_show", payment_registered: 1 }), Q("image", null), [grant()], false, "not_allowed"],
  ["crossword is not controlled yet", U({ client_type: "full" }), Q("crossword", null), [], false, "unknown_type"],
  ["missing tier is treated as restricted", U({ client_type: "test" }), Q("audio", undefined), [], false, "not_allowed"],
];

test("access matrix", () => {
  const out = decide(matrix.map(([, u, q, g]) => [u, q, g]));
  const bad = [];
  matrix.forEach(([name, , , , allowed, reason], i) => {
    if (out[i][0] !== allowed || out[i][1] !== reason) bad.push(`${name}: expected ${allowed}/${reason}, got ${out[i][0]}/${out[i][1]}`);
  });
  assert.deepStrictEqual(bad, []);
});

test("REGRESSION: with the switch off everything is open, even for visitors who are not logged in", () => {
  const out = decide([[null, Q("audio", null), []], [U({ approval_status: 0 }), Q("image", "demo"), []], [null, Q("crossword", null), []]], false);
  assert.deepStrictEqual(out.map((r) => [r[0], r[1]]), [[true, "switch_off"], [true, "switch_off"], [true, "switch_off"]]);
});

test("only the first open of a grant starts the clock", () => {
  const u = U({ client_type: "paid", plan: "per_show", payment_registered: 1 });
  const out = decide([[u, Q("audio", null), [grant()]], [u, Q("audio", null), [grant({ first_opened_at: "2026-11-10 09:00:00", expires_at: "2026-11-13 09:00:00" })]]]);
  assert.deepStrictEqual(out.map((r) => r[2]), [true, false]);
});

test("3 days run from the moment of first open", () => {
  const out = execFileSync("php", ["-r", `require ${JSON.stringify(helper)}; echo access_grant_expiry(3, new DateTime(${JSON.stringify(NOW)}));`], { encoding: "utf8" });
  assert.strictEqual(out, "2026-11-13 12:00:00");
});

test("the shipped switch is OFF", () => {
  const cfg = fs.readFileSync(path.join(app, "config", "access.php"), "utf8");
  assert.match(cfg, /\$config\['access_control_enabled'\]\s*=\s*FALSE;/);
});
