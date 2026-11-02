// `node --test tests/*.test.js` - customer password hashing (Module 0 of ACCESS_CONTROL_PLAN.md).
// APP_ROOT can point at an older copy of httpd.www/application to prove the tests catch the old behaviour.
const test = require("node:test");
const assert = require("node:assert");
const fs = require("fs");
const path = require("path");
const { execFileSync } = require("child_process");

const app = process.env.APP_ROOT || path.join(__dirname, "..", "httpd.www", "application");
const helper = path.join(app, "helpers", "user_password_helper.php");
const model = fs.readFileSync(path.join(app, "models", "Admin_model.php"), "utf8");
const login = fs.readFileSync(path.join(app, "controllers", "Login.php"), "utf8");

// Runs a PHP snippet with the helper loaded; returns the JSON it prints.
const php = (code) => JSON.parse(execFileSync("php", ["-r", `require ${JSON.stringify(helper)}; echo json_encode((function(){ ${code} })());`], { encoding: "utf8" }));
const fn = (name) => model.slice(model.indexOf(`function ${name}(`), model.indexOf("\n\t\tfunction ", model.indexOf(`function ${name}(`) + 10));

test("a new password is stored as a bcrypt hash that verifies", () => {
  const r = php(`$h = user_password_hash('hemmelig123'); return [$h, user_password_is_hashed($h), password_verify('hemmelig123', $h), $h === 'hemmelig123'];`);
  assert.match(r[0], /^\$2y\$/); assert.ok(r[1]); assert.ok(r[2]); assert.ok(!r[3]);
});

test("hash fits the users.password column (varchar(100))", () => {
  assert.ok(php(`return strlen(user_password_hash('x'));`) <= 100);
});

test("REGRESSION: a customer with an old plain-text password can still log in, and is flagged for upgrade", () => {
  assert.strictEqual(php(`return user_password_check('gammel', 'gammel');`), "ok_upgrade");
});

test("wrong or empty password is rejected, for plain-text and hashed rows alike", () => {
  assert.strictEqual(php(`return user_password_check('feil', 'gammel');`), "bad");
  assert.strictEqual(php(`return user_password_check('feil', user_password_hash('riktig'));`), "bad");
  assert.strictEqual(php(`return user_password_check('', '');`), "bad");
  assert.strictEqual(php(`return user_password_check('x', '');`), "bad");
});

test("a hashed row logs in with the right password and needs no upgrade", () => {
  assert.strictEqual(php(`return user_password_check('riktig', user_password_hash('riktig'));`), "ok");
});

test("a stored hash is never accepted as the password itself (pass-the-hash)", () => {
  assert.strictEqual(php(`$h = user_password_hash('riktig'); return user_password_check($h, $h);`), "bad");
});

test("login picks the matching row among customers sharing an email, and returns a new hash only for plain-text", () => {
  const r = php(`
    $rows = [['users_id'=>1,'password'=>'a'], ['users_id'=>2,'password'=>user_password_hash('b')], ['users_id'=>3,'password'=>'c']];
    $legacy = user_password_pick_login($rows, 'c');
    $hashed = user_password_pick_login($rows, 'b');
    $none = user_password_pick_login($rows, 'zzz');
    return [$legacy['row']['users_id'], user_password_is_hashed($legacy['new_hash']), $hashed['row']['users_id'], $hashed['new_hash'], $none['row']];`);
  assert.deepStrictEqual(r, [3, true, 2, null, null]);
});

test("user login no longer compares the password in SQL, and upgrades old rows", () => {
  const f = fn("user_login_varify");
  assert.ok(!/where\(\s*'password'/.test(f), "password must not be matched in the query");
  assert.match(f, /user_password_pick_login/);
  assert.match(f, /update\('users'/);
});

test("both registration paths store a hash, not the typed password", () => {
  assert.ok(!/save_data\['password'\]\s*=\s*\$this->input->post\('password'\)/.test(fn("register_user")));
  assert.match(fn("register_user"), /user_password_hash/);
  const add = fn("add_new_user");
  assert.match(add, /user_password_hash/);
  assert.ok(!/\['password'\]\s*=\s*\$this->input->post/.test(add));
});

test("BUG FIX: registering an email that already exists never updates that customer", () => {
  const f = fn("register_user");
  assert.ok(!/update\('users'/.test(f), "register_user must not update an existing customer");
  assert.match(f, /already_exist/);
});

test("controller tells the visitor when the email is already registered", () => {
  assert.match(login, /register_user\(\);/);
  assert.match(login, /already_exist/);
});

test("admin customer details no longer print a stored hash", () => {
  const view = fs.readFileSync(path.join(app, "views/admin/modals/user_details.php"), "utf8");
  assert.ok(!/<\?=\s*\$user->password;\s*\?>/.test(view));
});
