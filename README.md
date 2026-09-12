# ClubSphere — Esports Club Management System

**CSC 3215 Web Technologies · Summer 2025–26 · Section A**
HTML · CSS · JavaScript · PHP · MySQL (XAMPP + phpMyAdmin)

A role-based web platform for a university esports club: members, teams,
tournaments, match results, finances and equipment in one system, replacing the
current mix of social media, messaging apps and spreadsheets.

Roles: **Admin**, **Moderator**, **Member**.

---

## Group and module ownership

| Member | Requirements | Module | Branch |
|---|---|---|---|
| Maria Hossain Jotey | FR1–FR5 | Accounts, login, roles, dashboards | `feature/fr01-05-maria` |
| — | FR6–FR10 | Teams, tournaments, brackets | `feature/fr06-10-<name>` |
| **Bibek Howlader** | **FR11–FR15** | **Match lifecycle + club income** | `feature/fr11-15-bibek` |
| — | FR16–FR20 | Expenses, reports, inventory | `feature/fr16-20-<name>` |
| — | FR21–FR25 | Announcements, notifications, admin, sponsors | `feature/fr21-25-<name>` |

Bibek handles the repository: reviews every pull request and does every merge.

---

## Running it locally

**1. Put the project in your web root**

```
C:/xampp/htdocs/esports-management            (Windows)
/Applications/XAMPP/htdocs/esports-management  (Mac)
```

The application itself lives in the `ClubSphere/` folder inside it, so the URL is
<http://localhost/esports-management/ClubSphere/Views/login.php>.

**2. Start Apache and MySQL** from the XAMPP control panel.

**3. Create the database in phpMyAdmin**

Open <http://localhost/phpmyadmin>, then:

1. Create a database called **`clubsphere`** (utf8mb4) if it does not exist
2. Select it in the left sidebar
3. **Import** → each `ClubSphere/Sql/*.sql` file, **in numerical/alphabetical
   order** — later files have foreign keys pointing at tables the earlier ones
   create

**4. Open the app** and register, or log in with an account an admin has
approved.

---

## Folder structure

```
esports-management/
├── .github/pull_request_template.md
├── docs/
│   ├── GIT_PLAYBOOK.md          full Git process (integrator's copy)
│   ├── TEAMMATE_CHEATSHEET.md   the daily routine — read this first
│   └── MERGE_NOTES.md           FR11–FR15 pull request notes
└── ClubSphere/
    ├── Models/          all database access, one file per module
    ├── Controls/        form handling, validation, role guards
    ├── Views/           pages
    ├── Css/             one stylesheet per module — never one shared style.css
    ├── Js/              one script per screen
    ├── Images/
    ├── Sql/             one schema file per member
    └── Uploads/results/ match screenshots (git-ignored)
```

**Why one CSS file and one SQL file per member:** five people editing one
`style.css` or one `schema.sql` on five branches conflicts on nearly every pull
request. Splitting by owner means Git almost never has to guess.

---

## Shared files — do not edit on your own branch

| File | Rule |
|---|---|
| `ClubSphere/Models/dbConnect.php` | Frozen. Credentials change once, on `dev`, and everyone pulls immediately. |
| `ClubSphere/Views/login.php`, `register.php`, `logout.php` | FR1–FR5 module owns these. |
| `README.md` | Ask in the group chat first. |

**The one rule to repeat until it sticks: never edit a file you do not own.**
If someone else's file blocks you, message them or raise it on the pull request.

---

## Git — the short version

```bash
git checkout dev
git pull origin dev
git checkout feature/frXX-YY-yourname
git merge dev              # every session, before you write any code
# ... work ...
git status
git add <the files you changed>
git commit -m "feat(FR7): moderator can edit team member lists"
git push origin feature/frXX-YY-yourname
# then open a pull request on GitHub with base: dev
```

Pull requests target **`dev`**, never `main`. Only Bibek merges.

Full guide: `docs/TEAMMATE_CHEATSHEET.md` (read this) and `docs/GIT_PLAYBOOK.md`.
