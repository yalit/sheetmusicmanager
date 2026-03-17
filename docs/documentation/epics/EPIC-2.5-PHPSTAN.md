# Epic 2.5: Static Analysis with PHPStan

**Branch**: `epic/2.5-phpstan`
**Status**: ⏳ Pending
**Estimated Effort**: 1-2 hours
**Dependencies**: Epic 2 (Entity Layer)

---

## Goal

Set up PHPStan for static analysis to catch type errors and improve code quality. Configure PHPStan with Symfony and Doctrine extensions, then fix any issues found in the codebase.

---

## Stories

### Story 2.5.1: Install and Configure PHPStan

**Description**: Install PHPStan and required extensions for Symfony/Doctrine projects.

**Tasks**:
- [ ] Install PHPStan via Composer
- [ ] Install PHPStan Symfony extension
- [ ] Install PHPStan Doctrine extension
- [ ] Create `phpstan.neon` configuration file
- [ ] Configure PHPStan level (start with level 7)
- [ ] Add PHPStan to Makefile

**Acceptance Criteria**:
- PHPStan installed as dev dependency
- Configuration file created with Symfony/Doctrine extensions
- Can run PHPStan via `make phpstan` or `make analyse`
- Baseline established if needed

**Deliverables**:
- Updated `composer.json` with PHPStan dependencies
- `phpstan.neon` or `phpstan.neon.dist` configuration file
- Updated `Makefile` with PHPStan command

---

### Story 2.5.2: Run PHPStan and Fix Issues

**Description**: Run PHPStan against the codebase and fix any type-related issues.

**Tasks**:
- [ ] Run PHPStan on `src/` directory
- [ ] Review and categorize errors
- [ ] Fix type hints and docblocks
- [ ] Fix missing return types
- [ ] Fix incorrect property types
- [ ] Ensure all fixes maintain functionality

**Common Issues to Fix**:
- Missing return types
- Missing parameter types
- Incorrect docblock types
- Missing null checks
- Incorrect property types

**Acceptance Criteria**:
- PHPStan passes with 0 errors at configured level
- All type hints are correct
- Code quality improved
- No functionality broken

**Deliverables**:
- Fixed entity files
- Fixed repository files
- Fixed DTO files

---

### Story 2.5.3: Update CI/CD and Documentation

**Description**: Integrate PHPStan into the development workflow.

**Tasks**:
- [ ] Add PHPStan to Makefile quality checks
- [ ] Update README or documentation with PHPStan usage
- [ ] Document PHPStan level and configuration decisions

**Acceptance Criteria**:
- PHPStan can be run via `make` command
- Documentation updated
- Quality standards documented

**Deliverables**:
- Updated `Makefile`
- Updated documentation

---

## Epic Acceptance Criteria

- [ ] PHPStan installed and configured
- [ ] PHPStan runs without errors
- [ ] All type issues fixed
- [ ] Makefile updated with PHPStan commands
- [ ] Documentation updated

---

## Testing Checklist

```bash
# Run PHPStan
make phpstan
# or
vendor/bin/phpstan analyse

# Verify 0 errors
# [OK] No errors
```

---

## Deliverables

- [ ] `phpstan.neon` or `phpstan.neon.dist`
- [ ] Updated `composer.json`
- [ ] Updated `composer.lock`
- [ ] Fixed source files
- [ ] Updated `Makefile`
- [ ] Updated documentation

---

## Configuration Example

**phpstan.neon**:
```neon
parameters:
    level: 6
    paths:
        - src
    excludePaths:
        - src/Kernel.php
    symfony:
        container_xml_path: var/cache/dev/App_KernelDevDebugContainer.xml
    doctrine:
        objectManagerLoader: tests/object-manager.php
```

---

## Notes

- Start with level 6 (good balance between strictness and effort)
- Can increase to level 7 or 8 later
- Use baseline if there are too many errors initially
- PHPStan helps catch bugs before runtime
- Improves IDE autocomplete and refactoring

---

## Next Epic

**Epic 3**: Basic EasyAdmin CRUD
