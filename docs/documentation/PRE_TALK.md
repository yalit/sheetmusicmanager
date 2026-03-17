# Pre-Talk Checklist

Run through this one hour before the talk.

---

## System

- [ ] Laptop on power
- [ ] Notifications disabled
- [ ] Auto-sleep disabled
- [ ] Unnecessary applications closed

---

## Display

- [ ] Projector connected and mirrored
- [ ] IDE font size 16–18pt
- [ ] Terminal font size 16–18pt
- [ ] Browser zoom 125–150%

---

## Environment

```bash
git checkout main
symfony console doctrine:fixtures:load --no-interaction
symfony server:start
```

- [ ] Server running on expected port
- [ ] `/` loads

---

## Browser tabs

- [ ] Tab 1 — `/admin` logged out (for showing login)
- [ ] Tab 2 — `/admin` logged in as **member**
- [ ] Tab 3 — `/admin` logged in as **admin**

---

## IDE

- [ ] `src/Controller/Admin/` open in file tree
- [ ] `src/Filter/HasPdfFilter.php` bookmarked
- [ ] `src/Admin/Action/DuplicateSetlistAction.php` bookmarked
- [ ] `src/Controller/Action/AddSheetsToSetlistController.php` bookmarked
- [ ] `assets/admin/collection_table_sortable.js` bookmarked
- [ ] `templates/admin/form.html.twig` bookmarked

---

## Fallback

If something breaks mid-demo:

```bash
# Reset fixtures
symfony console doctrine:fixtures:load --no-interaction

# Jump to a known-good branch
git stash
git checkout epic/08-actions
symfony console doctrine:fixtures:load --no-interaction
```

- [ ] These commands tested and working
- [ ] `docs/DEMO_FLOW.md` open as reference
