# Merge notes — FR11–FR15 (Bibek Howlader, 23-54606-3)

Use this as the pull request description, and read the **Blocking issue**
section before merging anything.

---

## What this branch delivers

| ID | Requirement | Screen |
|---|---|---|
| FR11 | Moderators shall be able to set and modify match times within a tournament | `Views/matchSchedule.php` |
| FR12 | Members or Moderators shall be able to upload match results and supporting screenshots | `Views/submitResult.php` |
| FR13 | Moderators shall have the ability to verify and finalize submitted match results | `Views/verifyResults.php` |
| FR14 | The system shall automatically update rankings and leaderboards based on match outcomes | `Views/leaderboard.php` |
| FR15 | Admins shall be able to record income from sponsorships, donations and entry fees | `Views/adminFund.php` |

Plus **`Views/moderatorDashboard.php`** — see "Gap filled" below.

FR11–FR14 are one chain, not four separate features:

```
Pending ──FR11 set time──▶ Scheduled ──FR12 submit score + screenshot──▶ awaiting review
                                                                              │
                     ┌──── FR13 reject (with a reason) ────────────────────────┤
                     │        team submits again                                │
                     ▼                                                    FR13 verify
                back to Scheduled                                               │
                                                                                ▼
                                        Completed + winner set + FR14 leaderboard rebuilt
```

FR15 is separate — the club's income ledger.

---

## Gap filled: the moderator dashboard

`Controls/loginControls.php` already redirects a Moderator here:

```php
header("Location: ../Views/moderatorDashboard.php");
```

…but that file did not exist, so **moderator login landed on a 404**. Two of my
five requirements are moderator screens, so I built that dashboard. It uses the
same sidebar, cards and pill menu as `adminDashboard.php`, and shows live counts
of scheduled matches, matches awaiting a schedule, and results waiting to be
verified.

---

## ⚠ Blocking issue found while testing — not my file, please fix

Two files in the FR1–FR5 module have blank lines **after** their closing `?>`
tag. PHP sends those bytes to the browser the moment the file is included, and
once any output has been sent, `header()` stops working. Every redirect in the
project then fails silently.

| File | Bytes after final `?>` |
|---|---|
| `Models/dbConnect.php` | 60 bytes (30 blank lines) |
| `Models/userModels.php` | 4 bytes (2 blank lines) |

**How I confirmed it.** With `output_buffering = 0` in `php.ini`:

- `Controls/loginControls.php` returns `200` and never redirects — login is broken
- Delete the trailing lines in **both** files → login returns `302 → moderatorDashboard.php` ✓

XAMPP's default `php.ini` sets `output_buffering = 4096`, which hides this
completely. That is why nobody has hit it yet. But any teammate whose PHP is
configured differently — or any host we deploy to — sees a dead login page and no
error message explaining why.

**The fix (30 seconds, in the FR1–FR5 branch):** delete everything after the last
`?>` in both files. Better still, **delete the closing `?>` entirely** — in a file
that contains only PHP it is optional, and leaving it off makes this class of bug
impossible.

I did **not** change those files myself, because they belong to the FR1–FR5
module and editing another person's file on my branch is exactly what causes
merge conflicts. Please apply it on your branch.

**Meanwhile, my module is safe either way.** Each of my four controllers calls
`ob_start()` as its first statement, so my redirects work whether or not the
root cause has been fixed. The comment at the top of each file explains why.

---

## Design decisions worth defending

**The table is `matches`, not `match`.** `MATCH` is a reserved word in MySQL
(`MATCH … AGAINST` full-text search). Renaming it means no query ever needs
backticks. A documented, deliberate deviation from the SRS.

**The leaderboard is recomputed, not incremented.** Verifying a result does not
run `UPDATE team_rating SET won = won + 1`. It deletes the standings for that
tournament and rebuilds them from every verified `match_result`. That makes it
*idempotent* — running it once or five times gives the same answer — and every
number on the leaderboard can be justified by the verified results behind it.
Incrementing drifts silently the moment a result is corrected or a button is
double-clicked.

**Verification runs in one database transaction.** Verifying does four things:
stamp the submission, supersede any competing submission, set the winner and
complete the match, rebuild the leaderboard. If the script died between steps 3
and 4, the match would say "Phoenix won" while the leaderboard showed zero wins
— and nobody would notice until the defense. `mysqli_begin_transaction` …
`mysqli_commit` makes all four happen or none.

**Recording income writes two tables in one transaction.** The SRS normalises
finance to 3NF: `transaction` holds what every money movement shares, `income`
holds what is specific to money coming in. If the transaction row were written
and the income row failed, the ledger would hold money from an unknown source —
exactly the mismanagement FR15 exists to prevent.

**Uploads are checked by content, not by name.** `$_FILES["type"]` is sent by the
browser and can simply be a lie. `finfo` reads the file's own bytes. The file is
then saved under a random name with an extension the *server* chooses, and
`Uploads/results/.htaccess` switches the PHP engine off in that folder — so even
if a `.php` file somehow landed there, Apache would serve it as plain text.
Three independent locks on the same door.

**Rejected submissions are kept, not deleted.** That is the audit trail: a
moderator can show why a result was rejected and what was submitted instead.

---

## Notes for the other modules

**For FR6–FR10 (teams and tournaments).** My SQL file creates `team`,
`team_member`, `tournament` and `tournament_register` as **stubs**, using
`CREATE TABLE IF NOT EXISTS`, because FR11–FR15 cannot be demonstrated without
somewhere for teams and tournaments to live. They are yours. Importing your file
after mine will not wipe anything. Once your module is merged, delete that block
from `Sql/03_matches_fund.sql` — I will do it in a follow-up PR.

**For FR16–FR20 (expenses).** Write your expenses to the **same** `transaction`
table with `transaction_type = 'Expense'`, plus your own `expense` table in your
own SQL file. `getClubFund()` in `Models/fundModel.php` already computes
`income − expense`, so the Club Fund figure starts working the moment your module
lands. Tested — see the test note below.

**For FR25 (sponsor directory).** `sponsor` already exists (my `income` table has
a foreign key to it). Build your CRUD screens on top of it; do not re-create it.

**For FR1–FR5 (accounts).** Two things you may want:

- `adminDashboard.php` shows a hard-coded `150,000 TK` on the Club Fund card.
  Replace it with the real figure whenever you like:
  ```php
  <?php require_once "../Models/fundModel.php"; ?>
  <span><?php echo number_format(getClubFund()); ?> TK</span>
  ```
- `memberDashboard.php` has W / D / L boxes and a Wins card that are static.
  `Models/leaderboardModel.php` exposes `getUserTeam()`, `getRecentForm()` and
  `countTeamWins()` for exactly that. All read-only.

---

## Files added (no file in this PR already existed — zero collisions)

```
ClubSphere/Models/matchModel.php          FR11
ClubSphere/Models/resultModel.php         FR12, FR13
ClubSphere/Models/leaderboardModel.php    FR14
ClubSphere/Models/fundModel.php           FR15
ClubSphere/Controls/matchControls.php
ClubSphere/Controls/resultControls.php
ClubSphere/Controls/verifyControls.php
ClubSphere/Controls/fundControls.php
ClubSphere/Views/moderatorDashboard.php
ClubSphere/Views/matchSchedule.php
ClubSphere/Views/submitResult.php
ClubSphere/Views/verifyResults.php
ClubSphere/Views/leaderboard.php
ClubSphere/Views/adminFund.php
ClubSphere/Css/moderatorDashboard.css
ClubSphere/Css/matchPages.css
ClubSphere/Js/*.js                        (5 files)
ClubSphere/Sql/03_matches_fund.sql
ClubSphere/Uploads/results/.htaccess
```

I did not modify a single existing file.

---

## How this was tested

Against a live MariaDB 10.11 instance with the real `users` table:

- **64 automated assertions across FR11–FR15, all passing** — schedule clash
  detection, self-clash exemption, duplicate-submission blocking,
  double-verification blocking, draw handling (`winner_id` stays `NULL`),
  leaderboard idempotency, delete-cascade, and an expense flowing through
  `getClubFund()`
- All six screens render with **zero PHP warnings or notices**
- Role guards verified end-to-end, including **direct POSTs to the controllers**
  with the wrong role — a member POSTing to `verifyControls.php` and a moderator
  POSTing to `fundControls.php` are both refused, and neither wrote a row
- Logged in as Admin, Moderator and Member through Maria's real
  `loginControls.php`, not a test harness

---

## Reviewer checklist

- [ ] `Sql/03_matches_fund.sql` imports cleanly in phpMyAdmin on top of the
      existing `clubsphere` database
- [ ] Moderator login lands on the dashboard instead of a 404
- [ ] Schedule a match, submit a result as a member, verify it as a moderator,
      confirm the leaderboard changes
- [ ] Record an income as admin, confirm the summary cards update
- [ ] Log in as a Member and confirm `matchSchedule.php` and `adminFund.php`
      say "Access Denied!"
- [ ] Maria's own screens (login, register, profile, both dashboards, members
      list) still work
