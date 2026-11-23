// `node --test tests/*.test.js` - Module 8 of ACCESS_CONTROL_PLAN.md: remaining bugs
// (ajax login check, home/shows errors, dead save_cart route, Admin Emails warning,
// admin passwords as hashes).
// APP_ROOT can point at an older copy of httpd.www/application to prove the tests catch the old behaviour.
const test = require("node:test");
const assert = require("node:assert");
const fs = require("fs");
const path = require("path");
const { execFileSync } = require("child_process");

const repo = path.join(__dirname, "..");
const app = process.env.APP_ROOT || path.join(repo, "httpd.www", "application");
const read = (p) => fs.readFileSync(path.join(app, p), "utf8");
const run = (harness, action, scenario) =>
  JSON.parse(execFileSync("php", [path.join(__dirname, "support", harness), app, action], { input: JSON.stringify(scenario), encoding: "utf8" }));
const ajax = (method, admin_login) => run("controller_harness.php", "ajax", { method, admin_login });
const shows = (param1, param2, rows = [{ image: "x.jpg" }]) => run("controller_harness.php", "shows", { param1, param2, rows });
const model = (action, scenario) => run("order_harness.php", action, scenario);

// Every action of controllers/Ajax.php, read from the file itself so a new one can't slip past.
const AJAX_ACTIONS = [...read("controllers/Ajax.php").matchAll(/public function (\w+)\s*\(/g)].map((m) => m[1]);
const PUBLIC_AJAX = ["index", "get_video_images"];

// ── ajax/* needs the admin login ────────────────────────────────────────────

test("ajax: every admin action is refused (403) without the admin login", () => {
  const admin = AJAX_ACTIONS.filter((a) => !PUBLIC_AJAX.includes(a));
  assert.ok(admin.length >= 12, `found ${admin.length} admin actions`);
  for (const action of admin) {
    for (const login of [undefined, "0", 0, "", "2"]) {
      const r = ajax(action, login);
      assert.strictEqual(r.exited, true, `${action} with admin_login=${login}`);
      assert.strictEqual(r.status, 403, action);
      assert.strictEqual(r.echo, "Forbidden", action);
    }
  }
});

test("ajax: a different spelling of the action name is refused too (PHP method names ignore case)", () => {
  for (const action of ["Update_Corder", "UPDATE_APPROVAL_STATUS", "Delete_V_Image"]) {
    assert.strictEqual(ajax(action).status, 403, action);
  }
});

test("ajax: the admin (logged in) can still use every action", () => {
  for (const action of AJAX_ACTIONS) {
    for (const login of ["1", 1]) {
      const r = ajax(action, login);
      assert.strictEqual(r.constructed, true, `${action} with admin_login=${login}`);
      assert.strictEqual(r.status, 200, action);
    }
  }
});

test("REGRESSION: the public blog page's video pictures (get_video_images) need no login", () => {
  const r = ajax("get_video_images");
  assert.strictEqual(r.constructed, true);
  assert.strictEqual(r.status, 200);
  assert.match(read("views/frontend/blog_details.php"), /ajax\/get_video_images/);
});

test("ajax: every page that calls an admin action is an admin page (the admin session cookie goes along)", () => {
  const views = path.join(app, "views");
  const walk = (dir) => fs.readdirSync(dir, { withFileTypes: true }).flatMap((d) => (d.isDirectory() ? walk(path.join(dir, d.name)) : [path.join(dir, d.name)]));
  for (const file of walk(views).filter((f) => f.endsWith(".php"))) {
    for (const [, action] of fs.readFileSync(file, "utf8").matchAll(/ajax\/(\w+)/g)) {
      if (action === "libs" || PUBLIC_AJAX.includes(action)) continue; // cdnjs URLs / public actions
      assert.match(path.relative(views, file), /^admin\//, `${action} called from ${path.relative(views, file)}`);
    }
  }
});

// ── home/shows ──────────────────────────────────────────────────────────────

test("home/shows: no category, unknown category or not a number -> 404 instead of a PHP/SQL error page", () => {
  assert.strictEqual(shows("", "", []).status, 404);
  assert.strictEqual(shows("999", "", []).status, 404);
  assert.strictEqual(shows("1", "999", []).status, 404);
  assert.strictEqual(shows("abc", "", []).status, 404);
});

test("home/shows: URL segments reach the SQL and the view as whole numbers only", () => {
  const r = shows("1 OR 1", "0");
  assert.strictEqual(r.param1, 1);
  assert.strictEqual(r.param2, 0);
  assert.deepStrictEqual(r.where, [["categories", "categories_id", 1]]);
  const sub = shows("5", "7abc");
  assert.strictEqual(sub.param2, 7);
  assert.deepStrictEqual(sub.where, [["sub_categories", "sub_categories_id", 7]]);
});

test("REGRESSION: a real category (and sub-category) page renders as before", () => {
  const r = shows("5", "");
  assert.strictEqual(r.status, 200);
  assert.strictEqual(r.view, "frontend/index");
  assert.equal(r.param1, 5); // "5" before the fix, 5 after: same page
  assert.strictEqual(shows("5", "3").status, 200);
});

test("home/save_cart (called a model method that never existed, so always a 500) is gone", () => {
  assert.doesNotMatch(read("controllers/Home.php"), /function save_cart|cart_data_saved/);
});

// ── Admin Emails page ───────────────────────────────────────────────────────

test("Admin Emails page: no leftover customer modal looping over an undefined $users", () => {
  const view = read("views/admin/admin_email.php");
  assert.doesNotMatch(view, /\$users\b/);
  assert.doesNotMatch(view, /changeApprovalStatus/);
  assert.match(view, /foreach\(\$admin_emails as \$data\): \$i\+\+; \?>\s*<tr>/, "one <tr> per e-mail");
  assert.match(read("controllers/Admin.php"), /\$data\['admin_emails'\]\s*= \$this->Admin_model->get_data_desc\('admin_emails'\)/);
});

// ── Admin passwords ─────────────────────────────────────────────────────────

const WIDE = [{ Field: "password", Type: "varchar(255)" }];
const NARROW = [{ Field: "password", Type: "varchar(55)" }];
const adminRow = (password) => ({ admin_id: 1, name: "Admin", username: "boss", password });
const login = (password, stored, columns = WIDE) =>
  model("admin_login", { tables: { admin: [adminRow(stored)] }, columns, post: { username: "boss", password } });
const hash = (p) => execFileSync("php", ["-r", `echo password_hash(${JSON.stringify(p)}, PASSWORD_DEFAULT);`], { encoding: "utf8" });

test("admin login: old plain-text password still works and is replaced by a hash", () => {
  const r = login("hemmelig", "hemmelig");
  assert.strictEqual(r.result.admin_id, 1);
  assert.strictEqual(r.updates.length, 1);
  assert.deepStrictEqual(r.updates[0].where, { admin_id: 1 });
  assert.match(r.updates[0].data.password, /^\$2y\$/);
});

test("admin login: a hashed password logs in and nothing is rewritten", () => {
  const r = login("hemmelig", hash("hemmelig"));
  assert.strictEqual(r.result.admin_id, 1);
  assert.deepStrictEqual(r.updates, []);
});

test("admin login: wrong password, wrong user or empty password is refused", () => {
  assert.strictEqual(login("feil", "hemmelig").result, false);
  assert.strictEqual(login("feil", hash("hemmelig")).result, false);
  assert.strictEqual(login("", "").result, false);
  const other = model("admin_login", { tables: { admin: [adminRow("hemmelig")] }, columns: WIDE, post: { username: "noen", password: "hemmelig" } });
  assert.strictEqual(other.result, false);
});

test("admin login before the SQL is run (column still varchar(55)): logs in, password left as it is", () => {
  const r = login("hemmelig", "hemmelig", NARROW);
  assert.strictEqual(r.result.admin_id, 1);
  assert.deepStrictEqual(r.updates, [], "a 60-character hash would be cut to 55 and lock the admin out");
});

test("admin profile: empty password box keeps the current password", () => {
  const r = model("admin_profile", { session: { admin_id: 1 }, columns: WIDE, post: { name: "A", phone: "1", email: "a@b.no", username: "boss", password: "", address: "x" } });
  assert.strictEqual(r.updates.length, 1);
  assert.ok(!("password" in r.updates[0].data));
  assert.strictEqual(r.updates[0].data.username, "boss");
});

test("admin profile: a new password is stored as a hash (plain text until the SQL is run)", () => {
  const post = { name: "A", phone: "1", email: "a@b.no", username: "boss", password: "nytt-passord", address: "x" };
  const wide = model("admin_profile", { session: { admin_id: 1 }, columns: WIDE, post });
  assert.match(wide.updates[0].data.password, /^\$2y\$/);
  const narrow = model("admin_profile", { session: { admin_id: 1 }, columns: NARROW, post });
  assert.strictEqual(narrow.updates[0].data.password, "nytt-passord");
});

test("admin profile form no longer prints the stored password into the page", () => {
  const view = read("views/admin/myprofile.php");
  assert.doesNotMatch(view, /profile_data->password/);
  assert.match(view, /name="password"[^>]*value=""/);
  assert.doesNotMatch(view.match(/<input type="password" name="password"[^>]*>/)[0], /required/);
});

test("SQL widens admin.password only; down script warns it is only safe before hashes exist", () => {
  const sql = (f) => fs.readFileSync(path.join(repo, "database", "migrations", f), "utf8");
  const up = sql("2026-11-23_admin_password_up.sql").replace(/--.*$/gm, "").trim();
  assert.strictEqual(up, "ALTER TABLE `admin` MODIFY `password` varchar(255) NOT NULL;");
  const down = sql("2026-11-23_admin_password_down.sql");
  assert.match(down, /restore the backup/);
  assert.strictEqual(down.replace(/--.*$/gm, "").trim(), "ALTER TABLE `admin` MODIFY `password` varchar(55) NOT NULL;");
});

test("REGRESSION: customer login rules unchanged (same helper, same upgrade on login)", () => {
  const m = read("models/Admin_model.php");
  const user = m.slice(m.indexOf("function user_login_varify("), m.indexOf("function activate_me("));
  assert.match(user, /get_where\('users', array\('email'=>\$username\)\)/);
  assert.match(user, /user_password_pick_login\(\$rows, \$password\)/);
  assert.match(user, /update\('users', array\('password'=>\$picked\['new_hash'\]\)\)/);
});
