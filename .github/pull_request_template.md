## What this delivers

FR__ :

<!-- 2-3 lines: what works now that did not work before -->

## How I tested it

- [ ] Ran every page I touched in XAMPP — no PHP warnings or notices on screen
- [ ] My `ClubSphere/Sql/` file imports cleanly in phpMyAdmin
- [ ] Logged in as Admin / Moderator / Member and clicked through
- [ ] Every query uses `mysqli_prepare` with bound parameters
- [ ] Every protected page or controller checks the role **and** `exit()`s
- [ ] I did not edit any file I do not own
- [ ] I merged the latest `dev` into my branch before pushing

## Anything the reviewer should watch out for

