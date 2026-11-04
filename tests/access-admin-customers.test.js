// `node --test tests/*.test.js` - admin "Customer access" (Module 5 of ACCESS_CONTROL_PLAN.md).
const test = require("node:test");
const assert = require("node:assert");
const fs = require("fs");
const path = require("path");
const { execFileSync } = require("child_process");

const app = process.env.APP_ROOT || path.join(__dirname, "..", "httpd.www", "application");
const harness = path.join(__dirname, "support", "access_harness.php");
const run = (action, scenario) => JSON.parse(execFileSync("php", [harness, app, action], { input: JSON.stringify(scenario), encoding: "utf8" }));
const fn = (name, ...args) => run("fn", { fn: name, args });
const ID = "68c93334e1e8359caede34c3";

// ── cleaning the form ────────────────────────────────────────────────────────
test("form fields: valid values are kept", () => {
  assert.deepStrictEqual(fn("access_customer_fields", { client_type: "paid", plan: "all_shows", payment_registered: "1", access_start: "2026-11-01", access_end: "2027-10-31", allow_audio: "1", allow_demo: "1" }), {
    client_type: "paid", plan: "all_shows", payment_registered: 1, access_start: "2026-11-01 00:00:00", access_end: "2027-10-31 23:59:59",
    allow_audio: 1, allow_image: 0, allow_imagemusic: 0, allow_demo: 1,
  });
});

test("form fields: anything unexpected becomes empty, never stored as typed", () => {
  const out = fn("access_customer_fields", { client_type: "admin", plan: "free'; DROP TABLE users;--", access_start: "31.12.2026", access_end: "2026-02-30" });
  assert.deepStrictEqual([out.client_type, out.plan, out.access_start, out.access_end], [null, null, null, null]);
  assert.deepStrictEqual(fn("access_customer_fields", null).allow_audio, 0, "no form at all = all ticks off");
});

test("picked show: valid type:id and days; bad input refused", () => {
  assert.deepStrictEqual(fn("access_new_grant", 7, `audio:${ID}`, "3"), { users_id: 7, quiz_type: "audio", quiz_id: ID, days_valid: 3 });
  assert.strictEqual(fn("access_new_grant", 7, `image:${ID}`, "").days_valid, null, "empty days = no time limit");
  for (const [show, days] of [[`crossword:${ID}`, "3"], ["audio:not-an-id", "3"], [ID, "3"], [`audio:${ID}`, "0"], [`audio:${ID}`, "400"]]) {
    assert.strictEqual(fn("access_new_grant", 7, show, days), null, `${show} ${days}`);
  }
});

// ── saving ───────────────────────────────────────────────────────────────────
const save = (o) => run("save_customer", { users_id: 7, fields: { client_type: "test", allow_image: "1" }, remove: [], add_show: "", add_days: "", tables: { user_show_grants: [] }, ...o });

test("save: updates only that customer's row with the cleaned fields", () => {
  const out = save({});
  assert.strictEqual(out.ok, true);
  assert.strictEqual(out.updates.length, 1);
  assert.deepStrictEqual(out.updates[0].where, [{ users_id: 7 }]);
  assert.deepStrictEqual([out.updates[0].data.client_type, out.updates[0].data.allow_image], ["test", 1]);
});

test("save: removing picked shows only touches that customer's grants", () => {
  const out = save({ remove: ["5", "abc", "9"] });
  assert.deepStrictEqual(out.deletes, [{ table: "user_show_grants", where: [{ users_id: 7 }, { "grant_id IN": [5, 9] }] }]);
});

test("save: adds a picked show once; the same show again is not duplicated", () => {
  assert.deepStrictEqual(save({ add_show: `audio:${ID}`, add_days: "3" }).inserts, [{ table: "user_show_grants", data: { users_id: 7, quiz_type: "audio", quiz_id: ID, days_valid: 3 } }]);
  const existing = { tables: { user_show_grants: [{ grant_id: 1, users_id: 7, quiz_type: "audio", quiz_id: ID }] } };
  assert.deepStrictEqual(save({ ...existing, add_show: `audio:${ID}` }).inserts, []);
});

test("save: does nothing before the Module 1 SQL has been run, or without a customer id", () => {
  assert.deepStrictEqual(save({ ready: false }), { ok: false, updates: [], deletes: [], inserts: [] });
  assert.strictEqual(save({ users_id: 0 }).ok, false);
});

// ── customer list summary ────────────────────────────────────────────────────
test("customer list shows type, plan and state in one line", () => {
  const s = (u) => fn("Access_model::summary", u);
  assert.strictEqual(s({ client_type: null }), "Not set");
  assert.strictEqual(s({ client_type: "full" }), "Full");
  assert.strictEqual(s({ client_type: "paid", plan: "all_shows", payment_registered: 1, access_end: "2099-01-01" }), "Paid · All shows");
  assert.strictEqual(s({ client_type: "paid", plan: "per_show", payment_registered: 0 }), "Paid · Per show · not paid");
  assert.strictEqual(s({ client_type: "paid", plan: "audio_simple", payment_registered: 1, access_end: "2026-01-01" }), "Paid · AudioQuiz + simple · Utgått");
});

// ── the form section ─────────────────────────────────────────────────────────
const renderSection = (user, grants = [], show_choices = []) =>
  run("render", { view: "views/admin/modals/customer_access.php", vars: { user, grants, show_choices } }).html;

test("form section shows the customer's current settings", () => {
  const html = renderSection({ client_type: "paid", plan: "per_show", payment_registered: 1, access_end: "2027-01-31 23:59:59", allow_demo: 1, allow_audio: 0 });
  assert.match(html, /<option value="paid" selected>/);
  assert.match(html, /<option value="per_show" selected>/);
  assert.match(html, /name="access\[payment_registered\]" value="1" checked/);
  assert.match(html, /name="access\[access_end\]" value="2027-01-31"/);
  assert.match(html, /name="access\[allow_demo\]" value="1" checked/);
  assert.match(html, /name="access\[allow_audio\]" value="1" >/);
});

test("form section lists picked shows by name with their time left, and offers shows to add", () => {
  const html = renderSection({ client_type: "paid" }, [
    { grant_id: 4, quiz_type: "audio", quiz_id: ID, days_valid: 3, first_opened_at: null, expires_at: null },
    { grant_id: 5, quiz_type: "image", quiz_id: "x", days_valid: 3, first_opened_at: "2026-01-01 10:00:00", expires_at: "2026-01-04 10:00:00" },
  ], [{ value: `audio:${ID}`, label: "AudioQuiz - Elvis Presley" }]);
  assert.match(html, /name="grant_remove\[\]" value="4"/);
  assert.match(html, /<b>AudioQuiz - Elvis Presley<\/b> - 3 days from first open \(not opened yet\)/);
  assert.match(html, /ended 04\.01\.2026 10:00/);
  assert.match(html, /<option value="audio:68c93334e1e8359caede34c3">AudioQuiz - Elvis Presley<\/option>/);
});

test("customer names in the form are escaped", () => {
  assert.match(renderSection({}, [], [{ value: "audio:x", label: "<script>x</script>" }]), /&lt;script&gt;/);
});

// ── wiring ───────────────────────────────────────────────────────────────────
test("wiring: saved with the existing Update button, shown only when the database is ready", () => {
  const admin = fs.readFileSync(path.join(app, "controllers", "Admin.php"), "utf8");
  assert.match(admin, /update_user_access\(\);\s*\$this->load->model\('Access_model'\);\s*\$this->Access_model->save_customer_access\(/);
  assert.match(admin, /\$data\['access_ready'\] = \$this->Access_model->columns_ready\(\);/);
  const details = fs.readFileSync(path.join(app, "views", "admin", "modals", "user_details.php"), "utf8");
  const form = details.slice(details.indexOf('id="user_form"'), details.indexOf("</form>", details.indexOf('id="user_form"')));
  assert.match(form, /if \(\$CI->Access_model->columns_ready\(\)\) \{[\s\S]*load->view\('admin\/modals\/customer_access'/, "inside the form, behind columns_ready");
  assert.doesNotMatch(form, /\$this->Access_model/, "a view's $this is the loader: models must go through get_instance()");
  const list = fs.readFileSync(path.join(app, "views", "admin", "users.php"), "utf8");
  assert.strictEqual((list.match(/<\?php if \(\$access_ready\) \{ \?>/g) || []).length, 2, "header and cell together, so the table keeps its shape");
});
