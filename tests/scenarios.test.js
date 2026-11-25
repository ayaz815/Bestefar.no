// `node --test tests/*.test.js` - every access scenario (Release 23 Nov 2026, ACCESS_CONTROL_PLAN.md).
// 1. Every combination of customer x show x picked shows x switch, checked against the
//    plan's rules written as properties (they don't copy the code's order of checks).
// 2. Each customer type from the brief, through the real Access_model (catalog rows + Start).
// 3. The whole flow: new registration -> order -> admin sets access -> customer opens.
// APP_ROOT can point at an older copy of httpd.www/application.
const test = require("node:test");
const assert = require("node:assert");
const path = require("path");
const { execFileSync } = require("child_process");

const app = process.env.APP_ROOT || path.join(__dirname, "..", "httpd.www", "application");
const php = (script, action, input) =>
  execFileSync("php", [path.join(__dirname, "support", script), app, action].filter(Boolean),
    { input: JSON.stringify(input), encoding: "utf8", maxBuffer: 64 * 1024 * 1024 });
const access = (action, scenario) => JSON.parse(php("access_harness.php", action, scenario));
const orders = (action, scenario) => JSON.parse(php("order_harness.php", action, scenario));

// ── 1. Every combination ────────────────────────────────────────────────────

const NOW = "2026-11-23 12:00:00";
const TYPES = ["audio", "image", "imagemusic", "crossword"];
const TIERS = ["restricted", "registered", "demo", null];
const TICKS = [{}, { allow_audio: 1 }, { allow_image: 1, allow_imagemusic: 1 }, { allow_demo: 1 }, { allow_audio: 1, allow_image: 1, allow_imagemusic: 1, allow_demo: 1 }];
const DATES = {
  none: {},
  current: { access_start: "2026-01-01 00:00:00", access_end: "2026-12-31 23:59:59" },
  future: { access_start: "2026-12-01 00:00:00", access_end: "2027-11-30 23:59:59" },
  past: { access_start: "2025-11-01 00:00:00", access_end: "2026-11-01 23:59:59" },
};

const users = [null];
for (const client_type of [null, "test", "full", "paid"]) {
  for (const status of ["Active", "Inactive"]) {
    for (const approval_status of [0, 1]) {
      for (const ticks of TICKS) {
        const base = { users_id: 7, status, approval_status, client_type, allow_audio: 0, allow_image: 0, allow_imagemusic: 0, allow_demo: 0, ...ticks };
        if (client_type !== "paid") { users.push({ ...base, plan: null, payment_registered: 0 }); continue; }
        for (const plan of ["per_show", "audio_simple", "all_shows", null]) {
          for (const payment_registered of [0, 1]) {
            for (const [dates, d] of Object.entries(DATES)) users.push({ ...base, plan, payment_registered, ...d, _dates: dates });
          }
        }
      }
    }
  }
}
const quizzes = TYPES.flatMap((type) => TIERS.map((tier) => ({ type, id: "q1", tier })));
const grant = (o) => ({ quiz_type: "audio", quiz_id: "q1", days_valid: null, first_opened_at: null, expires_at: null, ...o });
const GRANT_SETS = {
  none: [],
  fresh: TYPES.map((t) => grant({ quiz_type: t, days_valid: 3 })), // picked, 3 days, not opened yet
  running: TYPES.map((t) => grant({ quiz_type: t, days_valid: 3, first_opened_at: "2026-11-22 10:00:00", expires_at: "2026-11-25 10:00:00" })),
  expired: TYPES.map((t) => grant({ quiz_type: t, days_valid: 3, first_opened_at: "2026-11-01 10:00:00", expires_at: "2026-11-04 10:00:00" })),
  unlimited: TYPES.map((t) => grant({ quiz_type: t })),
  other_show: TYPES.map((t) => grant({ quiz_type: t, quiz_id: "q2" })),
};

const cases = [];
for (const user of users) for (const quiz of quizzes) for (const [g] of Object.entries(GRANT_SETS)) for (const enabled of [true, false]) cases.push({ user, quiz, g, enabled });
const results = php("access_matrix.php", null, { users, quizzes, grant_sets: Object.values(GRANT_SETS), now: NOW }).trim().split("\n")
  .map((line) => ({ allowed: line[0] === "1", reason: line.slice(2) }));
const all = cases.map((c, i) => ({ ...c, ...results[i] }));
const when = (pred) => all.filter(pred);
const show = (c) => JSON.stringify({ user: c.user && { client: c.user.client_type, plan: c.user.plan, pay: c.user.payment_registered, dates: c.user._dates, status: c.user.status, appr: c.user.approval_status, ticks: [c.user.allow_audio, c.user.allow_image, c.user.allow_imagemusic, c.user.allow_demo].join("") }, quiz: c.quiz, grants: c.g, on: c.enabled, got: c.reason });
const expectAll = (list, allowed, why) => {
  assert.ok(list.length > 0, `no cases for: ${why}`);
  const bad = list.filter((c) => c.allowed !== allowed);
  assert.strictEqual(bad.length, 0, `${why}: ${bad.length} of ${list.length} wrong, e.g. ${bad.slice(0, 3).map(show).join("\n")}`);
};
// The old users.status ("Paid / Not Paid") is ignored (client, 25 Nov 2026), only approval counts.
const active = (c) => c.user && c.user.approval_status === 1;
const controlled = (c) => c.quiz.type !== "crossword";
const restricted = (c) => !c.quiz.tier || c.quiz.tier === "restricted";
const on = (c) => c.enabled;

test(`matrix: ${all.length} cases, one answer each`, () => {
  assert.ok(all.length > 100000);
  assert.strictEqual(results.length, cases.length);
});

test("switch OFF: every customer (and nobody logged in) opens every show, as today", () => {
  expectAll(when((c) => !c.enabled), true, "switch off");
  assert.ok(when((c) => !c.enabled).every((c) => c.reason === "switch_off"));
});

test("switch ON, not logged in: nothing opens; the message asks to log in", () => {
  expectAll(when((c) => on(c) && !c.user), false, "logged out");
  assert.ok(when((c) => on(c) && !c.user).every((c) => c.reason === "login_required"));
});

test("switch ON: crosswords (TekstQuiz) are never decided by this rule (postponed by the client)", () => {
  assert.ok(when((c) => on(c) && c.user && !controlled(c)).every((c) => !c.allowed && c.reason === "unknown_type"));
});

test("'Open to all registered' shows open for every logged-in customer: unapproved, inactive, unpaid, expired too", () => {
  expectAll(when((c) => on(c) && c.user && controlled(c) && c.quiz.tier === "registered"), true, "registered tier");
});

test("unapproved customers open nothing else", () => {
  expectAll(when((c) => on(c) && c.user && controlled(c) && c.quiz.tier !== "registered" && !active(c)), false, "unapproved");
});

test("the old 'Paid / Not Paid' field changes nothing: every case gets the same answer with Active and Inactive", () => {
  const key = (c) => JSON.stringify([{ ...c.user, status: undefined }, c.quiz, c.g, c.enabled]);
  const byKey = new Map();
  for (const c of when((c) => c.user)) {
    const k = key(c);
    if (!byKey.has(k)) byKey.set(k, []);
    byKey.get(k).push(c);
  }
  let pairs = 0;
  for (const [, list] of byKey) {
    assert.strictEqual(new Set(list.map((c) => c.user.status)).size, list.length);
    if (list.length === 2) pairs++;
    assert.ok(list.every((c) => c.allowed === list[0].allowed && c.reason === list[0].reason), `status changed the answer: ${list.map(show).join("\n")}`);
  }
  assert.ok(pairs > 50000, `compared ${pairs} Active/Inactive pairs`);
});

test("B. Full customers (approved, active) open every show, demo included, whatever the ticks", () => {
  expectAll(when((c) => on(c) && active(c) && controlled(c) && c.user.client_type === "full"), true, "full");
});

test("demo shows open exactly for customers with the demo tick (full customers always)", () => {
  const demo = when((c) => on(c) && active(c) && controlled(c) && c.quiz.tier === "demo" && c.user.client_type !== "full");
  expectAll(demo.filter((c) => c.user.allow_demo), true, "demo ticked");
  expectAll(demo.filter((c) => !c.user.allow_demo), false, "demo not ticked");
});

const paid = (c) => on(c) && active(c) && controlled(c) && restricted(c) && c.user.client_type === "paid";
test("C. Paid, payment not registered: no restricted show opens (picked shows and ticks don't count)", () => {
  expectAll(when((c) => paid(c) && !c.user.payment_registered), false, "unpaid");
});

test("C. Paid without a plan: nothing restricted opens", () => {
  expectAll(when((c) => paid(c) && c.user.payment_registered && c.user.plan === null), false, "no plan");
});

test("C3. All shows, paid, inside the dates: every restricted show opens", () => {
  const c3 = when((c) => paid(c) && c.user.payment_registered && c.user.plan === "all_shows");
  expectAll(c3.filter((c) => ["none", "current"].includes(c.user._dates)), true, "all_shows in dates");
  expectAll(c3.filter((c) => ["future", "past"].includes(c.user._dates)), false, "all_shows outside dates");
  assert.ok(c3.filter((c) => c.user._dates === "past").every((c) => c.reason === "expired"), "ended -> 'Utgått'");
});

test("C2. AudioQuiz + simple, paid, inside the dates: AudioQuiz only (ImageQuiz/ImageMusic stay locked)", () => {
  const c2 = when((c) => paid(c) && c.user.payment_registered && c.user.plan === "audio_simple" && ["none", "current"].includes(c.user._dates));
  expectAll(c2.filter((c) => c.quiz.type === "audio"), true, "audio_simple audio");
  expectAll(c2.filter((c) => c.quiz.type !== "audio"), false, "audio_simple other types");
  expectAll(when((c) => paid(c) && c.user.payment_registered && c.user.plan === "audio_simple" && c.user._dates === "past"), false, "audio_simple ended");
});

test("C1. 2 shows / 3 days, paid: only the picked shows, from first open until 3 days later; ticks and dates don't matter", () => {
  const c1 = when((c) => paid(c) && c.user.payment_registered && c.user.plan === "per_show");
  expectAll(c1.filter((c) => ["fresh", "running", "unlimited"].includes(c.g)), true, "picked show");
  expectAll(c1.filter((c) => ["none", "other_show", "expired"].includes(c.g)), false, "not picked / 3 days over");
  assert.ok(c1.filter((c) => c.g === "fresh").every((c) => c.reason === "grant_first_open"), "first open starts the clock");
  assert.ok(c1.filter((c) => c.g === "expired").every((c) => c.reason === "expired"), "3 days over -> 'Utgått'");
});

test("A. Test customers and old customers (no type yet): ticked types, or shows picked for them", () => {
  const a = when((c) => on(c) && active(c) && controlled(c) && restricted(c) && (c.user.client_type === "test" || c.user.client_type === null));
  const ticked = (c) => !!c.user["allow_" + c.quiz.type];
  expectAll(a.filter(ticked), true, "ticked type");
  expectAll(a.filter((c) => !ticked(c) && ["fresh", "running", "unlimited"].includes(c.g)), true, "picked show");
  expectAll(a.filter((c) => !ticked(c) && ["none", "other_show", "expired"].includes(c.g)), false, "not ticked, not picked");
});

test("a show picked for ANOTHER show id never opens this one", () => {
  expectAll(when((c) => on(c) && active(c) && controlled(c) && restricted(c) && c.g === "other_show" && c.user.client_type !== "full" && !(c.user.client_type !== "paid" && c.user["allow_" + c.quiz.type]) && !(c.user.client_type === "paid" && c.user.payment_registered && ["all_shows", "audio_simple"].includes(c.user.plan))), false, "other show's grant");
});

test("every refusal has a customer message and a lock label: 'Utgått' only for ended access", () => {
  const reasons = [...new Set(when((c) => !c.allowed).map((c) => c.reason))];
  for (const reason of reasons) {
    const msg = access("fn", { fn: "Access_model::message_for", args: [reason] });
    assert.ok(msg && msg.length > 10, reason);
    assert.strictEqual(access("fn", { fn: "Access_model::short_label_for", args: [reason] }), reason === "expired" ? "Utgått" : "Låst", reason);
  }
  assert.strictEqual(access("fn", { fn: "Access_model::message_for", args: ["expired"] }), "Ditt abonnement har utgått. Vil du fornye det?");
});

// ── 2. Each customer type through the real catalog + Start ──────────────────

const day = 24 * 3600 * 1000;
const at = (offsetDays) => new Date(Date.now() + offsetDays * day).toISOString().slice(0, 19).replace("T", " ");
const ID = { a1: "a1".padEnd(24, "0"), a2: "a2".padEnd(24, "0"), ar: "ab".padEnd(24, "0"), ad: "ad".padEnd(24, "0"), i1: "b1".padEnd(24, "0"), m1: "c1".padEnd(24, "0"), t1: "d1".padEnd(24, "0") };
const CATALOG = [
  { quiz_type: "AudioQuiz", external_id: ID.a1, name: "Audio 1", tier: "restricted" },
  { quiz_type: "AudioQuiz", external_id: ID.a2, name: "Audio 2", tier: null },
  { quiz_type: "AudioQuiz", external_id: ID.ar, name: "Audio for all registered", tier: "registered" },
  { quiz_type: "AudioQuiz", external_id: ID.ad, name: "Audio demo", tier: "demo" },
  { quiz_type: "BildeQuiz", external_id: ID.i1, name: "Bilde 1", tier: "restricted" },
  { quiz_type: "BilderMusikk", external_id: ID.m1, name: "BilderMusikk 1", tier: "restricted" },
  { quiz_type: "TekstQuiz", external_id: ID.t1, name: "Kryssord 1" },
];
const customer = (o) => ({ users_id: 7, status: "Active", approval_status: 1, client_type: null, plan: null, payment_registered: 0, access_start: null, access_end: null, allow_audio: 0, allow_image: 0, allow_imagemusic: 0, allow_demo: 0, ...o });
const pick = (key, o) => ({ grant_id: 50 + Object.keys(ID).indexOf(key), users_id: 7, quiz_type: { a: "audio", b: "image", c: "imagemusic" }[ID[key][0]], quiz_id: ID[key], days_valid: 3, first_opened_at: null, expires_at: null, ...o });
const catalogFor = (user, grants = [], enabled = true) => {
  const rows = access("annotate", { enabled, session: user ? { users_id: user.users_id } : {}, tables: { users: user ? [user] : [], user_show_grants: grants }, quizzes: CATALOG }).quizzes;
  return Object.fromEntries(rows.map((r, i) => [Object.keys(ID)[i], r.access ? (r.access.allowed ? "open" : `${r.access.reason}`) : "as today"]));
};
const LOCKED_EXCEPT = (open, reason) => Object.fromEntries(Object.keys(ID).map((k) => [k, k === "t1" ? "as today" : open.includes(k) ? "open" : typeof reason === "function" ? reason(k) : reason]));

test("journey: switch OFF -> every row as today for everyone (no locks, no access data)", () => {
  for (const user of [null, customer({ approval_status: 0 }), customer({ client_type: "paid", plan: "per_show" })]) {
    assert.deepStrictEqual(catalogFor(user, [], false), Object.fromEntries(Object.keys(ID).map((k) => [k, "as today"])));
  }
});

test("journey: not logged in -> every controlled row asks for login; crossword as today", () => {
  assert.deepStrictEqual(catalogFor(null), LOCKED_EXCEPT([], "login_required"));
});

test("journey: just registered, not approved yet -> only 'open to all registered' shows", () => {
  assert.deepStrictEqual(catalogFor(customer({ approval_status: 0 })), LOCKED_EXCEPT(["ar"], "not_approved"));
});

test("journey: A. test customer with the AudioQuiz tick", () => {
  assert.deepStrictEqual(catalogFor(customer({ client_type: "test", allow_audio: 1 })),
    LOCKED_EXCEPT(["a1", "a2", "ar"], (k) => (k === "ad" ? "demo_not_allowed" : "not_allowed")));
});

test("journey: A. test customer with one picked BildeQuiz and the demo tick", () => {
  assert.deepStrictEqual(catalogFor(customer({ client_type: "test", allow_demo: 1 }), [pick("i1", { days_valid: null })]),
    LOCKED_EXCEPT(["ar", "ad", "i1"], "not_allowed"));
});

test("journey: B. full customer -> everything", () => {
  assert.deepStrictEqual(catalogFor(customer({ client_type: "full" })), LOCKED_EXCEPT(["a1", "a2", "ar", "ad", "i1", "m1"], "-"));
});

test("journey: C1. birthday (2 quizer i 3 dager), paid, 2 shows picked", () => {
  const user = customer({ client_type: "paid", plan: "per_show", payment_registered: 1 });
  assert.deepStrictEqual(catalogFor(user, [pick("a1"), pick("m1")]), LOCKED_EXCEPT(["a1", "ar", "m1"], (k) => (k === "ad" ? "demo_not_allowed" : "not_allowed")));
  // Before payment is registered nothing restricted opens.
  assert.deepStrictEqual(catalogFor({ ...user, payment_registered: 0 }, [pick("a1"), pick("m1")]),
    LOCKED_EXCEPT(["ar"], (k) => (k === "ad" ? "demo_not_allowed" : "payment_missing")));
});

test("journey: C1. first Start begins the 3 days; running clock is kept; after 3 days -> 'Utgått'", () => {
  const user = customer({ client_type: "paid", plan: "per_show", payment_registered: 1 });
  const tables = (g) => ({ users: [user], user_show_grants: [g] });
  const first = access("open", { enabled: true, session: { users_id: 7 }, tables: tables(pick("a1")), type: "audio", id: ID.a1 });
  assert.strictEqual(first.decision.allowed, true);
  assert.match(first.decision.target_url, /bestefar-audio\/index\.html\?screen=1&show=a10+&autostart=1/);
  assert.strictEqual(first.updates.length, 1);
  const saved = first.updates[0].data;
  const hours = (new Date(saved.expires_at.replace(" ", "T")) - new Date(saved.first_opened_at.replace(" ", "T"))) / 3600000;
  assert.strictEqual(hours, 72);
  const again = access("open", { enabled: true, session: { users_id: 7 }, tables: tables(pick("a1", { first_opened_at: at(-1), expires_at: at(2) })), type: "audio", id: ID.a1 });
  assert.strictEqual(again.decision.allowed, true);
  assert.deepStrictEqual(again.updates, [], "a running clock is never restarted");
  const later = access("open", { enabled: true, session: { users_id: 7 }, tables: tables(pick("a1", { first_opened_at: at(-4), expires_at: at(-1) })), type: "audio", id: ID.a1 });
  assert.strictEqual(later.decision.allowed, false);
  assert.strictEqual(later.decision.reason, "expired");
  assert.strictEqual(catalogFor(user, [pick("a1", { first_opened_at: at(-4), expires_at: at(-1) })]).a1, "expired");
});

test("journey: C2. Audio Quiz og Tekst Quiz i ett år, paid -> AudioQuiz open, picture quizzes locked, crosswords as today", () => {
  const user = customer({ client_type: "paid", plan: "audio_simple", payment_registered: 1, access_start: at(-30), access_end: at(335) });
  assert.deepStrictEqual(catalogFor(user), LOCKED_EXCEPT(["a1", "a2", "ar"], (k) => (k === "ad" ? "demo_not_allowed" : "plan_excludes_type")));
});

test("journey: C3. Alle variantene i ett år, paid -> all restricted shows (demo needs its tick)", () => {
  const user = customer({ client_type: "paid", plan: "all_shows", payment_registered: 1, access_start: at(-30), access_end: at(335) });
  assert.deepStrictEqual(catalogFor(user), LOCKED_EXCEPT(["a1", "a2", "ar", "i1", "m1"], "demo_not_allowed"));
  assert.deepStrictEqual(catalogFor({ ...user, allow_demo: 1 }), LOCKED_EXCEPT(["a1", "a2", "ar", "ad", "i1", "m1"], "-"));
});

test("journey: C3 year over -> 'Utgått' on every restricted row; year not started yet -> locked", () => {
  const ended = customer({ client_type: "paid", plan: "all_shows", payment_registered: 1, access_start: at(-400), access_end: at(-35) });
  assert.deepStrictEqual(catalogFor(ended), LOCKED_EXCEPT(["ar"], (k) => (k === "ad" ? "demo_not_allowed" : "expired")));
  const future = customer({ client_type: "paid", plan: "all_shows", payment_registered: 1, access_start: at(5), access_end: at(370) });
  assert.deepStrictEqual(catalogFor(future), LOCKED_EXCEPT(["ar"], (k) => (k === "ad" ? "demo_not_allowed" : "not_started")));
});

test("journey: old field set to 'Not Paid' (Inactive) -> the new settings still decide (client, 25 Nov 2026)", () => {
  assert.deepStrictEqual(catalogFor(customer({ client_type: "full", status: "Inactive" })), LOCKED_EXCEPT(["a1", "a2", "ar", "ad", "i1", "m1"], ""));
  assert.deepStrictEqual(catalogFor(customer({ client_type: "test", allow_audio: 1, status: "Inactive" })), catalogFor(customer({ client_type: "test", allow_audio: 1, status: "Active" })));
  assert.deepStrictEqual(catalogFor(customer({ status: "Inactive", approval_status: 0 })), LOCKED_EXCEPT(["ar"], "not_approved"), "approval still counts");
});

test("journey: Start on a locked show is refused (no player link), and a crossword Start is not handled by open_quiz", () => {
  const user = customer({ client_type: "test", allow_audio: 1 });
  const refused = access("open", { enabled: true, session: { users_id: 7 }, tables: { users: [user], user_show_grants: [] }, type: "image", id: ID.i1 });
  assert.strictEqual(refused.decision.allowed, false);
  assert.strictEqual(refused.decision.target_url, undefined);
  assert.strictEqual(access("open", { enabled: true, session: { users_id: 7 }, tables: { users: [user] }, type: "crossword", id: ID.t1 }).decision.reason, "unknown_type");
});

// ── 3. Whole flow: register -> order -> admin -> open ───────────────────────

test("flow: new customer orders 'Alle variantene', nothing opens until the admin registers payment and access", () => {
  const fresh = customer({ users_id: 9, approval_status: 0, institute: "Sykehjemmet", fname: "Kari", lname: "N", phone: "1", mobile: "2", email: "kari@example.no" });
  const grantsOf = [];
  const view = (u) => {
    const rows = access("annotate", { enabled: true, session: { users_id: 9 }, tables: { users: [u], user_show_grants: grantsOf }, quizzes: CATALOG }).quizzes;
    return rows.filter((r) => r.access).filter((r) => r.access.allowed).map((r) => r.name);
  };
  assert.deepStrictEqual(view(fresh), ["Audio for all registered"], "before approval");

  // The order form works for an unapproved, untyped customer too, and grants nothing.
  const order = orders("place", { tables: { admin_emails: [{ email: "admin@bestefar.no", status: "1" }], orders: [] }, customer: fresh, catalog: [], post: { plan: "all_shows" } });
  assert.deepStrictEqual(order.result, { ok: true, errors: [] });
  assert.deepStrictEqual(order.inserts.map((i) => [i.table, i.data.plan, i.data.price]), [["orders", "all_shows", 2900]]);
  assert.deepStrictEqual(order.updates, [], "ordering changes nothing on the customer");
  assert.deepStrictEqual(view(fresh), ["Audio for all registered"], "still locked after ordering");

  // Admin: approve (users list) + Customer access: paid, all shows, payment registered, one year.
  const saved = access("save_customer", { users_id: 9, fields: { client_type: "paid", plan: "all_shows", payment_registered: "1", access_start: at(0).slice(0, 10), access_end: at(365).slice(0, 10) }, remove: [], add_show: "", add_days: "" });
  assert.strictEqual(saved.ok, true);
  assert.deepStrictEqual(saved.updates.map((u) => [u.table, u.where]), [["users", [{ users_id: 9 }]]], "only this customer's row");
  const after = { ...fresh, approval_status: 1, ...saved.updates[0].data };
  assert.deepStrictEqual(view(after), ["Audio 1", "Audio 2", "Audio for all registered", "Bilde 1", "BilderMusikk 1"]);
});

test("flow: birthday order -> admin picks the 2 shows with 3 days -> customer sees exactly those", () => {
  const user = customer({ users_id: 9, client_type: "paid", plan: "per_show", payment_registered: 1 });
  const added = [ID.a2, ID.i1].map((id, n) => access("save_customer", { users_id: 9, fields: { client_type: "paid", plan: "per_show", payment_registered: "1" }, remove: [], add_show: `${n ? "image" : "audio"}:${id}`, add_days: "3" }));
  const grants = added.map((a, n) => ({ grant_id: 70 + n, first_opened_at: null, expires_at: null, ...a.inserts[0].data }));
  assert.deepStrictEqual(grants.map((g) => [g.quiz_type, g.days_valid]), [["audio", 3], ["image", 3]]);
  const rows = access("annotate", { enabled: true, session: { users_id: 9 }, tables: { users: [user], user_show_grants: grants }, quizzes: CATALOG }).quizzes;
  assert.deepStrictEqual(rows.filter((r) => r.access && r.access.allowed).map((r) => r.name), ["Audio 2", "Audio for all registered", "Bilde 1"]);
});
