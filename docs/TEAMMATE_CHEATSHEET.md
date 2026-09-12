# ClubSphere — Git cheat sheet

**Send this to the group. It is the whole routine. Nothing else is required of you.**

Repo: `https://github.com/BHowlader/esports-management`
Git owner / reviewer: **Bibek Howlader**

---

## 0. One time only, on your own laptop

```bash
git config --global user.name  "Your Full Name"
git config --global user.email "yourname@aiub.edu"
```

Use your real name — this is what shows up as your contribution when the faculty
checks who did what.

```bash
cd C:/xampp/htdocs                 # Windows
# cd /Applications/XAMPP/htdocs    # Mac

git clone https://github.com/BHowlader/esports-management.git
cd esports-management

git checkout dev
git pull origin dev
git checkout -b feature/frXX-YY-yourname     # your branch, from the list below
git push -u origin feature/frXX-YY-yourname
```

| Member | Branch | Requirements |
|---|---|---|
| Maria | `feature/fr01-05-maria` | FR1–FR5 — accounts, roles, login, dashboards |
| (TBD) | `feature/fr06-10-teams` | FR6–FR10 — teams, tournaments, brackets |
| Bibek | `feature/fr11-15-bibek` | FR11–FR15 — match schedule, results, leaderboard, income |
| Sayma | `feature/fr16-20-sayma` | FR16–FR20 — expenses, reports, inventory |
| Sanchita | `feature/fr21-25-sanchita` | FR21–FR25 — announcements, notifications, admin, sponsors |

---

## 1. Every time you sit down to work

```bash
git checkout dev
git pull origin dev                          # get everyone's merged work
git checkout feature/frXX-YY-yourname
git merge dev                                # bring it into your branch NOW
```

**Do not skip the last line.** Merging `dev` daily means you deal with two days of
other people's changes at a time. Leaving it to the end means you deal with three
weeks of changes at once, the night before submission.

---

## 2. Every time you finish something that works

```bash
git status                                   # READ this before anything else
git add models/yourFile.php views/yourPage.php
git commit -m "feat(FR7): moderator can edit team member lists"
git push origin feature/frXX-YY-yourname
```

Then on GitHub: click **Compare & pull request**.

- **base: `dev`** ← important, not `main`
- **compare:** your branch
- Write 2–3 lines: what it does, which FRs, anything Bibek should watch for
- Request **Bibek** as reviewer

If Bibek requests changes, just push another commit to the same branch — the pull
request updates itself. Do not open a new one.

---

## 3. Commit message format

```
type(FRxx): what the project can do now
```

| Type | Use for |
|---|---|
| `feat` | a new requirement works |
| `fix` | something broken now works |
| `refactor` | code tidied, behaviour unchanged |
| `style` | CSS / formatting only |
| `docs` | README, comments |
| `chore` | config, folders, .gitignore |

Good: `feat(FR9): teams can register for a tournament`
Bad: `update`, `final`, `asdf`, `changes`

---

## 4. Rules that keep the project from breaking

1. **Never edit a file you do not own.** If someone else's file has a bug that blocks
   you, message them — do not fix it on your branch.
2. **These files are frozen.** Nobody edits them without asking the group first:
   `models/dbConnect.php`, `controllers/authGuard.php`, `ClubSphere/Sql/00_core_baseline.sql`.
3. **Your database tables go in your own SQL file** — `ClubSphere/Sql/0X_yourmodule.sql`.
   Never add tables to the shared baseline file.
4. **Your CSS goes in your own stylesheet.** No shared `style.css`.
5. **Never `git add .` without reading `git status` first.** That is how a 40 MB
   XAMPP folder ends up in the repo forever.
6. **Never push straight to `main` or `dev`.** Only Bibek merges.
7. **Push at the end of every session,** even if the feature is not finished. A
   commit that is only on your laptop is not a backup.

---

## 5. If something goes wrong

| Situation | Command |
|---|---|
| I need to switch branches mid-change | `git stash` … `git stash pop` |
| I wrecked a file, not committed yet | `git restore path/to/file.php` |
| I want to undo my last commit, keep the code | `git reset --soft HEAD~1` |
| I committed on the wrong branch | tell Bibek, don't improvise |
| A merge is going badly | `git merge --abort` |
| I think I lost work | `git reflog` — then tell Bibek. It is almost always recoverable. |

**Never run `git push --force`.** It deletes other people's work from GitHub with
no warning.

---

## 6. Merge conflict — what to do

A conflict is not damage. Git is asking which version you meant.

You will see this inside the file:

```
<<<<<<< HEAD
your line
=======
their line
>>>>>>> dev
```

1. Open the file, decide what the correct final code is
2. Delete all three marker lines: `<<<<<<<`, `=======`, `>>>>>>>`
3. `git add thatfile.php`
4. `git status` — repeat until nothing is listed as unmerged
5. `git commit`

Before committing, search the project for `<<<<<<<`. A leftover marker makes PHP
fatal-error on a line nobody wrote.

If you are stuck: `git merge --abort` puts everything back and costs nothing.
