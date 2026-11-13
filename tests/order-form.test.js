// `node --test tests/*.test.js` - order form "Bestilling" (ACCESS_CONTROL_PLAN.md, Module 7)
// and the shared mailer that also fixes the registration e-mails.
// APP_ROOT can point at an older copy of httpd.www/application to prove the tests catch the old behaviour.
const test = require("node:test");
const assert = require("node:assert");
const fs = require("fs");
const path = require("path");
const { execFileSync } = require("child_process");

const repo = path.join(__dirname, "..");
const app = process.env.APP_ROOT || path.join(repo, "httpd.www", "application");
const read = (p) => fs.readFileSync(path.join(app, p), "utf8");
const exists = (p) => fs.existsSync(path.join(app, p));

// Calls a function from helpers/order_helper.php with JSON arguments.
const helper = (fn, ...args) => JSON.parse(execFileSync("php", ["-r", `
  define('BASEPATH', 'test'); require ${JSON.stringify(path.join(app, "helpers", "order_helper.php"))};
  echo json_encode(${fn}(...json_decode(file_get_contents('php://stdin'), true)));`], { input: JSON.stringify(args), encoding: "utf8" }));

// Runs Order_model / Admin_model::send_site_email in tests/support/order_harness.php.
const harness = (action, scenario) => JSON.parse(execFileSync("php", [path.join(__dirname, "support", "order_harness.php"), app, action], { input: JSON.stringify(scenario), encoding: "utf8" }));

const SHOWS = { "audio:a1": "AudioQuiz - Sang", "image:i1": "BildeQuiz - Dyr", "imagemusic:m1": "BilderMusikk - Vinter" };
const TODAY = "2026-11-14";
const customer = { users_id: 7, institute: "Instituttet for lek og moro", fname: "Kari", lname: "Nordmann", phone: "111", mobile: "222", email: "kari@example.no" };
const catalog = [
  { quiz_type: "AudioQuiz", external_id: "a1", name: "Sang" },
  { quiz_type: "BildeQuiz", external_id: "i1", name: "Dyr" },
  { quiz_type: "TekstQuiz", external_id: "t1", name: "Kryssord" },
];
const admins = [{ email: "admin@bestefar.no", status: "1" }, { email: "old@bestefar.no", status: "0" }];

// ── Order rules (helpers/order_helper.php) ────────────────────────────────

test("plans are the three paid plans the access rule knows, per_show picks 2 shows", () => {
  const plans = helper("order_plans");
  assert.deepStrictEqual(Object.keys(plans), ["per_show", "audio_simple", "all_shows"]);
  assert.strictEqual(plans.per_show.shows, 2);
  assert.strictEqual(plans.audio_simple.shows, 0);
  assert.strictEqual(plans.all_shows.shows, 0);
  const rule = read("helpers/access_helper.php");
  for (const key of Object.keys(plans)) assert.match(rule, new RegExp(`'${key}'`), key);
});

test("valid 2-show order is accepted with the picked shows' names", () => {
  const r = helper("order_input", { plan: "per_show", shows: ["audio:a1", "image:i1"], start_date: "2026-12-01", comment: "  Bursdag  " }, SHOWS, TODAY);
  assert.deepStrictEqual(r.errors, []);
  assert.strictEqual(r.order.plan, "per_show");
  assert.deepStrictEqual(JSON.parse(r.order.shows), [{ value: "audio:a1", name: "AudioQuiz - Sang" }, { value: "image:i1", name: "BildeQuiz - Dyr" }]);
  assert.strictEqual(r.order.start_date, "2026-12-01");
  assert.strictEqual(r.order.comment, "Bursdag");
  assert.strictEqual(r.order.status, "new");
});

test("2-show plan refuses one show, the same show twice, or a show not in the catalog", () => {
  for (const shows of [["audio:a1"], ["audio:a1", "audio:a1"], ["audio:a1", "audio:fake"], ["audio:a1", "image:i1", "imagemusic:m1"], undefined]) {
    const r = helper("order_input", { plan: "per_show", shows }, SHOWS, TODAY);
    assert.strictEqual(r.order, null, JSON.stringify(shows));
    assert.deepStrictEqual(r.errors, ["Velg 2 forskjellige forestillinger."]);
  }
});

test("annual plans ignore any posted shows", () => {
  for (const plan of ["audio_simple", "all_shows"]) {
    const r = helper("order_input", { plan, shows: ["audio:a1"] }, SHOWS, TODAY);
    assert.deepStrictEqual(r.errors, []);
    assert.strictEqual(r.order.shows, null);
    assert.strictEqual(r.order.start_date, null);
  }
});

test("unknown or missing plan is refused", () => {
  for (const plan of [undefined, "", "full", "test", ["all_shows"]]) {
    assert.deepStrictEqual(helper("order_input", { plan }, SHOWS, TODAY).errors, ["Velg hva du vil bestille."], JSON.stringify(plan));
  }
});

test("start date must be a real date, today or later", () => {
  const err = (d) => helper("order_input", { plan: "all_shows", start_date: d }, SHOWS, TODAY).errors;
  assert.deepStrictEqual(err("2026-02-30"), ["Ugyldig startdato."]);
  assert.deepStrictEqual(err("14.11.2026"), ["Ugyldig startdato."]);
  assert.deepStrictEqual(err("2026-11-13"), ["Startdatoen kan ikke være før i dag."]);
  assert.deepStrictEqual(err(TODAY), []);
  assert.deepStrictEqual(err(""), []);
});

test("comment is capped at 1000 characters (multi-byte safe)", () => {
  const r = helper("order_input", { plan: "all_shows", comment: "ø".repeat(1500) }, SHOWS, TODAY);
  assert.strictEqual([...r.order.comment].length, 1000);
});

test("e-mail summary escapes what the customer typed", () => {
  const html = helper("order_summary_html", { ...customer, institute: "<b>X</b>" },
    { plan: "per_show", shows: JSON.stringify([{ value: "audio:a1", name: "A & B" }]), start_date: "2026-12-01", comment: "<script>x</script>" });
  assert.match(html, /&lt;b&gt;X&lt;\/b&gt;/);
  assert.match(html, /A &amp; B/);
  assert.match(html, /&lt;script&gt;/);
  assert.doesNotMatch(html, /<script>/);
  assert.match(html, /Ønsket startdato:<\/strong> 01\.12\.2026/);
  assert.match(html, /2 forestillinger, 3 dager/);
});

// ── Order_model (saving + e-mails) ────────────────────────────────────────

test("placing an order saves one row for that customer and e-mails the active admins and the customer", () => {
  const out = harness("place", { tables: { admin_emails: admins, orders: [] }, customer, catalog, post: { plan: "per_show", shows: ["audio:a1", "image:i1"] } });
  assert.deepStrictEqual(out.result, { ok: true, errors: [] });
  assert.strictEqual(out.inserts.length, 1);
  assert.strictEqual(out.inserts[0].table, "orders");
  assert.strictEqual(out.inserts[0].data.users_id, 7);
  assert.strictEqual(out.inserts[0].data.status, "new");
  assert.match(out.inserts[0].data.created_at, /^\d{4}-\d\d-\d\d \d\d:\d\d:\d\d$/);
  assert.deepStrictEqual(out.sent.map((m) => [m.to, m.subject]), [[["admin@bestefar.no"], "NY BESTILLING"], ["kari@example.no", "Bekreftelse på bestilling"]]);
  assert.match(out.sent[0].message, /AudioQuiz - Sang, BildeQuiz - Dyr/);
});

test("crosswords (TekstQuiz) are not offered: only the three access-controlled types", () => {
  const out = harness("place", { tables: { orders: [] }, customer, catalog, post: { plan: "per_show", shows: ["audio:a1", "text:t1"] } });
  assert.deepStrictEqual(Object.keys(out.choices), ["audio:a1", "image:i1"]);
  assert.strictEqual(out.result.ok, false);
});

test("an invalid order saves nothing and sends nothing", () => {
  const out = harness("place", { tables: { admin_emails: admins, orders: [] }, customer, catalog, post: { plan: "per_show", shows: ["audio:a1"] } });
  assert.strictEqual(out.result.ok, false);
  assert.deepStrictEqual(out.inserts, []);
  assert.deepStrictEqual(out.sent, []);
});

test("no active admin address: only the customer's copy is sent", () => {
  const out = harness("place", { tables: { admin_emails: [admins[1]], orders: [] }, customer, catalog, post: { plan: "all_shows" } });
  assert.deepStrictEqual(out.sent.map((m) => m.subject), ["Bekreftelse på bestilling"]);
});

test("current customer comes from the session; nobody logged in = null", () => {
  const users = [customer, { ...customer, users_id: 8, email: "other@example.no" }];
  assert.strictEqual(harness("customer", { tables: { users }, session: { users_id: 8 } }).customer.email, "other@example.no");
  assert.strictEqual(harness("customer", { tables: { users }, session: {} }).customer, null);
});

test("admin list shows only that customer's orders, newest first; no table yet = empty", () => {
  const orders = [
    { orders_id: 1, users_id: 7, created_at: "2026-11-10 10:00:00" },
    { orders_id: 2, users_id: 8, created_at: "2026-11-12 10:00:00" },
    { orders_id: 3, users_id: 7, created_at: "2026-11-13 10:00:00" },
  ];
  assert.deepStrictEqual(harness("for_customer", { tables: { orders }, users_id: 7 }).orders.map((o) => o.orders_id), [3, 1]);
  assert.deepStrictEqual(harness("for_customer", { tables: {}, users_id: 7 }).orders, []);
});

// ── Shared mailer / registration e-mail fix ──────────────────────────────

test("SMTP settings live in config/email.php, which CodeIgniter applies on every load", () => {
  assert.ok(exists("config/email.php"), "config/email.php missing");
  const cfg = execFileSync("php", ["-r", `define('BASEPATH','t'); require ${JSON.stringify(path.join(app, "config", "email.php"))}; echo json_encode($config);`], { encoding: "utf8" });
  const c = JSON.parse(cfg);
  assert.strictEqual(c.protocol, "smtp");
  assert.strictEqual(c.smtp_host, "mailout.one.com");
  assert.strictEqual(c.smtp_port, 587);
  assert.strictEqual(c.smtp_user, "support@bestefar.no");
  assert.ok(typeof c.smtp_pass === "string");
  assert.strictEqual(c.mailtype, "html");
  assert.strictEqual(c.charset, "utf-8");
});

test("SMTP password is not in git: read from the gitignored email_secret.php", () => {
  const cfg = read("config/email.php");
  assert.match(cfg, /\$config\['smtp_pass'\]\s*=\s*'';/);
  assert.match(cfg, /include __DIR__ \. '\/email_secret\.php';/);
  assert.match(read("config/email_secret.example.php"), /\$config\['smtp_pass'\] = '';/);
  assert.match(fs.readFileSync(path.join(repo, ".gitignore"), "utf8"), /^httpd\.www\/application\/config\/email_secret\.php$/m);
  const tracked = execFileSync("git", ["ls-files", "httpd.www/application"], { cwd: repo, encoding: "utf8" });
  assert.doesNotMatch(tracked, /email_secret\.php\n/, "email_secret.php must not be committed");
  assert.doesNotMatch(read("models/Admin_model.php"), /smtp_pass/);
});

test("registration no longer loads the e-mail library twice (second load with the SMTP settings was skipped)", () => {
  const model = read("models/Admin_model.php");
  const body = model.slice(model.indexOf("function add_new_user()"), model.indexOf("function register_user()"));
  assert.doesNotMatch(body, /load->library\('email'/);
  assert.doesNotMatch(body, /smtp_pass/);
  assert.match(body, /send_site_email\(\$this->input->post\('email'\), 'Bekreftelse på registrering', \$message\)/);
  assert.match(body, /send_site_email\(\$user_emails, 'NY REGISTRERING', \$email_body\)/);
});

test("a failed send is logged, not shown as an error page (the customer is already saved)", () => {
  const out = harness("mail", { mail_fails: true, to: "k@example.no", subject: "Test", html: "x" });
  assert.strictEqual(out.exception, undefined);
  assert.strictEqual(out.result, false);
  assert.match(out.logged.join("\n"), /error: E-mail "Test" not sent: smtp refused/);
  const model = read("models/Admin_model.php");
  const body = model.slice(model.indexOf("function add_new_user()"), model.indexOf("function register_user()"));
  assert.doesNotMatch(body, /show_error/);
});

test("mailer sends from support@bestefar.no and returns true on success", () => {
  const out = harness("mail", { to: ["a@x.no", "b@x.no"], subject: "S", html: "<b>h</b>" });
  assert.strictEqual(out.result, true);
  assert.deepStrictEqual(out.sent, [{ from: "support@bestefar.no", to: ["a@x.no", "b@x.no"], subject: "S", message: "<b>h</b>" }]);
});

test("REGRESSION: registration e-mail texts and admin lookup unchanged", () => {
  const model = read("models/Admin_model.php");
  assert.match(model, /takk for registreringen din\./);
  assert.match(model, /Ny bruker er registrert på Bestefar\.no/);
  assert.match(model, /logo_3000-300x101\.png/);
  assert.match(model, /'admin_emails'[\s\S]{0,80}["']status["'][^)]{0,6}["']1["']/, "admin addresses still read from active admin_emails rows");
});

// ── Page, menu, admin, SQL ────────────────────────────────────────────────

test("route requires a logged-in customer and redirects after saving (no resubmit on reload)", () => {
  const home = read("controllers/Home.php");
  const body = home.slice(home.indexOf("public function bestilling("), home.indexOf("public function blogs("));
  assert.match(body, /if\(!\$customer\)\{\s*redirect\('login', 'refresh'\);/);
  assert.match(body, /redirect\('home\/bestilling\/sendt'\)/);
  assert.match(body, /table_ready\(\)/);
  assert.doesNotMatch(body, /Access_model->enabled/, "must not depend on the access switch");
});

test("form matches the client's sketch: read-only details, Detaljer options, Bestill button", () => {
  const view = read("views/frontend/bestilling.php");
  for (const label of ["Institusjon/forening:", "Kontaktperson:", "Telefon(er):", "Epostadresse:"]) assert.ok(view.includes(label), label);
  for (const ph of ["Fornavn", "Etternavn", "Institusjon/Forening", "Privat"]) assert.ok(view.includes(`placeholder="${ph}"`), ph);
  for (const f of ["institute", "fname", "lname", "phone", "mobile", "email"]) assert.match(view, new RegExp(`\\$customer\\['${f}'\\]\\) \\?>" [^>]*readonly`), f);
  assert.match(view, /<h3><b>Bestilling<\/b><\/h3>/);
  assert.match(view, /<h3><b>Detaljer<\/b><\/h3>/);
  assert.match(view, /<button type="submit" class="order-btn">Bestill<\/button>/);
  assert.match(view, /name="plan"/);
  assert.match(view, /name="shows\[\]"/);
  assert.doesNotMatch(view, /<\?= \$customer\[/, "customer values must be escaped");
});

test("menu: Bestilling only for logged-in customers, in both desktop and mobile blocks", () => {
  const nav = read("views/frontend/theme/navigation.php");
  const blocks = nav.match(/<\?php if\(isset\(\$_SESSION\['users_id'\]\)\)\{ \?>[\s\S]*?<\?php \} \?>/g) || [];
  assert.strictEqual(blocks.length, 2);
  for (const b of blocks) {
    const [loggedIn, loggedOut] = b.split("<?php }else{ ?>");
    assert.match(loggedIn, /home\/bestilling">Bestilling<\/a>\|<a href="<\?= base_url\(\); \?>login\/logout_user">Logg ut<\/a>/);
    assert.doesNotMatch(loggedOut, /bestilling/);
    assert.match(loggedOut, /login">Åpne<\/a>\|.*login\/signup">Registrere<\/a>/, "REGRESSION: logged-out links unchanged");
  }
});

test("admin customer window lists orders only once the orders table exists", () => {
  const details = read("views/admin/modals/user_details.php");
  assert.match(details, /if \(\$CI->Order_model->table_ready\(\)\) \{\s*\$this->load->view\('admin\/modals\/customer_orders'/);
  assert.match(read("views/admin/modals/customer_orders.php"), /htmlspecialchars/);
});

test("SQL creates the orders table with every column the model writes; down script drops only it", () => {
  const sql = (f) => fs.readFileSync(path.join(repo, "database", "migrations", f), "utf8").replace(/--.*$/gm, "");
  const up = sql("2026-11-14_orders_up.sql");
  const down = sql("2026-11-14_orders_down.sql");
  assert.match(up, /CREATE TABLE IF NOT EXISTS `orders`/);
  for (const col of ["orders_id", "users_id", "plan", "shows", "start_date", "comment", "status", "created_at"]) assert.match(up, new RegExp("`" + col + "`"), col);
  assert.doesNotMatch(up, /ALTER TABLE|DROP|DELETE|UPDATE/i);
  assert.match(down, /DROP TABLE IF EXISTS `orders`;/);
  assert.doesNotMatch(down.replace(/DROP TABLE IF EXISTS `orders`;/, ""), /DROP|DELETE|ALTER/i);
});
