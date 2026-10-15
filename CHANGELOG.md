# Changelog

Short, newest-first log of what's actually shipped in this repo, so a new session doesn't have to re-derive it from `git log`. **Nothing here auto-deploys** — every entry needs manual upload via the one.com filemanager to go live; this log tracks what's committed, not what's live. Always confirm with the user which of these have actually been uploaded before assuming something is live.

## Open questions for the client

Hold these for one combined email once the current batch of fixes is done - don't send piecemeal.

- **Music Type Norwegian labels (raised 14 Oct 2026):** the mobile popup now shows "Musikk" for Music-type quizzes (confirmed from the mockup). "Saying" and "Sound" are guessed as "Talemåte" and "Lyd" (`Admin_model.php`'s `$firm_naming_labels`) - please confirm or correct these.

## Built, verified, then deferred - not currently active

Implemented, tested working end-to-end, then reverted on the user's instruction (15 Oct 2026) - "save this somewhere, revert for now, implement later when told." Not deleted: each is a normal git revert, so re-enabling is just reverting the revert commit (same pattern already used once for Start Quiz earlier in this project). Ask the user before re-applying.

- **Print → full AudioQuiz PDF.** `GET /api/forms/pdf/:id` on the **backend** (commit `0c5b68d`, reverted `<fill in after revert>`) generates the same 3-page PDF (title page, participant answer sheet, answer key) the generator's own "Export PDF" button already produces - ported from `frontend/src/pdf/*.jsx` to plain `React.createElement` (no JSX transpiler on the backend) at `backend/pdf/*.js`, fonts/logos copied into `backend/pdf/fonts/` and `backend/pdf/assets/`. Served `Content-Disposition: inline` so the browser's own PDF viewer handles printing - which already searches for nearby AirPrint/Mopria printers and offers a copy-count field, satisfying the client's request with zero custom printer-discovery code. Verified against two real quizzes (a full 16-question quiz and a partially-filled one), both produced valid PDFs.
- **Start Quiz (mobile popup + desktop table icon).** Reuses the AudioQuiz player's existing `?show=<id>` loading mechanism (`https://bestefar.no/bestefar-audio/index.html?show=<id>`) - the same trick the generator's own "Preview" button already relies on, so no new endpoint was needed. Verified end-to-end against a real quiz ID via curl before wiring the UI.
- Both were AudioQuiz-only; other quiz types (BildeQuiz, TekstQuiz, BilderMusikk) were left as placeholders ("Kommer snart") in both cases.

## Changelog
- **2026-10-14** — Mobile quiz-info popup redesigned to match the client's mockup: centered yellow card, "Utgitt: ..." meta line, two-line info area, Skriv ut (green) / Start Quiz (red) buttons.
- **2026-10-14** — Fixed the "Ant spørsmål" count showing wrong numbers for AudioQuiz rows (root cause: a MongoDB write bug on the backend stored `screens` as an object instead of an array - full writeup in the `backend` repo's changelog). Existing broken documents were migrated after a full DB backup.
- **2026-10-14** — Fixed generator-entered Info being silently blanked by a leftover empty row in the local `quiz_info` admin-override table - an admin override now only wins when it actually has text.
- **2026-10-14** — Removed the "Musician" field per the client's correction (duplicate of the existing "Artist Name"). Artist Name is now shown inside the quiz player itself (revealed with the answer, so it doesn't spoil the song) instead of being a separate catalog field.
- **2026-10-12** — Fixed "Åpne"/"Registrere" wrapping onto two lines: the shared `.dropdown-content a` rule had `display:block`, which always puts each link on its own line regardless of device. Changed to `display:inline-block`. (`4796149`)
- **2026-10-12** — Reverted two attempts at repositioning the mobile login dropdown (tried shrinking the key icon, then opening the menu inline beside it) back to the shared default behavior — client wanted it to behave identically to laptop, not a mobile-specific layout. (`66eb7bf`, `774371f`, `c4df18a` — net effect is `66eb7bf`)
- **2026-10-12** — Reverted the Dato column's font back to the catalog's regular font (`agency_fbregular`); a previous monospace (`Courier New`) override for digit alignment was undone per client feedback, keeping `tabular-nums` for alignment. (`b478a68`)
- **2026-10-12** — Pulled real Info/Musician text from the backend API into the catalog's (i) popup for AudioQuiz rows, instead of always showing blank. (`52b1551`)
- **2026-10-12** — Fixed a CSS ordering bug that silently hid the "AudioQuiz" title above the mobile table — a `display:none` rule was declared after the mobile media query that turned it back on, so equal-specificity cascade order was winning regardless of screen size. (`2bc3310`)
- **2026-10-12** — Mobile table spacing/margin iteration (several small adjustments converging on current values) and quiz info popup changed from click-to-open to hover-to-open. (`378c181`, `83fb3d4`, `4a2d239`, and others same day)
- **2026-10-12** — Replaced the SVG PDF icon approximation with the client's actual `Symbol.png`. (`392ef7a`)

Older history: `git log --oneline` for anything before this.
