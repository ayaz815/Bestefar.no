// `node --test tests/*.test.js` - Module 1 migration. Runs against a throwaway database
// (scratch_m1_test) built from the users table definition; never touches bestefar_no.
const test = require("node:test");
const assert = require("node:assert");
const fs = require("fs");
const path = require("path");
const { execFileSync, spawnSync } = require("child_process");

const dir = path.join(__dirname, "..", "database", "migrations");
const upSql = fs.readFileSync(path.join(dir, "2026-11-04_access_control_up.sql"), "utf8");
const downSql = fs.readFileSync(path.join(dir, "2026-11-04_access_control_down.sql"), "utf8");
const mysql = (args, input) => spawnSync("mysql", ["-uroot", ...args], { input, encoding: "utf8" });
const haveMysql = mysql(["-e", "select 1"]).status === 0;
const opts = { skip: haveMysql ? false : "local MySQL not running" };
const DB = "scratch_m1_test";
const q = (sql) => mysql(["-N", DB, "-e", sql]);

test("migration is additive: no DROP/DELETE/UPDATE in the up file", () => {
  assert.ok(!/\b(DROP|DELETE|UPDATE|TRUNCATE)\b/i.test(upSql));
});

test("up then down on a scratch users table", opts, () => {
  mysql(["-e", `DROP DATABASE IF EXISTS ${DB}; CREATE DATABASE ${DB}`]);
  try {
    q("CREATE TABLE users (users_id int AUTO_INCREMENT PRIMARY KEY, email varchar(100) NOT NULL, password varchar(100) NOT NULL, approval_status int DEFAULT 0, membership enum('Demo','Extended','Extra','Paused','') NOT NULL)");
    q("INSERT INTO users (email,password,approval_status,membership) VALUES ('a@x.no','p',1,'Demo'),('b@x.no','q',0,'Extra')");
    assert.strictEqual(mysql([DB], upSql).status, 0, "up failed");
    // REGRESSION: existing customers unchanged and flagged as "old"
    assert.strictEqual(q("SELECT COUNT(*) FROM users WHERE client_type IS NULL AND plan IS NULL AND payment_registered=0 AND allow_audio=0 AND allow_image=0 AND allow_imagemusic=0 AND allow_demo=0 AND access_end IS NULL").stdout.trim(), "2");
    assert.strictEqual(q("SELECT approval_status, membership FROM users ORDER BY users_id").stdout, "1\tDemo\n0\tExtra\n");
    // grants: a show can only be granted once per customer
    assert.strictEqual(q("INSERT INTO user_show_grants (users_id,quiz_type,quiz_id,days_valid) VALUES (1,'audio','abc',3)").status, 0);
    assert.notStrictEqual(q("INSERT INTO user_show_grants (users_id,quiz_type,quiz_id) VALUES (1,'audio','abc')").status, 0);
    assert.notStrictEqual(q("INSERT INTO user_show_grants (users_id,quiz_type,quiz_id) VALUES (1,'crossword','z')").status, 0, "crossword is postponed");
    assert.notStrictEqual(q("INSERT INTO users (email,password,client_type) VALUES ('c','c','bogus')").status, 0);
    assert.strictEqual(mysql([DB], downSql).status, 0, "down failed");
    assert.strictEqual(q("SHOW COLUMNS FROM users").stdout.trim().split("\n").length, 5);
    assert.strictEqual(q("SHOW TABLES LIKE 'user_show_grants'").stdout.trim(), "");
  } finally { mysql(["-e", `DROP DATABASE IF EXISTS ${DB}`]); }
});
