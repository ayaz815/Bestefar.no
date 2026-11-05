# Access control plan ("Allow to open…")

Client brief received 26 Oct 2026. Goal: quizzes are exclusive. Only approved, registered customers can open them, and every registered customer gets some quizzes.

Status key: `[ ]` not started, `[~]` in progress, `[x]` done (date + commit hash).

## Rules for every module
- Own feature branch, never straight to `main`. PR after tests pass.
- Unit tests written and passing before the PR (`node --test tests/*.test.js`).
- No edits to customer rows in `bestefar_no` without per-action permission. Back up the DB first.
- Ship behind an on/off switch in `system_setting` where customers could notice.
- List the files to upload manually to one.com, and a rollback note.
- Add a line to `CHANGELOG.md` when a module is shipped.

## Customer types (from the brief)
| Type | Who | Access |
|---|---|---|
| A. Test | Test users | A few shows, free |
| B. Full | Friends, developers | Everything, free |
| C1. Paid, per show | Birthday parties | 2 shows, 3 days only |
| C2. Paid, AudioQuiz + simple | Annual | AudioQuiz and simple Q&A sheets |
| C3. Paid, all shows | Annual | Everything |

Plus: admin "Allow to open…" per show type, and one for "demo view" (shows tagged as demo).

## Modules

### [~] Module 0. Security first (started 2 Nov 2026) - code done, waiting on backup, upload and PR
- [ ] Back up the live `bestefar_no` database (must be done by the user in one.com phpMyAdmin BEFORE uploading)
- [x] Hash passwords with `password_hash`, converting each customer on their next login (no resets) (2 Nov, `bedfe09`)
- [x] Stop `register_user()` overwriting the password of an existing email (2 Nov, `bedfe09`)
- [x] Tests: old and converted passwords both log in; registering an existing email changes nothing (`tests/password-hashing.test.js`, 12 tests)
- [x] CHANGELOG
- [ ] PR opened (branch `feature/password-hashing`), files uploaded, live login checked
- [ ] Follow-ups: admin table passwords still plain text (column is varchar(55), needs widening); SMTP password is hard-coded in `Admin_model.php`

### [~] Module 1. Data model (additive, old columns stay) - SQL and backend done 4 Nov, generator switch left
- [x] `users`: `client_type`, `plan`, `payment_registered`, `access_start`, `access_end` (4 Nov)
- [x] `users`: `allow_audio`, `allow_image`, `allow_imagemusic`, `allow_demo` (4 Nov)
- [x] New table `user_show_grants` (user, quiz type, quiz id, days_valid, first_opened_at, expires_at) (4 Nov)
- [x] SQL up + down scripts in `database/migrations/`, tested on a scratch DB (`tests/access-data-model.test.js`)
- [ ] Run the up script on live (user, after backup)
- [x] Mongo: one `accessTier` field (restricted / registered / demo) on audio, image, image-music + `GET/PUT /api/access-tier/:type/:id` (4 Nov, backend branch `feature/access-tier`, 12 tests)
- [ ] Generator switch "Demo show" / "Open to all registered" (frontend, not started)
- [ ] PR

### [x] Module 2. One rule in one place: `can_open(user, quiz)` (done 4 Nov, branch `feature/access-can-open`)
- [x] `application/helpers/access_helper.php`: `access_can_open()` plus `access_grant_expiry()`. Pure functions, no database.
- [x] Rules: registered-tier shows open to any logged-in customer (approved or not); demo needs `allow_demo`; full opens everything; paid needs payment registered and dates in range; audio+simple = AudioQuiz only; per-show = picked shows, 3 days counted from first open; test and old customers = ticked types or picked shows; unapproved or inactive denied.
- [x] 33-case table test + switch-off regression + first-open and 3-day tests (`tests/access-can-open.test.js`)
- [x] Switch: `application/config/access.php`, `access_control_enabled = FALSE` (changed from `system_setting`, which is a single row of Stripe keys, not key/value)
- [ ] Reading grants from MySQL and saving `first_opened_at` / `expires_at` on first open: built in Module 3 where the rule is first used
- Assumptions to confirm with the client: an unapproved customer can still open "open to all registered" shows; old customers with no type behave like test users with no ticks; a paid plan with no end date never expires.

### [~] Module 3. Catalog (built and tested 4 Nov 2026, branch `feature/access-catalog`) - waiting on backend deploy + upload
Everything below only takes effect when `access_control_enabled` is TRUE. With it FALSE the catalog stays exactly as today.
- [x] Backend: the three show lists include each show's `accessTier` (backend commit `04eb6f4`, 3 tests). **Not deployed yet** (deploy was blocked by the permission check; needs your OK)
- [x] `models/Access_model.php`: loads the logged-in customer and their grants, calls `access_can_open()`, saves `first_opened_at` / `expires_at` on a grant's first open (never restarts a running clock)
- [x] `/home/sporreleker` requires login (redirect to the login page)
- [x] Each row: Start + PDF when allowed; lock icon + "Låst" when not (full message as tooltip and in the phone popup)
- [x] Expired: lock + "Utgått"; banner "Ditt abonnement har utgått. Vil du fornye det?"
- [x] Start goes through `home/open_quiz/<type>/<id>`: checks again, starts the 3-day clock, opens the player; refused -> back to the catalog with `?access=<reason>` banner
- [x] "Open to all registered" and demo tags are used per row (from the backend lists)
- [x] Crossword (TekstQuiz) rows left exactly as today (postponed)
- [x] Tests: `tests/access-catalog.test.js` (17 tests: switch-off catalog matches main's HTML, open_quiz unchanged when off, row locking, messages, open_quiz, first-open saving, other customer's grant ignored). Fail on main's code (5 of 16 at the time), pass on the branch. Whole suite 65/65.
- [x] Browser check (local copy of the database `bestefar_m3test` + 2 local test customers, switch on): logged out -> login page; test customer with AudioQuiz tick -> AudioQuiz open, other types locked; Start -> AudioQuiz player; image show refused -> banner; expired customer -> all rows "Utgått" + banner; phone width 388px popup shows the expired message and hides Start/Skriv ut; switch off + logged out -> catalog as today.
- [ ] Not verified: real iPhone/Safari; live one.com server; demo/registered tags in the browser (needs the backend deploy)
- [ ] Upload, then switch stays OFF until Module 4 and the customer mapping are done
- Found: on the local server old session messages ("Wellcome ...") never clear. Pre-existing, not fixed (not in the plan); the catalog banner avoids session messages for that reason.

### [~] Module 4. Real enforcement (play keys) - investigated 5 Nov 2026, waiting for a scope decision
Decided: a key lasts 12 hours after pressing Start.

Findings (checked against the live API, read-only):
1. The ImageQuiz and ImageMusic players read shows through the same endpoints the generator uses to edit them (`image-quiz/get-image-quiz`, `image-music/get-image-music-show`). The generator has no login, so requiring a key there would break the generator.
2. The generator also reads AudioQuiz shows through `forms/get-show`, which is open to anyone with an id.
3. The public show lists (`forms/get-all` etc.) return every show's full content, including answers and the music/audio file links.
4. Music, audio and image files are on S3 and open directly by link (no login).

So keys on the player endpoints alone would NOT make shows exclusive. Proposed split:
- [x] 4a. Player keys (built and tested 5 Nov; website branch `feature/access-play-keys`, backend branch `feature/play-keys` commit `2726e2b`; nothing deployed or uploaded)
  - Website: `open_quiz` adds a 12-hour signed key to the player link; PDF buttons go through `open_quiz?pdf=1` -> backend `/api/play/<type>/<id>/pdf`. No key while the switch is off.
  - Secret: `application/config/access_secret.php` (gitignored, example file committed) must match `ACCESS_TOKEN_SECRET` in the backend `.env`.
  - Backend: `/api/play/<type>/<id>` and `/pdf` for players and PDFs; those plus `audio-quiz/get-audio-quiz` and the three old `/pdf/:id` routes need a key once `ACCESS_ENFORCE=true`. Off by default.
  - Players (AudioQuiz, ImageQuiz index + dynamicQuiz, ImageMusic) read from `/api/play/...` and send the key; it is kept per tab so later screens load too. AudioQuiz script version bumped to `v=20261105`.
  - Branch merged `feature/audio-single-page` first (the live AudioQuiz is that code), so these player files build on what is live.
  - Tests: backend 26/26 (key check, switch, which routes are guarded, generator routes untouched); website 145/145 (keys in links, PHP key accepted by the backend check, player key handling; new player tests fail on the old files 14/14).
  - Browser (local test server with fake API): AudioQuiz title screen and the first question screen both load the show with the key.
  - Not verified: real backend with enforcement on; ImageQuiz/ImageMusic players in a browser; iPhone/Safari.
  - Known gap until 4c: with enforcement on, the generator Preview for ImageQuiz/ImageMusic has no key. Enforcement stays off until 4c.
  - Deploy order when the time comes: backend first (new `/api/play` routes), then upload the players, then the website files, then switches on.
- [x] 4b. Public lists trimmed (built and tested 5 Nov; backend branch `feature/trim-public-lists` commit `5fa1afd`, website branch `feature/access-play-keys`; nothing deployed or uploaded)
  - Backend: with `ACCESS_ENFORCE=true` the four public lists (`forms/get-all`, `image-quiz/get-all-image-quizzes`, `image-music/get-all-image-music-shows`, `audio-quiz/get-all-audio-quizzes`) send only catalog fields: id, name, type, info, published, tag, question count, Music Type, dates. No questions, answers or file links. Switch off = unchanged.
  - Why behind the switch: the generator's archive reads full content from these same lists. It moves to its own logged-in list in 4c.
  - Website: catalog reads both shapes (`Admin_model::catalog_question_count`, `catalog_music_type`), shared by all three list readers.
  - Tests: backend 35/35 (no answers/links leak with the switch on, full content with it off; new tests fail 5/9 on old code); website 149/149 (new tests fail 4/4 on old code).
  - Checked: local catalog against the live API shows the same question counts and Music Types as bestefar.no today.
  - Not verified: the trimmed lists against the real backend (not deployed).
- [x] 4c. Generator login (built and tested 5 Nov; backend branch `feature/generator-login` commit `520b036`; generator changes NOT committed, see note; nothing deployed)
  - Decision: a separate generator login stored in the backend `.env` on EC2 (`ADMIN_USERNAME`, `ADMIN_PASSWORD_HASH` made with `node scripts/hash-admin-password.js`). The backend can't reach the website's MySQL (one.com only allows local connections), so it can't use the website admin login.
  - Backend: `POST /api/admin/login` (7-day session, 5 wrong tries = 15 min lock), `GET /api/admin/preview-key/...`. With `ACCESS_ENFORCE=true` every create/edit/delete/upload/full-read route needs the login (29 routes, listed in `test/admin-login.test.js`); the catalog/player routes stay without login; public lists stay trimmed unless logged in.
  - Generator: login box opens only when the backend answers 401 (so nothing changes while the switch is off); all calls send the login; uploads too; Share link gets a 12-hour key. Previews use the browser's local storage and need no key.
  - Tests: backend 48/48 (new route tests fail 3/13 on old code); generator 4/4; generator production build OK.
  - Browser: generator against a local stand-in backend: login box appears, wrong password shows an error, right one closes it, requests then carry the login and the show list loads.
  - Note: the generator folder's git remote points at the backend repo and it holds a lot of older uncommitted work, so these generator changes are left uncommitted (logged in the generator's CHANGELOG). They go live by building and uploading the generator.
- [x] 4d. Private media (code built and tested 5 Nov; backend branch `feature/private-media`; not deployed)
  - With `ACCESS_ENFORCE=true`, every link to `musicFiles/`, `audioFiles/`, `imageFiles/` in the S3 bucket goes out as a 12-hour signed link (players, generator reads, full lists). Saving strips the signature, so shows never store an expiring link.
  - Players checked: video detection looks for `.mp4` etc. anywhere in the link, so signed links still work.
  - PDFs only use logos stored on the server, not affected.
  - Tests: backend 57/57 (real AWS signing with dummy keys, no network; wiring tests fail 2/9 on old code).
  - [ ] Last step, by hand in AWS, only after the switch is on and players are checked: remove public read for those three folders in the bucket policy. Needs the current bucket policy to write the exact change (an explicit Deny would also block signed links, so it must be done by narrowing the public Allow).
- Each part ships behind a backend switch (`ACCESS_ENFORCE`, off by default) and is deployed + tested separately.

### [~] Module 5. Admin Customers + registration form (admin part done 5 Nov 2026, branch `feature/access-admin-customers`)
- [x] Customer details window: new "Customer access" section (`views/admin/modals/customer_access.php`): client type, plan, payment registered, access from/to, "Allow to open" ticks (AudioQuiz, BildeQuiz, BilderMusikk, Demo), picked shows with time left, add/remove picked shows. Saved by the existing Update button (`Admin::users('update_access')` -> `Access_model::save_customer_access`).
- [x] Only appears once the Module 1 SQL has been run (`Access_model::columns_ready()`), so nothing breaks before that.
- [x] Customer list: "Access" column ("Paid · All shows", "Paid · Per show · not paid", "... · Utgått", "Not set").
- [x] Input is cleaned before saving (unknown values become empty); saving only touches that customer and that customer's picked shows.
- [x] Tests: `tests/access-admin-customers.test.js` (13; fail 12/12 on old code). Whole suite 161/161.
- [x] Checked on the local copy of the database: admin list shows the Access column; details window shows the section with 13 shows to pick; a save changed only that customer; the customer then saw only the picked show open; opening it started the 3-day clock (72 h).
- Found and fixed while checking: the details window crashed (a model can't be loaded through `$this` inside a CodeIgniter view). Unit tests could not see this; the browser check did.
- [ ] Not checked visually (screenshot) and not on one.com.
- [ ] Registration form redesign: NOT built. The plan says the client's version comes first and we have not received it.
- [ ] Moving the existing customers to a type: done by you in this new admin section, one customer at a time (no automatic bulk change).

### [-] Module 6. (PARKED 4 Nov 2026: user chose not to set up the test site; code stays on branch `feature/staging-site`, not merged) Online test site `staging.bestefar.no` (asked by the user 4 Nov 2026)
Same website and look, its own address and its own copy of the database, access control switched ON. Live customers and the live database are not touched.
- [x] Site picks its settings by address: on `staging.*` it runs as `testing` (`index.php`), elsewhere exactly as today.
- [x] `config/testing/`: own `base_url`, switch ON, own database (`database.php` kept out of git, example file committed).
- [x] Player links follow the site address (`base_url()`), so the test site opens its own players. Live output stays identical.
- [x] Upload package with the test `.htaccess` (pictures under `uploads/` are fetched from the live site instead of copying 1 GB).
- [ ] You: create subdomain `staging` and a second database on one.com, import a copy of the live database, run the Module 1 SQL on it, upload the package.
- [x] Tests: `tests/staging-site.test.js` (7; 4 fail on old code, the 2 regression ones pass). Whole suite 168/168. Checked on the local server with host `staging.bestefar.no`: login required, all links on staging, stops with a message when `database.php` is missing. Not checkable locally: the uploads rule (needs Apache) and one.com itself.
- [x] Package: `~/Per Olav Projects/_staging_site_2026-11-04/` (`staging.bestefar.no/` + `HOW_TO.md`), built from committed files only.
- Limits: shares the live backend (switched off there), so play keys, generator login and private media are not enforced on the test site. Emails sent from the test site (registration) are real emails.

## Deployment status (5 Nov 2026)
- All code for Modules 0-5 is built and tested (registration form excepted, waiting for the client).
- Upload package: `~/Per Olav Projects/_upload_2026-11-05_access_control/` with `DEPLOY.md` (order, checks, rollback, how to switch on).
- [x] Backend deploy to EC2: `feature/private-media` (`29ebc01`) live, pm2 restarted 4 Nov 2026. Checked: play, PDF, lists, access-tier, old audio route all 200; switches off (plain media links, no login asked).
- [ ] Website upload (24 files): AFTER the backend deploy (new players need the new backend).
- [ ] Generator upload: see warning in `DEPLOY.md` step 4.
- [ ] Switches on: `DEPLOY.md` step 6, after existing customers are given a type.

## Client answers (2 Nov 2026)
- Crosswords are postponed until the generator is fully working. No crossword access control for now.
- The 3 days start at the customer's first open, but only if payment is registered. Needs: a "payment registered" flag per customer/grant, and `access_end` set on first open (first-open time + 3 days). Affects Modules 1 and 2.
- Remaining answers promised later the same day.

- 3 Nov 2026, remaining answers:
  - Expired access message (Norwegian): "Ditt abonnement har utgått. Vil du fornye det?" (Module 3, lock screen).
  - Payment: registered by hand in the admin for now. Client wants an "in-between" payment provider soon (customers pay online, provider keeps a share, about 10%). Keep payment status behind one admin action so a provider can be plugged in later. Provider choice is a later module.
  - Some shows must be open to every registered customer (even unapproved test users). Needs a per-show "open to all registered" tag next to the demo tag (Module 1, Mongo flag + generator switch; Module 2 rule).
  - Existing customers (very few): client asks for a suggestion; the new system rules. Proposal to send, list them in admin and decide per person (no automatic bulk change; permission per row).
  - Share links: client unsure what this means and says change everything as needed. Decision: links only work for allowed, logged-in customers. Generator Preview for the author stays working (Module 4).

## Still open
1. Confirm the three tick types are AudioQuiz, ImageQuiz and ImageMusic, and where the simple paper quiz fits.
2. Approve the proposal for mapping the existing customers.

## Log
- 2 Nov 2026: plan written, Module 0 started.
- 2 Nov 2026: client answered Q1 (crossword postponed) and Q2 (3 days start at first open, payment must be registered). Q3 to Q7 pending.
- 3 Nov 2026: client answered Q3 to Q7. Next: reply with the mapping proposal, then Module 1 (waits only on tick types).
- 4 Nov 2026: Module 1 SQL half done on branch `feature/access-data-model`.
- 4 Nov 2026: Module 2 done (rule + tests, switch off).
- 4 Nov 2026: Module 3 started; sub-items written down before coding.
- 4 Nov 2026: Module 3 built and tested (65/65, browser checked at phone width). Waiting on backend deploy OK and upload.

- 5 Nov 2026: Module 4 investigated; key length 12 h agreed; found generator shares player endpoints, lists leak full content, media is public. Waiting for scope decision before coding.
- 5 Nov 2026: client chose the full Module 4 (4a-4d). 4a done and tested, not deployed.
- 5 Nov 2026: 4b done and tested, not deployed. Next: 4c generator login.
- 5 Nov 2026: 4c done and tested, not deployed. Next: 4d private media.
- 5 Nov 2026: 4d code done and tested, not deployed. Next: Module 5.
- 5 Nov 2026: Module 5 admin part done and tested. Registration form waits for the client's version.
- 5 Nov 2026: all modules built and tested; deploy package prepared; backend deploy blocked by the permission check, waiting for the user.
- 4 Nov 2026: backend `feature/private-media` deployed to EC2 and checked live (switches off). Next: website upload.
- 4 Nov 2026: Module 6 test site built and tested (branch `feature/staging-site`); waiting for subdomain + test database on one.com.
- 4 Nov 2026: Module 6 parked at the user's request; testing stays on the Mac (localhost:8888).
- 5 Nov 2026: asked Per Olav for the layout of the admin customer page and the registration form. He needs more time (lecturing until Friday) and wants to design it step by step. Waiting; switches stay off.
