// End-to-end scenarios against the LOCAL site (php -S localhost:8888 from httpd.www)
// with the local scratch database. Drives the site over HTTP like a real customer
// and admin: register -> admin approves and sets access -> customer opens the catalog,
// presses Start, orders. Shows and their tags come from the live backend (read-only).
//
//   node tests/e2e/local-site.e2e.js
//
// Not part of `node --test tests/*.test.js`: it needs the local server and it ADDS
// rows (new zz-e2e-* customers, their grants and orders) to the local scratch
// database. It refuses to run against anything but localhost + bestefar_m3test,
// and it never deletes or edits rows other than the customers it created.
const assert = require("node:assert");
const fs = require("fs");
const path = require("path");
const { execFileSync } = require("child_process");

const BASE = "http://localhost:8888";
const app = path.join(__dirname, "..", "..", "httpd.www", "application");
const API = "https://api.bestefar.no/api";
const SWITCH_FILE = path.join(app, "config", "development", "access.php");

const dbName = (fs.readFileSync(path.join(app, "config", "development", "database.php"), "utf8").match(/'database'\s*=>\s*'([^']+)'/) || [])[1];
if (dbName !== "bestefar_m3test") throw new Error(`refusing to run: local database is ${dbName}, expected bestefar_m3test`);
const sql = (q) => execFileSync("mysql", ["-N", "-B", dbName, "-e", q], { encoding: "utf8" }).trim();
const adminPassword = fs.readFileSync(path.join(app, "config", "development", "TEST_ACCOUNTS.txt"), "utf8").match(/zz-admin .*password:\s*(\S+)/)[1];

class Browser {
  constructor() { this.cookies = {}; }
  async go(p, form) {
    const headers = {};
    if (Object.keys(this.cookies).length) headers.cookie = Object.entries(this.cookies).map(([k, v]) => `${k}=${v}`).join("; ");
    let body;
    if (form) { body = new URLSearchParams(form).toString(); headers["content-type"] = "application/x-www-form-urlencoded"; }
    const r = await fetch(BASE + p, { method: form ? "POST" : "GET", headers, body, redirect: "manual" });
    for (const c of r.headers.getSetCookie()) { const [kv] = c.split(";"); const i = kv.indexOf("="); this.cookies[kv.slice(0, i)] = kv.slice(i + 1); }
    const refresh = (r.headers.get("refresh") || "").replace(/^0;url=/i, "");
    return { status: r.status, location: r.headers.get("location") || refresh || null, html: await r.text() };
  }
}

const results = [];
const check = async (name, fn) => {
  try { await fn(); results.push(["ok", name]); console.log("ok   " + name); }
  catch (e) { results.push(["FAIL", name]); console.log("FAIL " + name + "\n     " + String(e.message).split("\n").join("\n     ")); }
};

async function catalogRows(browser) {
  const r = await browser.go("/home/sporreleker");
  if (r.location) return { redirect: r.location };
  const rows = [...r.html.matchAll(/<tr class="quiz-row"([^>]*)>([\s\S]*?)<\/tr>/g)].map(([, attrs, inner]) => ({
    id: attrs.match(/data-id="([^"]*)"/)[1],
    category: attrs.match(/data-category="([^"]*)"/)[1],
    state: /data-locked="1"/.test(attrs) ? (inner.match(/quiz-lock-text">([^<]*)</) || [, "locked"])[1] : /data-start-url=/.test(attrs) ? "open" : "as today",
  }));
  return { rows, html: r.html };
}

(async () => {
  // Show tags from the live backend: "registered" shows open for every logged-in customer.
  const tiers = {};
  for (const list of ["forms/get-all", "image-quiz/get-all-image-quizzes", "image-music/get-all-image-music-shows"]) {
    for (const s of (await (await fetch(`${API}/${list}`)).json()).shows) tiers[s.id || s._id] = s.accessTier || "restricted";
  }
  const TYPE = { AudioQuiz: "audio", BildeQuiz: "image", BilderMusikk: "imagemusic" };

  const stamp = Date.now().toString(36);
  const PASSWORD = "E2e-test-1234";
  const admin = new Browser();
  await admin.go("/login/admin_login_varify", { username: "zz-admin", password: adminPassword });
  assert.strictEqual((await admin.go("/admin/dashboard")).status, 200, "admin login");

  // Registers a new customer through the real registration form; returns users_id.
  async function register(name) {
    const email = `zz-e2e-${stamp}-${name}@test.local`;
    const r = await new Browser().go("/login/register/create", {
      institute: "E2E " + name, address: "Testveien 1", postal1: "0001", postal2: "Oslo", web: "", projector: "Yes", amplifier_speakers: "No",
      fname: "ZZ", lname: name, phone: "11111111", mobile: "22222222", email, password: PASSWORD, register: " ",
    });
    assert.match(r.location || "", /registration_msg/, "registration redirects to the thank-you message");
    const id = Number(sql(`select users_id from users where email='${email}'`));
    assert.ok(id > 0, "customer row created");
    return { id, email };
  }
  async function adminSets(c, { approve = true, status = "Active", access = {}, add_show = "", add_days = "" }) {
    if (approve) await admin.go("/ajax/update_approval_status", { id: c.id, status: 1 });
    const form = { users_id: c.id, status, key_person: "", notes: "", grant_add_show: add_show, grant_add_days: add_days };
    for (const [k, v] of Object.entries(access)) form[`access[${k}]`] = v;
    const r = await admin.go("/admin/users/update_access", form);
    assert.match(r.location || "", /admin\/users/, "admin save redirects to the customer list");
  }
  async function loginAs(c) {
    const b = new Browser();
    const r = await b.go("/login/user_login_varify", { email: c.email, password: PASSWORD });
    assert.match(r.location || "", /\/home$/, `customer login ${c.email}`);
    return b;
  }
  const iso = (days) => new Date(Date.now() + days * 864e5).toISOString().slice(0, 10);
  const expectRows = (rows, want) => {
    const wrong = rows.filter((r) => r.category !== "TekstQuiz").map((r) => {
      const expected = tiers[r.id] === "registered" ? "open" : want(r);
      return r.state === expected ? null : `${r.category} ${r.id}: got ${r.state}, expected ${expected}`;
    }).filter(Boolean);
    assert.deepStrictEqual(wrong, []);
    assert.ok(rows.filter((r) => r.category === "TekstQuiz").every((r) => r.state === "as today"), "crosswords as today");
  };

  const original = fs.readFileSync(SWITCH_FILE, "utf8");
  try {
    fs.writeFileSync(SWITCH_FILE, original.replace(/access_control_enabled'\]\s*=\s*(TRUE|FALSE)/, "access_control_enabled'] = TRUE"));

    await check("logged out: catalog asks for login", async () => {
      const r = await catalogRows(new Browser());
      assert.match(r.redirect || "", /login/);
    });

    const unapproved = await register("unapproved");
    await check("just registered (not approved, 'Not Paid'): only shows open to all registered", async () => {
      const { rows } = await catalogRows(await loginAs(unapproved));
      assert.ok(rows.length >= 10, `catalog has ${rows.length} rows`);
      expectRows(rows, () => "Låst");
    });

    const full = await register("full");
    await adminSets(full, { access: { client_type: "full" } });
    await check("B. full customer (admin: approved, Paid, type full): every show open", async () => {
      expectRows((await catalogRows(await loginAs(full))).rows, () => "open");
    });

    const testAudio = await register("test-audio");
    await adminSets(testAudio, { access: { client_type: "test", allow_audio: "1" } });
    const testBrowser = await loginAs(testAudio);
    await check("A. test customer with the AudioQuiz tick: AudioQuiz open, picture quizzes locked", async () => {
      expectRows((await catalogRows(testBrowser)).rows, (r) => (r.category === "AudioQuiz" ? "open" : "Låst"));
    });
    await check("A. Start on a locked picture quiz: refused, back to the catalog with the message", async () => {
      const { rows } = await catalogRows(testBrowser);
      const locked = rows.find((r) => r.state === "Låst");
      const r = await testBrowser.go(`/home/open_quiz/${TYPE[locked.category]}/${locked.id}`);
      assert.match(r.location || "", /home\/sporreleker\?access=not_allowed/);
      const back = await testBrowser.go("/home/sporreleker?access=not_allowed");
      assert.match(back.html, /Du har ikke tilgang til denne quizen\./);
    });
    await check("A. Start on an open AudioQuiz: goes to the player", async () => {
      const open = (await catalogRows(testBrowser)).rows.find((r) => r.category === "AudioQuiz" && r.state === "open");
      const r = await testBrowser.go(`/home/open_quiz/audio/${open.id}`);
      assert.match(r.location || "", new RegExp(`bestefar-audio/index\\.html\\?screen=1&show=${open.id}&autostart=1`));
    });
    await check("A. ordering (Alle variantene) works for a test customer and opens nothing by itself", async () => {
      const r = await testBrowser.go("/home/bestilling", { plan: "all_shows", start_date: "", comment: "E2E" });
      assert.match(r.location || "", /home\/bestilling\/sendt/);
      assert.strictEqual(sql(`select concat(plan,'/',price,'/',status) from orders where users_id=${testAudio.id}`), "all_shows/2900/new");
      expectRows((await catalogRows(testBrowser)).rows, (r) => (r.category === "AudioQuiz" ? "open" : "Låst"));
    });

    const inactiveTest = await register("test-not-paid");
    await adminSets(inactiveTest, { status: "Inactive", access: { client_type: "test", allow_audio: "1" } });
    await check("FINDING: test customer approved + AudioQuiz tick but old status left at 'Not Paid' (registration default)", async () => {
      const { rows } = await catalogRows(await loginAs(inactiveTest));
      const audio = rows.filter((r) => r.category === "AudioQuiz" && tiers[r.id] !== "registered");
      console.log(`     -> ${audio.filter((r) => r.state === "open").length} of ${audio.length} restricted AudioQuiz rows open`);
      assert.ok(audio.every((r) => r.state === "Låst"), "locked because status is Inactive");
    });

    const c2 = await register("c2");
    await adminSets(c2, { access: { client_type: "paid", plan: "audio_simple", payment_registered: "1", access_start: iso(-1), access_end: iso(364) } });
    await check("C2. Audio Quiz og Tekst Quiz i ett år (paid): AudioQuiz open, picture quizzes locked", async () => {
      expectRows((await catalogRows(await loginAs(c2))).rows, (r) => (r.category === "AudioQuiz" ? "open" : "Låst"));
    });

    const c3 = await register("c3");
    await adminSets(c3, { access: { client_type: "paid", plan: "all_shows", payment_registered: "1", access_start: iso(-1), access_end: iso(364) } });
    await check("C3. Alle variantene i ett år (paid): every show open", async () => {
      expectRows((await catalogRows(await loginAs(c3))).rows, () => "open");
    });

    const c3unpaid = await register("c3-unpaid");
    await adminSets(c3unpaid, { access: { client_type: "paid", plan: "all_shows", access_start: iso(-1), access_end: iso(364) } });
    await check("C3 before payment is registered: everything locked", async () => {
      expectRows((await catalogRows(await loginAs(c3unpaid))).rows, () => "Låst");
    });

    const c3old = await register("c3-ended");
    await adminSets(c3old, { access: { client_type: "paid", plan: "all_shows", payment_registered: "1", access_start: iso(-400), access_end: iso(-35) } });
    await check("C3 year over: every row 'Utgått' and the renewal banner", async () => {
      const { rows, html } = await catalogRows(await loginAs(c3old));
      expectRows(rows, () => "Utgått");
      assert.match(html, /Ditt abonnement har utgått\. Vil du fornye det\?/);
    });

    const c1 = await register("c1");
    const shows = (await catalogRows(await loginAs(full))).rows.filter((r) => r.category !== "TekstQuiz" && tiers[r.id] !== "registered");
    const [p1, p2] = [shows.find((r) => r.category === "AudioQuiz"), shows.find((r) => r.category !== "AudioQuiz")];
    const access = { client_type: "paid", plan: "per_show", payment_registered: "1" };
    await adminSets(c1, { access, add_show: `${TYPE[p1.category]}:${p1.id}`, add_days: "3" });
    await adminSets(c1, { approve: false, access, add_show: `${TYPE[p2.category]}:${p2.id}`, add_days: "3" });
    const c1Browser = await loginAs(c1);
    await check("C1. 2 quizer i 3 dager (paid): exactly the 2 picked shows open", async () => {
      expectRows((await catalogRows(c1Browser)).rows, (r) => ([p1.id, p2.id].includes(r.id) ? "open" : "Låst"));
    });
    await check("C1. first Start begins the 3 days (saved in the database), second Start keeps the clock", async () => {
      await c1Browser.go(`/home/open_quiz/${TYPE[p1.category]}/${p1.id}`);
      const first = sql(`select concat(first_opened_at,'|',expires_at) from user_show_grants where users_id=${c1.id} and quiz_id='${p1.id}'`);
      const [opened, expires] = first.split("|").map((t) => new Date(t.replace(" ", "T")));
      assert.strictEqual((expires - opened) / 3600000, 72);
      await c1Browser.go(`/home/open_quiz/${TYPE[p1.category]}/${p1.id}`);
      assert.strictEqual(sql(`select concat(first_opened_at,'|',expires_at) from user_show_grants where users_id=${c1.id} and quiz_id='${p1.id}'`), first);
      assert.strictEqual(sql(`select first_opened_at is null from user_show_grants where users_id=${c1.id} and quiz_id='${p2.id}'`), "1", "the other show's clock has not started");
    });

    await check("admin: customer list shows the Access column for the new customers", async () => {
      const r = await admin.go("/admin/users");
      for (const label of ["Full", "Paid · All shows", "Paid · Per show"]) assert.ok(r.html.includes(label), label);
    });

    fs.writeFileSync(SWITCH_FILE, original.replace(/access_control_enabled'\]\s*=\s*(TRUE|FALSE)/, "access_control_enabled'] = FALSE"));
    await check("switch OFF: catalog as today for everybody (no login needed, no locks)", async () => {
      for (const b of [new Browser(), await loginAs(unapproved), c1Browser]) {
        const { rows, redirect } = await catalogRows(b);
        assert.strictEqual(redirect, undefined);
        assert.ok(rows.length >= 10 && rows.every((r) => r.state === "as today"));
      }
    });
    await check("switch OFF: Start opens the player for anyone", async () => {
      const r = await new Browser().go(`/home/open_quiz/image/${shows.find((s) => s.category === "BildeQuiz").id}`);
      assert.match(r.location || "", /bestefar-image\/index\.html\?show=/);
    });
  } finally {
    fs.writeFileSync(SWITCH_FILE, original);
  }
  const failed = results.filter(([r]) => r === "FAIL").length;
  console.log(`\n${results.length - failed}/${results.length} scenarios passed (customers zz-e2e-${stamp}-* left in ${dbName})`);
  process.exit(failed ? 1 : 0);
})().catch((e) => { console.error(e); process.exit(1); });
