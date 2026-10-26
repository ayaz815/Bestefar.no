# Bestefar ecosystem — read this first

This repo (`bestefar.no_website_files_2026-08-22`) is one of **three independent projects** that together make up Per Olav's Bestefar.no / quiz-generator system, all living as sibling folders under `~/Per Olav Projects/`:

| Folder | What it is | Repo | Deploys to |
|---|---|---|---|
| `frontend` | React/Vite "Bestefar HTML Generator" — internal tool where quizzes (AudioQuiz, ImageQuiz, ImageMusic, Crossword) are authored | `ayaz815/bestefar-backend.git` ⚠️ misconfigured, points at the backend repo | manual (`npm run build` + manual upload) |
| `backend` | Node/Express + MongoDB API the generator reads/writes quizzes through, and that this website calls to read the AudioQuiz catalog | `ayaz815/bestefar-backend.git` | AWS EC2 (pm2 process `bestefar-backend`), SSH alias `bestefar-ec2` (`ubuntu@13.51.231.77`, key `~/Downloads/bestefarKey.pem`) |
| `bestefar.no_website_files_2026-08-22` (**this repo**) | The public bestefar.no website | `ayaz815/Bestefar.no.git` | **manual upload only**, see below |

Each project is opened and worked on **independently** (its own VS Code window / its own session) — don't try to treat them as one monorepo or open them together.

## This repo: the bestefar.no website

PHP / CodeIgniter 3, served from shared hosting (one.com). Key paths:

- `httpd.www/application/controllers/Home.php` — most frontend routes live here, including `sporreleker()` (the AudioQuiz/quiz catalog page), reachable at `/home/sporreleker`.
- `httpd.www/application/views/frontend/sporreleker.php` + `assets/frontend/css/sporreleker.css` + `assets/frontend/js/sporreleker.js` — the quiz catalog table/UI.
- `httpd.www/application/views/frontend/theme/head.php` / `navigation.php` — shared header, including the login/register ("Åpne | Registrere") key-icon dropdown. Desktop and mobile each have their own near-duplicate markup block (search for `elementor-hidden-mobile` vs `elementor-hidden-desktop`) — a fix often needs to be checked/applied in both, or made universal in the shared base rule instead of a device-specific override (that's what caused repeated back-and-forth on the mobile menu bug — prefer fixing the shared rule over adding another device-scoped patch).
- `httpd.www/application/models/Admin_model.php` — `get_quizzes_catalog()`-type methods that call the backend's `/api/forms/get-all` and shape the AudioQuiz rows the catalog table renders (name, date, info, question count).
- `httpd.www/bestefar-audio/` — the actual live AudioQuiz player (the generator's own "Preview" and the catalog's "Start Quiz"/PDF both point here). **⚠️ This folder has drifted from git before** (discovered 16 Oct 2026: production's `data/script.js` and `data/screens/show.html` had real fixes - an iOS audio-element cap fix, a double-Audio-instance bug fix - that were never committed here, so this repo's copies were stale by hundreds of lines). Before editing anything under this folder, diff the live file against the repo copy first (`curl -s https://bestefar.no/bestefar-audio/<path> | diff - httpd.www/bestefar-audio/<path>`) and sync from live if they differ, so a fix doesn't get silently built against a stale baseline and never actually apply once uploaded.
- MySQL database (`bestefar_no`) credentials are in `httpd.www/application/config/database.php` — `hostname => localhost` means **relative to wherever this code is actually running** (the one.com server in production), not this machine. There's a local-only copy importable from `~/Per Olav Projects/bestefar.no_database_bestefar_no_live-2026-08-22T092654Z.sql` if the user wants to test fully locally (MySQL running locally, imported from that dump) — this is separate from the backend's MongoDB, don't conflate the two databases.

### Deployment — manual, no CI

**Nothing in this repo auto-deploys.** A commit/push here does not update the live site. After committing a fix, always tell the user exactly which file(s) changed and that they need to upload them via the one.com filemanager — don't imply or let the user assume a change is "live" just because it's committed.

### Local testing

A local PHP dev server can be run from `httpd.www/`:
```
php -S localhost:8888
```
This needs a local MySQL running with the `bestefar_no` database imported (see dump path above), and matching credentials in `database.php`. To avoid ever touching the tracked `config.php` (which hardcodes `base_url` to `https://bestefar.no/`), use a CodeIgniter environment override instead: `httpd.www/application/config/development/config.php` (gitignored — create it if missing) with just `$config['base_url'] = 'http://localhost:8888/';`. `ENVIRONMENT` defaults to `development` already (see `httpd.www/index.php`), so this gets picked up automatically.

## Workflow rules (strict)

1. **Never push straight to `main`.** Create a feature branch, commit there, and open a PR for review — even for small fixes. Only push directly if the user explicitly says to skip the PR. (Current working branch at time of writing: `feature/quiz-catalog-v2`.)
2. **Never delete, drop, or bulk-edit rows in the `bestefar_no` MySQL database without the user's explicit, per-action permission.** This applies locally too, not just production.
3. **Always tell the user which files need manual upload** after any change here — never say a fix is "done"/"live" without that caveat.
4. **Log what you ship.** After a feature/fix is committed, add a line to `CHANGELOG.md` in this repo (create it if missing) so a future session knows what's already been done without re-deriving it from `git log`. Keep entries short: date, one line, commit hash.
5. **Always unit test before opening a pull request.** Every change needs tests for the behaviour it touches, written and passing *before* the PR is opened, and the PR description must say what was tested. Tests live in `tests/` and run with `node --test tests/*.test.js` (Node's built-in runner, no install needed; note `node --test tests/` without the glob fails). Rules: (a) add a test for each new/changed feature or fix; (b) prove a bug-fix test actually catches the bug by running it against the pre-fix files (`PLAYERS_ROOT=<dir with original files> node --test ...`) - it must fail there and pass on the fix; (c) include regression tests for behaviour that must NOT change; (d) run the whole suite, not just the new file, and fix any failure before opening the PR; (e) also check the change in a real browser (phone width for anything player/catalog related) and say what could not be verified (e.g. real Safari/iPhone).
6. **Keep client reference material out of the repo.** WhatsApp images, comment/correction PDFs, screenshots and mockups go in `~/Per Olav Projects/_bestefar_reference_files/` (outside this repo), never in the repo root. Move them there rather than deleting, unless the user says to delete.
