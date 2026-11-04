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
- [ ] 4a. Player keys: the website issues a signed 12-hour key in `open_quiz`; new player-only read endpoints check it; the three players use them. Generator endpoints untouched.
- [ ] 4b. Public lists trimmed to what the catalog needs (name, count, info, date, tag); the generator gets its own full list behind 4c.
- [ ] 4c. Generator login: the generator's read/save/delete endpoints require an admin login.
- [ ] 4d. Media files: S3 files private, players get short-lived links through the play endpoint.
- Each part ships behind a backend switch (`ACCESS_ENFORCE`, off by default) and is deployed + tested separately.

### [ ] Module 5. Admin Customers + registration form
- [ ] Customers list and detail: type, plan, "Allow to open…" ticks, dates, grants
- [ ] Registration form redesign (client's version first)
- [ ] Phone-width check, tests, PR

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
