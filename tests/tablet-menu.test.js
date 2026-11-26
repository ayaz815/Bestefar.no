// `node --test tests/*.test.js` - the menu button (≡) on iPad / phones.
// Client 26 Nov 2026 (iPad screenshot): "After using it once, it doesn't work anymore".
// Two switches decided whether the menu showed: our .menu-open on the <nav> (display)
// and the theme's .hfe-active-menu on the button (visibility, frontend.css). Each tap
// on ≡ flipped both; touching the key (on iPad a tap is also a hover) turned off only
// the theme's one. From then on every tap turned one on and the other off. Reproduced
// on the local site at 820 px: after touching the key, taps 4 and 6 never opened it.
// PLAYERS_ROOT can point at an older copy of httpd.www to prove the tests catch the old files.
const test = require("node:test");
const assert = require("node:assert");
const fs = require("fs");
const path = require("path");

const root = process.env.PLAYERS_ROOT || path.join(__dirname, "..", "httpd.www");
const read = (p) => fs.readFileSync(path.join(root, "application", "views", "frontend", p), "utf8");
const menuScript = () => {
  const m = read("theme/script.php").match(/<script>\s*\$\(document\)\.ready\(([\s\S]*?)<\/script>/);
  assert.ok(m, "menu script in theme/script.php");
  return "$(document).ready(" + m[1];
};
const tabletCss = () => {
  const head = read("theme/head.php");
  const at = head.indexOf("/* Mobile/Tablet nav: hide the dropdown by default");
  assert.ok(at > 0, "tablet menu block in theme/head.php");
  return head.slice(at, head.indexOf("/* Mobile (<768px)", at));
};

// The page with a tiny jQuery: class lists for the <nav>, the ≡ button and the key link.
function page() {
  const el = { nav: new Set(), toggle: new Set(), key: new Set(), navbar: new Set() };
  const pick = (sel) => (sel.startsWith("nav.hfe-nav-menu__layout-horizontal") ? ["nav"] : sel === ".hfe-nav-menu__toggle" ? ["toggle"] : sel === "#nav_bar" ? ["navbar"] : sel.startsWith(".dropdown") ? ["key"] : []);
  let keyHover = null;
  const $ = (arg) => {
    if (typeof arg !== "string") return { ready: (fn) => fn() };
    const names = pick(arg);
    const api = {
      removeClass: (c) => { for (const n of names) for (const k of c.split(/\s+/)) el[n].delete(k); return api; },
      toggleClass: (c) => { for (const n of names) el[n].has(c) ? el[n].delete(c) : el[n].add(c); return api; },
      hover: (fn) => { if (names[0] === "key") keyHover = fn; return api; },
      css: () => api,
    };
    return api;
  };
  const window = { matchMedia: () => ({ matches: true }) };
  const fns = new Function("$", "window", "document", menuScript() + "; return { give_margin, hide_dropdown };")($, window, {});
  // What the theme's own click handler does on ≡ (frontend.js _toggleClick).
  const themeTap = () => {
    el.toggle.has("hfe-active-menu") ? el.toggle.delete("hfe-active-menu") : el.toggle.add("hfe-active-menu");
    el.nav.has("menu-is-active") ? el.nav.delete("menu-is-active") : el.nav.add("menu-is-active");
  };
  return {
    el,
    tap: (themeBound = true) => { fns.give_margin(); if (themeBound) themeTap(); }, // onclick on the icon, then the click reaches the theme's button
    touchKey: () => { assert.ok(keyHover, "key has a hover handler"); keyHover(); },
    clickKey: () => fns.hide_dropdown(),
    // Shown = what the CSS decides: with the fix .menu-open alone; before, both were needed.
    shown: () => el.nav.has("menu-open") && (menuOpenWins() || el.toggle.has("hfe-active-menu")),
  };
}
function menuOpenWins() {
  const css = tabletCss();
  const nav = css.match(/nav\.hfe-nav-menu__layout-horizontal\.menu-open\s*\{([^}]*)\}/);
  const ul = css.match(/nav\.hfe-nav-menu__layout-horizontal\.menu-open ul\.hfe-nav-menu\s*\{([^}]*)\}/);
  return !!(nav && ul && /visibility:\s*visible !important/.test(nav[1]) && /opacity:\s*1 !important/.test(nav[1]) && /height:\s*auto !important/.test(nav[1])
    && /visibility:\s*visible !important/.test(ul[1]) && /opacity:\s*1 !important/.test(ul[1]));
}

test("the open menu is shown by .menu-open alone, over the theme's hidden rules (iPad and phone sizes)", () => {
  assert.ok(menuOpenWins(), ".menu-open sets display, visibility, opacity, height (+ the list) with !important");
  assert.match(tabletCss(), /^\/\* Mobile\/Tablet nav[^\n]*\n@media \(max-width: 1024px\) \{/, "only where the ≡ button is used");
  assert.match(tabletCss(), /nav\.hfe-nav-menu__layout-horizontal \{\s*display: none !important;/, "regression: closed by default");
});

test("iPad: open, touch the key, then ≡ opens and closes the menu again every time", () => {
  const p = page();
  const seen = [];
  p.tap(); seen.push(p.shown());
  p.tap(); seen.push(p.shown());
  p.tap(); seen.push(p.shown());
  p.touchKey(); seen.push(p.shown());
  for (let i = 0; i < 4; i++) { p.tap(); seen.push(p.shown()); }
  assert.deepStrictEqual(seen, [true, false, true, false, true, false, true, false]);
});

test("touching or tapping the key closes the menu completely (ours and the theme's classes)", () => {
  for (const how of ["touchKey", "clickKey"]) {
    const p = page();
    p.tap();
    p[how]();
    assert.deepStrictEqual([...p.el.nav], [], `${how}: nav has no open classes`);
    assert.ok(!p.el.toggle.has("hfe-active-menu") && !p.el.toggle.has("hfe-active-menu-full-width"), `${how}: theme button reset`);
    p.tap();
    assert.ok(p.shown(), `${how}: next tap opens it`);
  }
});

test("iPad turned after the page loaded (theme handler not attached): ≡ still opens and closes", () => {
  const p = page();
  const seen = [];
  for (let i = 0; i < 4; i++) { p.tap(false); seen.push(p.shown()); }
  assert.deepStrictEqual(seen, [true, false, true, false]);
});

test("regression: ≡ still toggles .menu-open, and the key's hover handler is on the same links", () => {
  const src = read("theme/script.php");
  assert.match(src, /function give_margin\(\)\{\s*var mobileNav = \$\('nav\.hfe-nav-menu__layout-horizontal'\);\s*mobileNav\.toggleClass\('menu-open'\);/);
  assert.match(src, /\$\("\.dropdown \.elementor-widget-container a"\)\.hover\(/);
  for (const f of ["theme/navigation.php", "home.php"]) {
    assert.match(read(f), /<div class="hfe-nav-menu-icon" onclick="give_margin\(\)">/, `${f}: ≡ calls give_margin`);
    assert.match(read(f), /class="dropdown [^"]*" [^>]*onclick="hide_dropdown\(\);"/, `${f}: key calls hide_dropdown`);
  }
});
