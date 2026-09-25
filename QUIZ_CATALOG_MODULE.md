# Spørreleker (Quiz Catalog) module — reference

This module was built, then reverted at the user's request (kept as a reference in
case it's wanted again later, or partially reused). This file exists so nothing
about it is lost. The code itself still lives in git history — see the branch/commit
list at the bottom to bring it back.

## What it was

A public page (`home/sporreleker`) listing every quiz on the site in one flat table —
Dato / Kategorier / Navn / Ant spørsmål — sortable by clicking a column, filterable by
category through a popup menu, with an (i) info icon per row. Modeled on two mockups:
`Catalog_Quiz.pdf` and `Data.pdf` (both in the project root).

A matching admin screen (`admin/quiz_info`) let an admin type a short info blurb for
each quiz, shown in that (i) popup.

## Where the real data actually comes from

This was the key discovery of the whole module: **the real quiz content does not live
in this MySQL database.** It lives on a separate backend:

```
https://api.bestefar.no/api
```

That's a Node/Express + MongoDB API, built and deployed independently (see
`/Users/ayazmahmood/Per Olav Projects/backend` and `/Users/ayazmahmood/Per Olav
Projects/frontend` — the actual React source for the quiz-generator apps, with its
own `SESSION_HANDOFF.md` describing bugs fixed there directly).

The catalog called these endpoints live, on every page load, and normalised each into
one common shape:

| Mockup category | Real endpoint | Notes |
|---|---|---|
| AudioQuiz | `GET /api/forms/get-all` | **No date field at all** on this API. Worked around by recording "first time our app saw this quiz" in a local table (`quiz_first_seen`) and reusing that stable timestamp. |
| BildeQuiz | `GET /api/image-quiz/get-all-image-quizzes` | Has `createdAt`, `numberOfScreens`. |
| BilderMusikk | `GET /api/image-music/get-all-image-music-shows` | Has `createdAt`, `screens[]`. |
| TekstQuiz | `GET /api/crossword/all` | **Stand-in, not a real match** — there is no real "text quiz" (quotes/Sitater) source anywhere. Crossword was used as the closest available "text" category, per direction to keep going rather than block on it. This was flagged as a real gap, not a clean fit. |

## Files touched (all on `main`, after merging branch `feature/quiz-catalog`)

- `httpd.www/application/controllers/Home.php` — added `sporreleker()` action
- `httpd.www/application/controllers/Admin.php` — added `quiz_info()` action
- `httpd.www/application/models/Admin_model.php` — added: `fetch_json()`,
  `get_or_record_first_seen()`, `fetch_audio_quizzes()`, `fetch_image_quizzes()`,
  `fetch_image_music_quizzes()`, `fetch_text_quizzes()`, `get_quizzes_catalog()`,
  `get_quiz_info()`, `get_quiz_info_map()`, `save_quiz_info()`
- `httpd.www/application/views/frontend/sporreleker.php` — the public catalog page (new file)
- `httpd.www/application/views/frontend/theme/navigation.php` — added a "Spørreleker" nav link
- `httpd.www/application/views/admin/quiz_info.php` — the admin info-editing screen (new file)
- `httpd.www/application/views/admin/theme/sidebar.php` — added the "Quiz Info" admin menu item
- `httpd.www/assets/font/AGENCYR.TTF`, `AGENCYB.TTF` — real Agency FB font files, copied
  from `frontend/public/` (the generator app's actual font), used because the site's
  own declared body font (`agency_fbbold`) turned out to have no matching `@font-face`
  anywhere — a pre-existing, unrelated dead reference discovered while working on this.

## Database tables added (all now unused if this module is reverted)

- `database/001_create_quizzes_table.sql` — an early local `quizzes` table, seeded with
  4 mock rows from the PDF mockup to prove the page worked, **before** discovering the
  real data lives on `api.bestefar.no`. Superseded by the live-API approach; never
  actually used by the final version of the catalog page.
- `database/002_create_quiz_first_seen_table.sql` — `quiz_first_seen(quiz_type,
  external_id, first_seen)` — the AudioQuiz date workaround described above.
- `database/003_create_quiz_info_table.sql` — `quiz_info(quiz_type, external_id, info,
  updated_at)` — backing store for the admin-editable (i) popup text.

These tables are harmless to leave in place (nothing reads them once the code is
reverted) or can be dropped manually if desired — dropping them was not done
automatically since that's a real data-loss action outside git's reach.

## Known open issues / unresolved when this was reverted

- TekstQuiz had no real source (see above) — a genuine product decision was needed
  (ask the client), never fully resolved.
- The AudioQuiz "date" is an approximation (first-seen-by-this-app), not a true
  upload date, because the source API has no such field.
- The (i) info text had no real source anywhere (not in the generator apps, not in the
  API) — the admin screen (`admin/quiz_info`) was the only way to add it, one quiz at a
  time, manually.
- The nav link was a plain item bolted onto the existing multi-dropdown nav, not a
  rebuild into the mockup's simpler 3-button layout ("Litt om oss / Spørreleker / Litt
  å lese på") — that rebuild was discussed but never done.
- Every page load hit the live `api.bestefar.no` API directly, with no caching — fine
  for the traffic at the time, but worth adding caching if this comes back and traffic
  grows.

## How to bring it back

The full feature is preserved in git history:

- Branch: `feature/quiz-catalog` (all the original work, before the font/color/design
  iteration that happened after merging)
- Merge commit on `main`: `4a992ca` ("Merge branch 'feature/quiz-catalog'")
- Follow-up polish commits on `main` after the merge: `2d68f5f` (Agency FB font naming)
  — the base_url and `.env`-removal commits around the same time are **unrelated bug
  fixes**, keep those regardless.

To restore: `git revert` the specific removal commit that undid this module (see git
log for its message), or cherry-pick the file set listed above from `4a992ca` onto
whatever `main` looks like at that point.
