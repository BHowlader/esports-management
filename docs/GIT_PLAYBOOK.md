# ClubSphere — Git Playbook (integrator's copy)

CSC 3215 Web Technologies · Summer 2025–26 · Section A
Integrator: **Bibek Howlader** (23-54606-3) · Repo: `BHowlader/esports-management`

> Teammates only need `docs/TEAMMATE_CHEATSHEET.md`. This file is the full version:
> setup, review process, recovery, and defense preparation.

---

## 1. Repository setup (once)

```bash
# every member, once per laptop
git config --global user.name  "Bibek Howlader"
git config --global user.email "bibek@aiub.edu"
git config --global init.defaultBranch main
git config --global --list

# Bibek, once
cd /Applications/XAMPP/htdocs          # Mac
# cd C:/xampp/htdocs                   # Windows
git clone https://github.com/BHowlader/esports-management.git
cd esports-management

# copy the starter files in, then:
git add .
git status
git commit -m "chore: project structure, shared schema and gitignore"
git branch -M main
git push -u origin main

git checkout -b dev
git push -u origin dev
```

`-u` links the local branch to the remote one, so plain `git push` / `git pull` work
afterwards without naming the remote.

---

## 2. Branch model

```
main     ●────────────────────●───────────────────────●   defense-day branch, always runs
                             ╱                       ╱
dev      ●──●──●────────────●────────●──────────────●     integration; PRs land here
            ╲   ╲                   ╱              ╱
feature      ●───●──●──●──●────────●              ╱       one per member
              ╲                                  ╱
feature        ●────●──────●──────────────────●─╱
```

| Branch | Job | Who merges in |
|---|---|---|
| `main` | The branch we demo from. Must always run. Receives only tested code from `dev`. | Bibek, from `dev` only |
| `dev` | Where five modules meet for the first time. Breakage is *allowed* here — that is the point. All PRs target `dev`. | Bibek, by merging PRs |
| `feature/…` | One per member, their private workspace. | Its owner |

Branch names — agree these exactly:

```
feature/fr01-05-maria      # accounts, roles, login, dashboards  (Maria)
feature/fr06-10-teams       # teams, tournaments, brackets
feature/fr11-15-bibek       # match schedule, results, leaderboard, income
feature/fr16-20-sayma       # expenses, reports, inventory
feature/fr21-25-sanchita    # announcements, notifications, admin, sponsors
```

**Everyone branches from `dev`, never from `main`.** A branch remembers where it
started; branching from a stale `main` drags old state into the PR and creates
conflicts in files nobody opened.

*Two-branch fallback:* if the group finds `dev` too much, drop it and point PRs at
`main` — every command here still works, replace `dev` with `main`. You lose the
safety net: a bad merge is then already in the branch you demo from.

---

## 3. File ownership — the rule that prevents most conflicts

Git only reports a conflict when two branches change the **same lines of the same
file**. So the cheapest prevention is agreeing that each file has one owner.

**Bibek owns (FR11–FR15):**

```
models/matchModel.php           models/resultModel.php
models/leaderboardModel.php     models/financeModel.php
controllers/matchScheduleControls.php   controllers/resultSubmitControls.php
controllers/resultVerifyControls.php    controllers/incomeControls.php
views/moderator/                views/member/submitResult.php
views/admin/recordIncome.php    views/leaderboard.php
views/css/matchFinance.css      views/js/{matchSchedule,submitResult,verifyResults,recordIncome,leaderboard}.js
ClubSphere/Sql/03_matches_finance.sql
```

**Shared / frozen files** — nobody edits these on a feature branch:

| File | Rule |
|---|---|
| `models/dbConnect.php` | Frozen. Credentials change once, on `dev`, and everyone pulls immediately. |
| `controllers/authGuard.php` | Frozen. A change here affects every screen. |
| `ClubSphere/Sql/00_core_baseline.sql` | Frozen after first import. Two people editing one `CREATE TABLE` desyncs the DB from everyone's code at once. |
| `index.php`, `README.md` | Ask in the group chat first. |

**The one rule to repeat until it sticks: never edit a file you do not own.**

---

## 4. Reviewing a pull request

Reading the diff on GitHub is not reviewing. PHP has no compiler — code that looks
fine can still fatal-error at runtime. **Run it before you merge it.**

```bash
git fetch origin

# option A — their branch directly
git checkout feature/fr06-10-teams
git pull origin feature/fr06-10-teams

# option B — fetch PR #3 into a scratch branch
git fetch origin pull/3/head:pr-3
git checkout pr-3
```

### Review checklist

**Does it run**
- [ ] Every page the PR touches opens in XAMPP
- [ ] Zero PHP warnings or notices on screen
- [ ] Their `ClubSphere/Sql/` file imports cleanly in phpMyAdmin

**Does it break anyone else**
- [ ] Logged in as admin, moderator and member and clicked through
- [ ] Opened two screens from *other* members' modules
- [ ] No shared/frozen file was quietly edited

**Security basics**
- [ ] Every query uses `mysqli_prepare` with bound parameters — no `$_POST` pasted into SQL
- [ ] Every protected page calls the role guard **and** `exit`s after redirecting
- [ ] Output from the database is escaped before printing

**Hygiene**
- [ ] No `.DS_Store`, `node_modules`, database dumps, personal paths
- [ ] No leftover `var_dump` or commented-out dead code
- [ ] Commit messages readable in `git log --oneline`

If something is wrong: comment on the exact line, click **Request changes**. Do not
fix it yourself. They push another commit to the same branch and the PR updates.

### Merging

Approve, then merge on GitHub with **Create a merge commit** (not squash, not
rebase) — the merge commit is what makes `git log --graph` show five branches
joining, which is the picture you want on defense day.

From the terminal instead:

```bash
git checkout dev
git pull origin dev
git merge --no-ff feature/fr06-10-teams -m "merge: FR6-FR10 team and tournament module"
git push origin dev

git branch -d feature/fr06-10-teams
git push origin --delete feature/fr06-10-teams
```

### Promoting dev to main

Only at real milestones, and only after clicking through the whole app on `dev`.

```bash
git checkout main
git pull origin main
git merge --no-ff dev -m "release: all modules integrated for defense"
git push origin main

git tag -a v1.0-defense -m "Build presented at the defense"
git push origin v1.0-defense
```

**Tag the night before the defense.** If someone pushes something broken that
morning, `git checkout v1.0-defense` puts the working build back in one second.

---

## 5. Branch protection

GitHub → **Settings → Branches → Add branch protection rule** for `main`:

- Require a pull request before merging
- Require approvals: 1
- Do not allow bypassing the above settings
- Require conversation resolution before merging

Lighter rule for `dev`: require a PR, no approval count.

Branch protection is free on **public** repos; on a private repo on the free plan
parts of it are locked. Either make the repo public (fine for coursework) or keep it
as a group agreement — it still holds, because only you merge. Say exactly that if
asked.

### PR template

Create `.github/pull_request_template.md`:

```markdown
## What this delivers
FR__ :

## How I tested it
- [ ] Ran every page I touched in XAMPP, no PHP warnings
- [ ] My ClubSphere/Sql/ file imports cleanly in phpMyAdmin
- [ ] Logged in as admin / moderator / member and clicked through
- [ ] I did not edit any file I do not own
- [ ] I merged the latest dev into my branch before pushing

## Anything the reviewer should watch out for
```

---

## 6. Merge conflicts

```
<<<<<<< HEAD
header("Location: login.php");            ← what is on the branch you are ON
=======
header("Location: ../views/login.php");   ← what is coming IN
>>>>>>> feature/fr01-05-maria
```

```bash
git status              # lists conflicted files under "Unmerged paths"
# edit each file, delete all three marker lines
git add views/logout.php
git status              # repeat until nothing is unmerged
git commit              # completes the merge
git push origin dev

git merge --abort       # escape hatch — undoes the merge, no damage
```

**Before committing a resolution, search the project for `<<<<<<<`.** A leftover
marker makes PHP fatal-error on a line nobody wrote.

### Conflicts to expect in this project

| File | Why | Resolution |
|---|---|---|
| `views/logout.php` | Bibek added a placeholder so his screens had a Logout link; Maria adds the real one | **Keep hers**, delete the placeholder |
| `index.php` | Same reason — two people wrote a two-line redirect | **Keep the auth owner's** |
| `models/dbConnect.php` | Someone changed credentials locally and committed | Keep `dev`'s version, tell them the agreed name |
| `README.md` | Two people documenting their module | **Keep both** — delete markers, leave both paragraphs |
| Any `ClubSphere/Sql/` file | Should never happen | Someone edited a file they don't own. Stop and talk. |

---

## 7. Recovery

| Situation | Command |
|---|---|
| Committed to the wrong branch | `git reset --hard HEAD~1` then `git cherry-pick <hash>` on the right one |
| Undo last commit, keep the code | `git reset --soft HEAD~1` |
| Bad merge already on `main` | `git revert -m 1 <merge-hash>` then push |
| Need to switch branches mid-change | `git stash` … `git stash pop` |
| Wrecked a file, not committed | `git restore path/to/file.php` |
| Deleted a branch with work on it | `git reflog`, then `git checkout -b recovered <hash>` |

`git reflog` is the undo history for the entire repository. Anything ever committed
— even on a deleted branch, even after `reset --hard` — is reachable from there for
about 90 days.

**`-m 1`** on a revert names which side of the merge to keep; `1` is always the
branch you merged *into*. Use `revert`, never `reset`, on a branch other people have
pulled — revert adds history, reset rewrites it, and rewriting shared history breaks
every clone.

**Never `git push --force` on `main` or `dev`.** On your own feature branch use
`--force-with-lease`, which refuses if someone pushed since you last fetched.

---

## 8. Commit messages

```
feat(FR11): moderator can set and modify match times
feat(FR12): result upload with screenshot validation
feat(FR13): verify and finalize results in one transaction
feat(FR14): leaderboard recomputed from verified results
fix(FR11): reject a schedule that clashes with the same team
refactor(FR15): move income validation into the model
docs: add setup instructions to README
chore: add .gitignore for uploads and .DS_Store
```

Types: `feat` · `fix` · `refactor` · `style` · `docs` · `chore`

Present tense — a commit message describes what the project does *after* it is
applied. Done consistently, `git log --oneline` becomes a delivery report you can
put on screen during the defense.

---

## 9. Defense day

Have these ready in a terminal:

```bash
git log --oneline --graph --all --decorate   # the whole project as a picture
git branch -a                                # every branch, local and remote
git shortlog -sn --all                       # commits per person — proves everyone contributed
git log --oneline --grep="FR13"              # every commit touching one requirement
```

`git shortlog -sn --all` is the strongest single thing you can show. The real
question about group work is always *"did everyone actually do something?"* and this
answers it from the repository itself, with no claims from you.

### Questions to expect

**Why branches at all, why not just share files?**
Five people editing one folder means the last to save overwrites everyone. A branch
gives each member an isolated copy of the whole project, so my match module can't
break the FR6-FR10 owner's team module mid-change. Git then merges the finished pieces in a
controlled way, at a moment I choose.

**Difference between `main` and `dev`?**
`main` is what we demo from — it must always run. `dev` is where the five modules
meet for the first time, so integration problems surface there instead of in the
branch we present. Code reaches `main` only from `dev`, after I've tested it.

**What actually happens when you merge?**
Git finds the last commit both branches had in common, works out what each side
changed since, and applies both. Different files or different lines — automatic.
Same lines — it stops and reports a conflict for a human.

**Show me a conflict you resolved.**
I created a placeholder `views/logout.php` so my screens had a Logout link before the
auth module merged. Maria's branch added the real one. Git reported the conflict; I
kept hers, deleted mine, `git add`, `git commit`.

**What is a pull request? Git already has `git merge`.**
A PR is a GitHub feature, not a Git one — a request to merge, held open for review.
It gives us line-level comments, a record of who approved what, and a merge button
only I use. `git merge` would do the merge, but silently, with no review step.

**`git fetch` vs `git pull`?**
`fetch` downloads without touching my working files, so I can look before I leap.
`pull` is `fetch` + `merge`. Reviewing someone's work I use `fetch`, precisely
because I don't want it merged yet.

**What does `git add` do that `git commit` doesn't?**
`add` stages — it's me choosing *which* edits belong in the next commit. `commit`
records what's staged. That separation is why I can fix two unrelated things and
still record them as two clean commits.

**Why a `.gitignore`? What's in it?**
Files Git must never track. Ours excludes `.DS_Store` (macOS Finder metadata — it
changes constantly and conflicts on a file nobody wrote), editor settings, and
`uploads/`, because match screenshots uploaded at runtime are data, not source code.
Committed, every clone would fill with each other's test images.

**How do you make sure a merge doesn't break the project?**
I never merge from the diff alone. I check the branch out locally, import their SQL
in phpMyAdmin, run every page they touched in XAMPP, and log in as all three roles.
PHP has no compiler — running it is the only real check.

**A wrong merge reached `main`. What now?**
`git revert -m 1 <merge-hash>` — a new commit undoing the merge. Safe because it
adds to history rather than rewriting it. Not `git reset`, which rewrites shared
history and breaks every teammate's clone.

**Why is `git push --force` dangerous?**
It replaces GitHub's shared history with whatever is on my machine, deleting anything
a teammate pushed in between, with no warning.

**How did you divide the work?**
25 requirements, five per member, grouped so each person owns a coherent module.
Mine is FR11–FR15: the whole match lifecycle — scheduling, submission, verification,
leaderboard — plus income recording. Each member also owns their own model,
controller, view, CSS and SQL files, which is why we had very few conflicts.

**Why a separate SQL file per person?**
One schema file edited by five people conflicts on nearly every PR, and a bad
resolution desyncs the database from everyone's code at once. We have one frozen
baseline for shared tables and one numbered file per member.

**What does `git status` tell you?**
Which branch I'm on, what changed, what's staged, and whether I'm ahead of or behind
GitHub. First command of every session, and before every commit.

**Is a commit a backup?**
Only locally — it saves to `.git` on my machine. It becomes a backup once pushed,
which is why we push at the end of every session, not only when a feature is done.

**What is `HEAD`?**
A pointer to the commit I'm working on top of, usually the tip of the checked-out
branch. `HEAD~1` is one commit before that — which is why `git reset --soft HEAD~1`
undoes the last commit.

**Why `--no-ff`?**
By default, if nothing changed on the target branch, Git just moves the pointer
forward and no merge commit exists — the branch vanishes from history. `--no-ff`
forces it, so `git log --graph` still shows each feature branch joining.

**Hardest part of managing the repository?**
Getting everyone to merge `dev` into their branch daily rather than at the end. The
first time someone left it two weeks, their PR had conflicts in files they'd never
opened. We made it the first line of the daily routine and it stopped.

---

## 10. Command reference

**Looking around**

| Command | What it does |
|---|---|
| `git status` | branch, changed files, what is staged |
| `git diff` | line-by-line changes, not yet staged |
| `git diff --staged` | what will go into the next commit |
| `git log --oneline --graph --all` | branch history as a picture |
| `git show <hash>` | everything one commit changed |
| `git branch -a` | all branches, local and remote |
| `git shortlog -sn --all` | commit count per author |
| `git blame <file>` | who last changed each line |

**Everyday work**

| Command | What it does |
|---|---|
| `git clone <url>` | download the repo for the first time |
| `git checkout <branch>` | switch to an existing branch |
| `git checkout -b <branch>` | create a branch and switch to it |
| `git add <file>` | stage a file for the next commit |
| `git commit -m "msg"` | record everything staged |
| `git push origin <branch>` | send commits to GitHub |
| `git pull origin <branch>` | fetch and merge from GitHub |
| `git fetch origin` | download without merging |

**Integrating**

| Command | What it does |
|---|---|
| `git merge <branch>` | bring another branch into this one |
| `git merge --no-ff <branch>` | same, always leaving a merge commit |
| `git merge --abort` | cancel a merge going badly |
| `git cherry-pick <hash>` | copy one commit onto this branch |
| `git tag -a v1.0 -m "msg"` | permanent name for a commit |

**Getting out of trouble**

| Command | What it does |
|---|---|
| `git stash` / `git stash pop` | shelve uncommitted work, take it back |
| `git restore <file>` | discard uncommitted changes to a file |
| `git reflog` | everywhere HEAD has been — the real undo history |
| `git revert <hash>` | new commit undoing an old one — safe on shared branches |
| ⚠ `git reset --soft HEAD~1` | undo last commit, **keep** changes |
| ⚠ `git reset --hard HEAD~1` | undo last commit, **delete** changes |
| ⚠ `git push --force` | overwrite GitHub's history — never on `main`/`dev` |
| ⚠ `git clean -fd` | delete every untracked file — unrecoverable |
