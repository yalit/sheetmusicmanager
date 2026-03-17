# Git Branch Strategy for Talk

## Visual Branch Flow

```
main (production-ready demo)
 │
 ├── epic/00-planning ✅ COMPLETE
 │   └── All documentation
 │
 ├── epic/01-setup
 │   └── Fresh Symfony + dependencies
 │
 ├── epic/02-entities
 │   └── All entities + migrations
 │
 ├── epic/03-basic-admin
 │   └── Basic EasyAdmin CRUD
 │   │
 │   └── 📍 TAG: step-1-base ⭐ TALK STARTS HERE
 │       └── Clean starting point for demo
 │
 ├── epic/04-security
 │   └── Authentication + roles + voters
 │
 ├── epic/05-multi-tenancy
 │   └── Organization scoping
 │
 ├── epic/06-files
 │   └── File & image uploads
 │
 ├── epic/07-filters
 │   └── Custom filter extensions
 │   │
 │   └── 📍 TAG: step-2-custom-filters 🔴 AFTER LIVE CODING #1
 │       └── Safety net if live coding fails
 │
 ├── epic/08-actions
 │   └── Custom actions
 │   │
 │   └── 📍 TAG: step-3-custom-actions 🔴 AFTER LIVE CODING #2
 │       └── Safety net if live coding fails
 │
 ├── epic/09-custom-fields
 │   └── Custom form types
 │
 ├── epic/10-javascript
 │   └── Embedded JS + Stimulus
 │
 ├── epic/11-advanced
 │   └── Export, queries, search
 │   │
 │   └── 📍 TAG: step-4-complete 🟢 WALKTHROUGH STARTS HERE
 │       └── All features for demonstration
 │
 ├── epic/12-demo-data
 │   └── Realistic fixtures
 │
 └── epic/13-talk-prep
     └── Final polish + rehearsal
```

---

## Talk Flow with Git

### Before Talk Starts

```bash
# Ensure clean state
git checkout step-1-base
composer install
php bin/console doctrine:migrations:migrate
php bin/console doctrine:fixtures:load

# Start server
symfony server:start

# Open browser tabs:
# - Tab 1: /admin (logged out)
# - Tab 2: /admin (ready to login)

# Open IDE with bookmarks:
# - src/Controller/Admin/
# - src/Filter/
# - src/Action/
```

---

## During Talk: Branch Usage

### Section 1: Foundation (3-8 min)
**Current Branch**: `step-1-base`

**Show**:
- Running application
- Basic CRUD operations
- Entity relationships
- "This is our starting point"

**No branch changes**

---

### Section 2: Live Coding #1 - Custom Filter (8-11 min)
**Current Branch**: `step-1-base`

**Action**: Live code the custom filter

**If Success**:
- Continue with working filter
- Move to Live Coding #2

**If Failure** (typo, error, timing):
```bash
# Quick recovery
git stash
git checkout step-2-custom-filters
php bin/console cache:clear

# Show working filter
# Explain: "Here's what we were building"
```

**Outcome**: Filter is working, either live-coded or from safety net

---

### Section 3: Live Coding #2 - Custom Action (11-15 min)
**Current Branch**: `step-1-base` OR `step-2-custom-filters`

**Action**: Live code the custom action

**If Success**:
- Continue with working action
- Move to walkthrough section

**If Failure**:
```bash
git stash
git checkout step-3-custom-actions
php bin/console cache:clear

# Show working action
# Explain: "Here's the complete implementation"
```

**Outcome**: Action is working, either live-coded or from safety net

---

### Section 4: Advanced Walkthrough (15-26 min)
**Current Branch**: `step-3-custom-actions` OR `step-4-complete`

**Recommended**: Switch to `step-4-complete` for safety

```bash
# Before starting walkthrough
git checkout step-4-complete
php bin/console cache:clear

# Now all features guaranteed to work
```

**Show** (no live coding, just demonstrate):
- Multi-tenancy (switch users)
- Role-based access (different roles)
- File uploads (upload PDF, image)
- JS integration (duration format, drag-drop)
- Export with filters
- Custom actions (PDF generation, etc.)

**Why step-4-complete?**
- All features are pre-built and tested
- No risk of breakage during demo
- Can focus on explaining, not coding
- Smooth user switching for multi-tenancy demo

---

## Disaster Recovery

### Complete Demo Failure

If everything breaks (network, database, environment):

**Plan A - Switch to main**:
```bash
git checkout main
docker-compose up -d  # If using Docker as backup
```

**Plan B - Show pre-recorded video**:
- Have a 5-minute video showing all features
- Narrate over the video
- "Let me show you what this looks like..."

**Plan C - Slides only**:
- Code examples in slides
- Screenshots of each feature
- Focus on explanation and concepts

---

## Branch Checkout Quick Reference

```bash
# Fresh start (talk beginning)
git checkout step-1-base

# After custom filter live coding (or if it fails)
git checkout step-2-custom-filters

# After custom action live coding (or if it fails)
git checkout step-3-custom-actions

# For walkthrough section (safest choice)
git checkout step-4-complete

# Complete production-ready version
git checkout main
```

---

## Pre-Talk Checklist

### One Week Before:
- [ ] All epics completed and merged to main
- [ ] All git tags created and verified
- [ ] Test checkout each branch - verify it runs
- [ ] Rehearse full talk 3 times

### One Day Before:
- [ ] Fresh clone of repo to clean directory
- [ ] Test all branches on fresh clone
- [ ] Prepare laptop (disable notifications, clean desktop)
- [ ] Charge laptop fully
- [ ] Backup: USB drive with repo + video

### One Hour Before:
- [ ] git checkout step-1-base
- [ ] composer install (if needed)
- [ ] php bin/console doctrine:migrations:migrate
- [ ] php bin/console doctrine:fixtures:load
- [ ] symfony server:start
- [ ] Test login with each test user
- [ ] Open browser tabs
- [ ] Open IDE with bookmarks
- [ ] Close all unnecessary apps
- [ ] Disable notifications
- [ ] Connect to projector and test display

### Right Before Talk:
- [ ] Server running
- [ ] Browser ready at /admin
- [ ] IDE ready
- [ ] Terminal ready
- [ ] Water nearby
- [ ] Backup USB plugged in
- [ ] Deep breath!

---

## Test User Credentials

For demo, have these users ready in fixtures:

```yaml
# Organization: "St. Mary's Choir" (ID: 1)
member@stmarys.org / password123    # ROLE_MEMBER (read-only)
librarian@stmarys.org / password123 # ROLE_LIBRARIAN (manage sheets)
conductor@stmarys.org / password123 # ROLE_CONDUCTOR (sheets + setlists)

# Organization: "City Jazz Band" (ID: 2)
admin@cityjazz.org / password123    # ROLE_ADMIN (everything)

# For multi-tenancy demo:
# Login as conductor@stmarys.org - see choir data only
# Login as admin@cityjazz.org - see band data only
```

Keep these credentials on a sticky note or separate screen!

---

## Timing with Branches

| Time | Section | Branch | Type |
|------|---------|--------|------|
| 0-3 min | Hook & Context | step-1-base | Slides + Demo |
| 3-8 min | Foundation | step-1-base | Walkthrough |
| 8-11 min | Live Code: Filter | step-1-base | **LIVE** |
| 11-15 min | Live Code: Action | step-1-base or step-2 | **LIVE** |
| **15 min** | **Switch branches** | **→ step-4-complete** | **Quick switch** |
| 15-17 min | Multi-tenancy | step-4-complete | Walkthrough |
| 17-20 min | JS Integration | step-4-complete | Walkthrough |
| 20-22 min | Role-based Access | step-4-complete | Walkthrough |
| 22-24 min | File Handling | step-4-complete | Walkthrough |
| 24-26 min | Export + Queries | step-4-complete | Walkthrough |
| 26-30 min | Wrap-up & Q&A | step-4-complete | Discussion |

**Key Moment (15 min mark)**:
```bash
# Quick switch during transition
git checkout step-4-complete
php bin/console cache:clear
# Refresh browser (still logged in)
# Continue seamlessly
```

Practice this transition until it's muscle memory!

---

## Why This Strategy Works

### Safety Nets:
- Can recover from any live coding failure
- Multiple checkpoints (step-1, 2, 3, 4)
- Each step is tested and working

### Clear Progression:
- Audience sees the journey (step-1 → step-4)
- Each step builds on previous
- Shows realistic development flow

### Flexible:
- Can skip live coding if short on time
- Can extend explanations if ahead of time
- Can show more/less depending on audience engagement

### Professional:
- Even if live coding fails, demo continues
- Looks prepared and confident
- Shows real-world git workflow

---

## Post-Talk

### Share with Attendees:

```markdown
# Sheet Music Manager - EasyAdmin Demo

## Explore the Code

Each branch represents a stage of development:

- `step-1-base` - Basic EasyAdmin setup (start here)
- `step-2-custom-filters` - Added custom filter extension
- `step-3-custom-actions` - Added custom actions
- `step-4-complete` - All features implemented
- `main` - Production-ready version

## Getting Started

git clone [repo-url]
cd sheetmusic-manager
git checkout step-1-base
composer install
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
php bin/console doctrine:fixtures:load
symfony server:start

## Test Users

- member@stmarys.org / password123 (read-only)
- librarian@stmarys.org / password123 (manage sheets)
- conductor@stmarys.org / password123 (manage sheets + setlists)
- admin@cityjazz.org / password123 (full access, different org)

## Documentation

See /docs folder for:
- TALK_PLANNING.md - Talk structure and timing
- ENTITY_MODEL.md - Complete entity specifications
- FEATURE_DEMONSTRATIONS.md - Feature implementation details
- EPICS_AND_STORIES.md - Development epic breakdown

## Questions?

[Your contact info]
```

---

## Final Thoughts

**Remember**:
- The code is a tool to demonstrate concepts
- If live coding fails, that's okay - you have backups
- The goal is to inspire, not to type perfectly
- Attendees care about learning, not perfect execution
- Your preparation (this branch strategy) shows professionalism

**You've got this!** 🎵🎸🎹
