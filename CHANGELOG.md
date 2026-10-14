# Changelog

Short, newest-first log of what's actually shipped in this repo, so a new session doesn't have to re-derive it from `git log`. **Nothing here auto-deploys** — every entry needs manual upload via the one.com filemanager to go live; this log tracks what's committed, not what's live. Always confirm with the user which of these have actually been uploaded before assuming something is live.

- **2026-10-12** — Fixed "Åpne"/"Registrere" wrapping onto two lines: the shared `.dropdown-content a` rule had `display:block`, which always puts each link on its own line regardless of device. Changed to `display:inline-block`. (`4796149`)
- **2026-10-12** — Reverted two attempts at repositioning the mobile login dropdown (tried shrinking the key icon, then opening the menu inline beside it) back to the shared default behavior — client wanted it to behave identically to laptop, not a mobile-specific layout. (`66eb7bf`, `774371f`, `c4df18a` — net effect is `66eb7bf`)
- **2026-10-12** — Reverted the Dato column's font back to the catalog's regular font (`agency_fbregular`); a previous monospace (`Courier New`) override for digit alignment was undone per client feedback, keeping `tabular-nums` for alignment. (`b478a68`)
- **2026-10-12** — Pulled real Info/Musician text from the backend API into the catalog's (i) popup for AudioQuiz rows, instead of always showing blank. (`52b1551`)
- **2026-10-12** — Fixed a CSS ordering bug that silently hid the "AudioQuiz" title above the mobile table — a `display:none` rule was declared after the mobile media query that turned it back on, so equal-specificity cascade order was winning regardless of screen size. (`2bc3310`)
- **2026-10-12** — Mobile table spacing/margin iteration (several small adjustments converging on current values) and quiz info popup changed from click-to-open to hover-to-open. (`378c181`, `83fb3d4`, `4a2d239`, and others same day)
- **2026-10-12** — Replaced the SVG PDF icon approximation with the client's actual `Symbol.png`. (`392ef7a`)

Older history: `git log --oneline` for anything before this.
