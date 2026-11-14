// `node --test tests/*.test.js` - catalog icon column: the info "i" and the
// lock / PDF icon must sit at the same place in every row (client, 14 Nov 2026).
// The cell is right-aligned, so the part after the info icon must be the same
// width in every row: lock slot ("Låst" or "Utgått") = PDF + gap + Start.
const test = require("node:test");
const assert = require("node:assert");
const fs = require("fs");
const path = require("path");

const root = process.env.PLAYERS_ROOT || path.join(__dirname, "..", "httpd.www");
const css = fs.readFileSync(path.join(root, "assets", "frontend", "css", "sporreleker.css"), "utf8");

// Declarations of the first rule whose selector list is exactly `selector`.
function rule(selector) {
  const esc = selector.replace(/[.*+?^${}()|[\]\\]/g, "\\$&");
  const m = css.match(new RegExp("(?:^|[}\\n])\\s*" + esc + "\\s*\\{([^}]*)\\}"));
  assert.ok(m, `rule ${selector} not found`);
  return m[1];
}
function px(selector, prop) {
  const m = rule(selector).match(new RegExp("(?:^|[;\\s{])" + prop + "\\s*:\\s*(\\d+)px"));
  assert.ok(m, `${selector} has no ${prop} in px`);
  return Number(m[1]);
}

test("lock slot is as wide as PDF + gap + Start, so info icons line up in open and locked rows", () => {
  const open = px(".quiz-pdf", "width") + px(".quiz-icons", "gap") + px(".quiz-start", "width");
  assert.strictEqual(px(".quiz-lock", "width"), open);
});

test("lock symbol takes the same width as the PDF icon, so lock and PDF icons line up", () => {
  const m = rule(".quiz-lock svg").match(/flex\s*:\s*0\s+0\s+(\d+)px/);
  assert.ok(m, ".quiz-lock svg must have a fixed flex width");
  assert.strictEqual(Number(m[1]), px(".quiz-pdf", "width"));
});

test("label still fits: widest label ('Utgått', ~25px at 13px) + symbol + gap stays inside the slot", () => {
  const used = px(".quiz-pdf", "width") + px(".quiz-lock", "gap") + 25;
  assert.ok(used <= px(".quiz-lock", "width"), `${used}px > slot`);
  assert.match(rule(".quiz-lock-text"), /white-space\s*:\s*nowrap/);
});

test("regression: icon column, right alignment and mobile label hiding unchanged", () => {
  assert.strictEqual(px(".quiz-icons-col", "width"), 120);
  assert.match(css, /td\.quiz-info-cell\{\s*text-align:right;\s*padding-right:20px;/);
  assert.match(css, /@media \(max-width: 767px\) \{ \.quiz-lock-text \{ display: none; \} \}/);
  assert.match(rule(".quiz-lock"), /color:\s*#555/);
});
