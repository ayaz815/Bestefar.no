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

### [ ] Module 3. Catalog
- [ ] `/home/sporreleker` requires login
- [ ] Rows shown, locked or hidden per `can_open`, clear "no access" message
- [ ] Demo rows stay visible
- [ ] Regression test: current demo behaviour unchanged; phone-width browser check

### [ ] Module 4. Real enforcement (play tokens)
- [ ] Website issues a short-lived signed token (user, quiz, expiry)
- [ ] Backend checks it on quiz-read and PDF endpoints
- [ ] Players pass the token on
- [ ] Decide what happens to Share links and generator Preview
- [ ] Backend token tests, deploy order (backend first, flag on last)

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
