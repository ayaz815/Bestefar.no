# Spørreleker (Quiz Catalog) module — reference

**Status: live and under active iteration**, on branch `feature/quiz-catalog-v2`. This is
not the reverted module described in earlier versions of this file — it was rebuilt from
scratch and has been through many rounds of real client feedback since.

## What it is

A public page (`home/sporreleker`) listing every quiz on the site in one flat table —
Dato / Kategorier / Navn / Ant spørsmål — sortable by clicking a column, filterable by
category through a popup menu, with an (i) info icon and a (currently inert) PDF download
icon per row. Modeled on `Catalog_Quiz.pdf` / `Data.pdf`, then refined against several
further rounds of client markup (`Comments_01.pdf`, `Suggested Corrections_30_09_26.pdf`,
`Correction_3.pdf`, and direct chat feedback).

Nav: desktop keeps the full "Litt om oss / Spørreleker / Litt å lese på" structure with
all original dropdowns/submenus intact — only "Leke & Lære" was swapped for
"Spørreleker". Mobile is simplified further per the client's explicit request: the
middle item links straight to the AudioQuiz-filtered view (`?type=AudioQuiz`) instead of
the full table, since only AudioQuiz is meant to be used from a phone.

## Where the real data comes from

The quiz content does not live in this MySQL database. It lives on a separate backend:

```
https://api.bestefar.no/api
```

A Node/Express + MongoDB API, built and deployed independently (see
`/Users/ayazmahmood/Per Olav Projects/backend` and `/Users/ayazmahmood/Per Olav
Projects/frontend` — the React source for the quiz-generator apps, with its own
`SESSION_HANDOFF.md`).

The catalog calls these endpoints live, on every page load, and normalises each into one
common shape:

| Mockup category | Real endpoint | Notes |
|---|---|---|
| AudioQuiz | `GET /api/forms/get-all` | **No date field at all** on this API. Worked around by recording "first time our app saw this quiz" in a local table (`quiz_first_seen`) and reusing that stable timestamp. |
| BildeQuiz | `GET /api/image-quiz/get-all-image-quizzes` | Has `createdAt`, `numberOfScreens`. |
| BilderMusikk | `GET /api/image-music/get-all-image-music-shows` | Has `createdAt`, `screens[]`. |
| TekstQuiz | `GET /api/crossword/all` | **Stand-in, not a real match** — there is no real "text quiz" (quotes/Sitater) source anywhere. Crossword was used as the closest available "text" category. Flagged as a real gap, never resolved. |

## Files touched

- `httpd.www/application/controllers/Home.php` — `sporreleker()` action
- `httpd.www/application/models/Admin_model.php` — `fetch_json()`,
  `get_or_record_first_seen()`, `fetch_audio_quizzes()`, `fetch_image_quizzes()`,
  `fetch_image_music_quizzes()`, `fetch_text_quizzes()`, `get_quizzes_catalog()`
  (with a deterministic `strcasecmp` secondary sort so tied rows stop jumping between
  loads — the real fix for the "jumping rows" complaint, not a CSS/font issue)
- `httpd.www/application/views/frontend/sporreleker.php` — the public catalog page
- `httpd.www/application/views/frontend/theme/navigation.php` — "Spørreleker" nav item
  (desktop), AudioQuiz-only item (mobile)
- `httpd.www/assets/frontend/css/sporreleker.css` — all catalog styling, including a
  `max-width:767px` mobile section (side margins, stacked header, horizontally-scrolling
  table with natural column widths instead of the desktop's equal-split
  `table-layout:fixed`)
- `httpd.www/assets/frontend/js/sporreleker.js` — info-popup toggle, filter-menu toggle

An earlier admin screen (`admin/quiz_info`) for editing the (i) popup text, and the
`quiz_info` table backing it, were part of the original build referenced below but are
**not present in the current rebuild** — the (i) popup on the live page currently just
shows a static "Ingen info lagt inn ennå" fallback. Re-adding that editor is open work if
wanted.

## Database tables

- `quiz_first_seen(quiz_type, external_id, first_seen)` — the AudioQuiz date workaround
  described above. In active use.
- `quiz_info(quiz_type, external_id, info, updated_at)` — exists from the earlier build;
  not currently written to or read from without the admin screen above.

## Corrections implemented so far (chronological, this round of work)

- Live-API wiring for all 4 quiz types, stable sort to fix jumping rows
- Font matched to the real site's actual live font (`agency_fbregular`), not a copied one
- Sticky-footer layout fix scoped to just this page
- PDF download icon redesigned to the client's own suggested document-symbol shape
  (explicitly not a copy of Adobe's Acrobat logo — flagged and resolved with the client)
- Date/count columns: tabular-nums, ~14% smaller font per client's side-by-side comparison
- Title/count/filter merged onto one line (desktop) per "All in one line" feedback
- Table aligned to the logo/key row (1140px, zero padding) rather than the narrower
  900px nav-button row — confirmed via pixel-exact browser measurement
- Icon column and Dato column margins equalized at 20px on both ends
- Nav: only "Leke & Lære" swapped for "Spørreleker" (desktop); all other dropdowns/
  submenus preserved — an earlier over-broad flattening of the whole nav was reverted
  after client clarification
- Mobile: AudioQuiz-only nav link, side margins added (previously flush to screen edge),
  header stacked instead of cramped onto one row, table switched to natural-width +
  horizontal scroll instead of crushed equal columns, filter button given real padding
- A "more professional" header/filter visual redesign was tried, then explicitly reverted
  at the client's request ("don't change design, it was fine") — current desktop design
  is intentionally the original, unchanged

## Known open issues

- **TekstQuiz has no real source** — crossword is a stand-in, never resolved; needs a
  product decision from the client.
- **AudioQuiz date is an approximation** (first-seen-by-this-app), not a true upload
  date — the source API has no date field at all.
- **(i) info popup has no content source** — the admin editor for it from the earlier
  build was not carried into this rebuild; every row currently shows the empty-state
  text. Needs either that admin screen rebuilt, or a real source identified.
- **PDF download icon is inert** — styled and in place, not wired to a real file, since
  the generator apps don't produce/store a downloadable PDF anywhere.
- **No caching** — every page load hits `api.bestefar.no` directly. Fine at current
  traffic; worth revisiting if traffic grows.
- Client's latest correction round (mobile margins, table layout, filter button padding)
  has been implemented and uploaded; **awaiting the client's confirmation** that it now
  matches what they want (email sent asking for feedback).

## Earlier, fully-reverted build (historical reference only)

Before this rebuild, an earlier version of this module was built, then **entirely
reverted** (not part of the current code). It's preserved only in git history in case
anything from it is worth reusing:

- Branch: `feature/quiz-catalog` (pre-rebuild original)
- Merge commit on `main`: `4a992ca`
- Follow-up: `2d68f5f` (Agency FB font naming)
- That version also had the admin `quiz_info` editing screen
  (`application/controllers/Admin.php` → `quiz_info()`, `application/views/admin/
  quiz_info.php`, `application/views/admin/theme/sidebar.php` menu item) and shipped its
  own font files (`httpd.www/assets/font/AGENCYR.TTF`, `AGENCYB.TTF`) — the current
  rebuild uses the font already live on the real site instead.

To pull anything from it: `git log` on `main` around `4a992ca` for the exact file set,
then cherry-pick or manually port what's wanted into the current `sporreleker.php`/
`sporreleker.css`.
