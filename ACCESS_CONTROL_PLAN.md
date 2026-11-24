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
- [x] Generator switch per show (24 Nov 2026, frontend project, not committed there: the touched files already had other uncommitted changes and its remote points at the backend repo): Archived Shows cards for AudioQuiz/ImageQuiz/ImageMusic get "Tilgang" Låst / Åpen for alle registrerte / Demo, reads `accessTier` from the lists, saves via `PUT /api/access-tier` with the generator login; failed save keeps the old value. 14 tests (generator 18/18), lint clean, build `index-N149_Mcr.js` / `index-2ZlXNrVM.css`; checked in the browser (4/7/5 cards, Crossword none, label fits). No save sent to the live API.
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
- [x] Registration form: client's revised layout received 7 Nov 2026 and built (branch `feature/registration-form`): "Alle feltene må fylles ut" heading, new placeholders, new required Ja/Nei question "Vi har forsterker og høyttalere" (`users.amplifier_speakers`, shown in the admin details window), projector now required too. SQL: `database/migrations/2026-11-07_registration_amplifier_up.sql`, run BEFORE uploading the model. Tests: `tests/registration-form.test.js` (9; 7 fail on old code). Local browser check at 320-1200px and a real local submit passed. Not checked on one.com or a real phone.
- [ ] Admin customer page layout: still waiting for the client.
- [ ] Moving the existing customers to a type: done by you in this new admin section, one customer at a time (no automatic bulk change).

### [~] Module 7. Order form "Bestilling" (client sketch `Bestilling.pdf`, 8 Nov 2026; built and tested 14 Nov 2026, branch `feature/order-form`) - client wording + prices in (16 Nov); waiting on SQL and upload
Client's logic: 1) everyone registers first, 2) a logged-in customer gets a new "Bestilling" menu item, 3) the order form confirms their details and lists the options.
- [x] `home/bestilling`: login required (always, not tied to the access switch). Not logged in -> login page.
- [x] Top part "Bestilling": Institusjon/forening, Kontaktperson (Fornavn, Etternavn), Telefon(er) (Institusjon/Forening, Privat), Epostadresse, filled in from the customer's registration and read-only (the form "confirms" them).
- [x] "Detaljer": the three paid plans already used by the access rule: `per_show` (2 shows, 3 days from first open: customer picks 2 shows from the catalog), `audio_simple` (AudioQuiz + simple question sheets, 1 year), `all_shows` (all shows, 1 year). Optional wanted start date and a comment. Client wording + prices (16 Nov 2026): "2 quizer i 3 dager (kr 150)", "Audio Quiz og Tekst Quiz i ett år (kr 1.800)", "Alle variantene i ett år (kr 2.900)"; price saved with each order (`orders.price`). All registered customers see the form (free/test too).
- [x] "Bestill" button: input checked on the server (known plan, exactly 2 different catalog shows for `per_show`, valid date), saved in a new `orders` table (status `new`), e-mail to the admin addresses (Admin emails page) and a copy to the customer, then a thank-you message.
- [x] Ordering grants nothing by itself: the admin registers payment and access by hand in "Customer access" (Module 5), as agreed 3 Nov. The customer's orders are listed there so the admin sees what was asked for.
- [x] Menu: "Bestilling" next to "Logg ut" in the key-icon dropdown (desktop and mobile blocks).
- [x] Fix found 6 Nov, needed here: registration e-mails ignore the SMTP settings (the e-mail library is loaded twice, the second load with the settings is skipped). One shared mailer for registration and orders. SMTP password moved out of the code into `config/email_secret.php` (gitignored, upload by hand; this closes the Module 0 follow-up for the SMTP password).
- [x] SQL: `database/migrations/2026-11-14_orders_up.sql` (+ down). [ ] Run on live after a backup, BEFORE uploading.
- [x] Tests: `tests/order-form.test.js` (24, harness `tests/support/order_harness.php`); 22 fail on the old code (the 2 that pass there are a regression check and the SQL file check). Whole suite 197/197.
- [x] Browser check (local copy `bestefar_m3test`, orders SQL run there, local mail sent to a dead host so nothing real goes out): logged out -> login page; logged in -> form with the customer's details read-only and the 13 live shows; same show twice -> "Velg 2 forskjellige forestillinger." with choices kept; 2 different shows -> thank-you page, one row saved; admin customer window lists the order with the comment escaped; 375px width: no sideways scrolling.
- [ ] Not verified: real e-mail delivery from one.com (the app's `log_threshold` is 0, so failed sends are not written to a log file on live either); real iPhone/Safari; one.com server.
- Client answered 16 Nov 2026: wording + prices done; all registered customers see the form.

### [~] Module 8. Remaining bugs (added 23 Nov 2026, user: "all modules finished, tested and deployed")
- [x] `ajax/*` admin routes answer without an admin login (anyone can call them). Now 403 without the admin session; `get_video_images` (public blog page) stays open.
- [x] `home/shows` gives a 500 error (`views/frontend/theme/head.php`). Only without/with an unknown category; now 404. Also found: `shows_f.php` puts the URL numbers into its SQL, now forced to whole numbers in the controller.
- [x] `home/save_cart` is broken. Dead route (called a model method that never existed, nothing links to it): removed, now 404.
- [~] Backend `/download-zip` gives a 500 error (backend repo). The generator uses `/api/zip/download-zip`, which works on live (200, zip). `/download-zip` is an old unused copy: remove it in the backend.
- [x] Admin "Admin Emails" page: view loops over `$users`, controller passes `admin_emails` (PHP warning, empty list). Leftover customer modal removed; list itself was fine.
- [x] Module 0 follow-up: admin passwords still plain text (column too short to hold a hash). SQL `2026-11-23_admin_password_up.sql` widens the column; hash stored at next login only once the column is wide enough (no lock-out if the files go up first); profile form no longer prints the password.

### [~] Release 23 Nov 2026 (branch `release/2026-11-23`)
User decisions 23 Nov: push as is (SMTP password already in main's history; change it in one.com); user uploads to one.com, Claude checks live after each step; access control stays OFF until Per Olav approves the customer mapping.
- [x] Branches pushed: `fix/music-type-stemme`, `feature/registration-form`, `feature/order-form`, `fix/catalog-icon-alignment`, `fix/registration-pointer-cursor`.
- [x] Release branch = every module branch + `fix/admin-logo` (218/218). Not included: `feature/staging-site` (Module 6, parked by the user), `feature/blog-image-remove` (Sep, unpushed, status unknown).
- [x] Module 8 fixed and merged (website `fix/module-8-bugs`; backend `fix/remove-dead-download-zip` pushed, deploy needs SSH approval).
- [x] Scenario tests: every customer type x every quiz type x switch off/on, logged out, admin flows, order, registration (automated + browser, desktop and phone width).
  - [x] Automated (`tests/scenarios.test.js`, branch `test/all-scenarios`): 134 592 combinations (customer type, approved/active, ticks, plan, payment, dates, quiz type, tag, picked shows, switch) checked against the plan's rules; each customer type from the brief through the real catalog + Start; whole flow register -> order -> admin -> open. 31 tests. Mutation check: 8 deliberately broken copies of the rule, every one caught (1-8 failing tests each). Whole suite 268/268.
  - [x] Same scenarios on the local site with the real database: `tests/e2e/local-site.e2e.js` (17/17, run 23 Nov): registers new zz-e2e-* customers through the real form, admin approves and sets access through the real admin routes, customer opens the catalog, presses Start, orders; switch off checked too. Shows and tags from the live backend. Leaves its test customers in `bestefar_m3test`.
  - [x] Browser: desktop catalog for a birthday customer (2 picked shows open, rest locked, icons aligned); phone 375px: catalog, locked popup (message, no Start), open popup (Skriv ut + Start Quiz), order form with prices; no sideways scrolling.
  - **Found, needs a decision before switch-on:** the old `users.status` field is shown in admin as "Paid / Not Paid" and every new registration is saved as "Not Paid" (Inactive). The access rule refuses Inactive customers everything except 'open to all registered' shows, so a free test customer that the admin approves and ticks still sees nothing unless the admin also sets the old field to "Paid" (E2E: 0 of 4 AudioQuiz rows open). Options: (a) the rule ignores the old status (payment is `payment_registered` now), (b) keep it and rename the admin label to "Active / Inactive", (c) keep as is.
  - Found (behaviour as built, to confirm with the client): a paid 'all shows' customer still needs the demo tick to open demo-tagged shows; 'Audio Quiz og Tekst Quiz' opens AudioQuiz through the rule, Tekst Quiz (crosswords) stays open to everyone as today (crossword access control postponed).
- [x] Client video 24 Nov ("Can you get rid of the «jumping»", `_bestefar_reference_files/2026-11-24_PerOlav_jumping.mp4`): the quiz list showed in pieces while loading (page top first, list popping in, rows growing when the Agency font arrived) and the whole page slid down ~50 px when the logo/key pictures arrived. Causes: `sporreleker.css` linked half way down the body (it also changes the page layout), no font preload, and the header picture links were inline-block (0 px until the picture loaded). Fixed on `fix/catalog-no-jumping`: stylesheet + font preload + hide-until-ready guard (max 3 s) in `<head>` for the catalog page only (`views/frontend/index.php`), reveal script after the list (`sporreleker.php`), `.qc-wait` rule (`sporreleker.css`), header links as blocks (`theme/head.php`, all pages; same final layout checked at 8 widths 375-1366 px). 11 tests (8 fail on the old code), whole suite 279/279. Slow-load replay (files delayed 1.5-3 s, cold cache): before = list missing, then popping in, then +13 px (font) and +52 px (header); after = first frame already final, 0 movements. Live page head checked: no drift, only these additions.
- [x] Client 24 Nov (iPhone screenshot of the AudioQuiz player, `_bestefar_reference_files/2026-11-24_PerOlav_jumping_pages.jpeg`: "pls fix the jumping on these page as well"; user: "check jumping on all projects ... do complete testing"). Measured with the slow-load replay (layout-shift recorder, files delayed 1.5-3 s, no cache) at phone 390/428 px and desktop: website home, articles list, article pages, registration, login, show lists, quiz list; AudioQuiz start + question pages; BildeQuiz start + all 16 questions (multiple/single/bit, open + both reveals); BilderMusikk start + show; generator Archived Shows. Found + fixed on `fix/no-jumping-everywhere`: (1) AudioQuiz frame followed the visible screen height (100dvh) so on iPhone every screen was laid out again when the browser bars slid (40-90 px moves, icons switching size at the 700 px breakpoint) -> 100svh in `player.html` + `show.html` (same layout as before at every size, checked); (2) article cover picture had no size: text dropped 282 px on a phone (home page article, CLS 0.31) -> real width/height from the file (`helpers/image_size_helper.php`, `blog_details.php`), now 0. Not fixed (not visible): BildeQuiz/BilderMusikk start-page text shifts happen while the text is still hidden by font-display block. Everything else measured 0. Tests `tests/no-jumping-everywhere.test.js` (8, 6 fail on old code). Not verifiable here: real iPhone (desktop Chrome has no sliding bars).
- [ ] Release PR to `main`.
- [x] Upload package + checklist: `~/Per Olav Projects/_upload_2026-11-23_release/` (`DEPLOY.md`, 53 website files, 4 SQL scripts + undo). Live check 23 Nov: of the 5 Nov package only the PHP files and the AudioQuiz player went up (catalog CSS/JS and the ImageQuiz/BilderMusikk players are still the old ones; live `sporreleker.js` is an older copy, no own changes), so the new package holds every file changed since `main`, checked byte for byte against the release branch.
- [x] Rehearsal on a copy of the live database (`bestefar_rehearsal`, from the 22 Aug dump + the 3 catalog tables created since): the 4 SQL scripts in order, then the site with the switch off: catalog 13 rows no locks, registration (amplifier saved, password hashed), order (kr 1800 saved), 8 admin pages, customer window with Customer access + Orders, ajax 403/200, bad addresses 404. No errors.
- [x] Found and fixed: the Module 1 SQL was never in git (`*.sql` ignore rule); `.gitignore` now keeps `database/migrations/*.sql`.
- [x] Backend deployed to EC2 23 Nov (user asked): `fix/remove-dead-download-zip` (`36ed575`) on top of the live `feature/private-media`; pm2 restarted; lists, play, PDF, access-tier, generator ZIP all 200, old `/download-zip` 404; `ACCESS_ENFORCE` off.
- [x] Generator build 23 Nov: `index-XdUNniSo.js` (same as the 5 Nov build; only change since the live 27 Oct build is the generator login, inactive while enforcement is off). Checked with `vite preview` against the live API. In the package as `generator/` (3 files to upload) with a rollback copy of the live build.
- [~] User uploaded the website 24 Nov. Claude's live check (outside, no logins): 19 of 22 static files byte-identical to the package; all pages 200 with no PHP/DB errors; catalog 13 rows, 0 locks, "jumping" fix in <head>, "Stemme"; registration form new; order page -> login; home/shows/abc + home/save_cart 404; ajax admin actions 403; open_quiz -> player. Still to do: (1) `uploads/admin/blogs/1743244857-Plakat_påske.jpg` is on the server with the Mac's decomposed "å" (NFD) so the NFC name the site asks for is 404 - rename it in the one.com file manager; (2) `uploads/admin/shows/image/1746952289-Klassik-musikk-1.jpg` and `1777704320-Ville dyr.jpg` not uploaded (shows 69/70; Klassik is broken on home/shows/5); (3) generator still the 27 Oct build (optional); (4) not checkable from outside: SQL scripts run, admin pages, e-mails.
- [ ] Later: switch on (after Per Olav), then the AWS bucket step.

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
- 7 Nov 2026: client's registration form layout and high-resolution logo received. Form and logo built and tested on `feature/registration-form`, not live. Client follows up on the other questions.
- 14 Nov 2026: read client e-mails of this week. "Saying" renamed to "Stemme" (`fix/music-type-stemme`). 5 missing images received and staged for upload. Module 7 (order form) added to the plan and started.
- 14 Nov 2026: Module 7 built and tested (197/197, browser checked at 375px). Registration e-mail fix included (shared mailer, `config/email.php`). Next: client confirms option wording and prices; then SQL + upload.
- 14 Nov 2026: full local run checked (198/198 tests, customer + admin pages). Questions e-mailed to Per Olav (reply in "Suggestion for the order form" thread): option wording, prices, free customers see form?, Paralyse.jpg + MI.jpg. Old bug found: admin/admin_emails view uses $users (controller passes admin_emails), PHP warning + empty list.
- 14 Nov 2026: Module 3 fix (client): info and lock icons lined up in every row - lock gets a fixed 56px slot (= PDF + gap + Start). Branch `fix/catalog-icon-alignment`, 4 tests (3 fail on old CSS), 202/202, checked in browser with open + locked + "Utgått" rows.
- 16 Nov 2026: client answered: option wording + prices (done, saved per order), all registered customers order (already so, now tested), MI.jpg said enclosed but not attached, Paralyse.jpg = article 81 "Lider også du av Mobil-paralyse?". 202/202.
- 23 Nov 2026: Module 8 (website part) fixed on `fix/module-8-bugs`: 19 tests (12 fail on old code, 7 regression/file checks pass there), whole suite 237/237. Checked on the local site: ajax 403 logged out / works for admin, shows 404 for bad addresses, admin login before and after the SQL (hash stored, wrong password refused), profile form empty.
- 24 Nov 2026: client's MI.jpg received; saved as `uploads/admin/blogs/1761383946-MI.jpg` (the name article 44 "Litt mer om multiintelligens og samfunnet." uses), shown on `home/omideen` locally, added to the upload package (53 files). Still broken on live: article 81 `1759667534-Paralyse.jpg` (client looking) and article 86 "Start en quiz når som helst og hvor som helst fra mobilen din" `1758368415-IMG_7157.JPEG` (new, ask client).
- 24 Nov 2026: CORRECTION: a wrong admin login does show "Invalid Username and Password." (pop-up from `views/admin/theme/script.php`, which `login.php` includes); it was green because it was stored as msg_success. Now msg_error (red), `Login.php`; test in no-jumping-everywhere.test.js. Local test admin renamed `zz-admin@test.local` (login box is an e-mail field). E2E script fixed: waits 2.5 s after flipping the switch (opcache re-reads files every 2 s); 17/17.
- 24 Nov 2026: progress e-mail sent to Per Olav (reply in his thread "Answers to your questions"): what is built, available tomorrow, both test on live this week, go live next week. Asked him: (1) should the old Paid / Not Paid field still count (suggested: ignore it), (2) OK on moving existing customers to the new types, (3) confirm the three ticks, (4) Paralyse.jpg + IMG_7157.JPEG, (5) where/when the player still jumps on his iPhone. Same text also sent as a new e-mail to per.o.asplund@gmail.com ("Bestefar.no – what's built, testing this week, go-live next week"), user's request.
