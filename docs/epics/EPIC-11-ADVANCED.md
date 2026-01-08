# Epic 11: Advanced Features

**Branch**: `epic/11-advanced`
**Status**: ⏳ Pending
**Estimated Effort**: 3-4 hours
**Dependencies**: Epic 3 (Basic Admin)

**Git Tag After Completion**: `step-4-complete` 🟢 **WALKTHROUGH STARTS HERE**

---

## Goal

Implement advanced features including export with filters, custom queries, dashboard widgets, search functionality, and performance optimizations.

---

## Stories

### Story 11.1: Implement Export with Filters

**Description**: Add CSV/Excel export functionality that respects currently applied filters.

**Tasks**:
- [ ] Create export action
- [ ] Get filtered query builder
- [ ] Generate CSV file
- [ ] Apply filters to export
- [ ] Test with various filter combinations

**Technical Details**:

**Export Action** (in `SheetCrudController.php`):
```php
use Symfony\Component\HttpFoundation\StreamedResponse;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;

public function configureActions(Actions $actions): Actions
{
    $export = Action::new('export', 'Export')
        ->linkToCrudAction('export')
        ->setIcon('fa fa-download')
        ->addCssClass('btn btn-success')
        ->createAsGlobalAction(); // Available on index page

    return $actions
        ->add(Crud::PAGE_INDEX, $export)
        // ... other actions
        ;
}

public function export(AdminContext $context): Response
{
    $fields = FieldCollection::new($this->configureFields(Crud::PAGE_INDEX));
    $filters = $this->container->get(FilterFactory::class)->create(
        $context->getCrud()->getFiltersConfig(),
        $fields,
        $context->getEntity()
    );

    // Get the repository
    $repository = $this->container->get('doctrine')->getRepository($context->getEntity()->getFqcn());

    // Create query builder
    $queryBuilder = $repository->createQueryBuilder('entity');

    // Apply filters (same as index page)
    $appliedFilters = $context->getRequest()->query->all('filters');
    foreach ($appliedFilters as $filterName => $filterValue) {
        // Apply each filter to query builder
        // This is simplified - EasyAdmin handles this internally
    }

    // Apply organization filter (from Epic 5)
    if ($this->getUser()) {
        $queryBuilder
            ->andWhere('entity.organization = :organization')
            ->setParameter('organization', $this->getUser()->getOrganization());
    }

    // Get results
    $entities = $queryBuilder->getQuery()->getResult();

    // Generate CSV
    $response = new StreamedResponse(function() use ($entities) {
        $handle = fopen('php://output', 'w');

        // CSV Header
        fputcsv($handle, [
            'ID',
            'Title',
            'Composer',
            'Arranger',
            'Genre',
            'Difficulty',
            'Duration',
            'Key Signature',
            'Status',
            'Has PDF',
            'Created At'
        ]);

        // CSV Rows
        foreach ($entities as $sheet) {
            fputcsv($handle, [
                $sheet->getId(),
                $sheet->getTitle(),
                $sheet->getComposer() ? $sheet->getComposer()->getName() : '',
                $sheet->getArranger() ? $sheet->getArranger()->getName() : '',
                $sheet->getGenre(),
                $sheet->getDifficulty(),
                $sheet->getDuration(),
                $sheet->getKeySignature(),
                $sheet->getStatus(),
                $sheet->getPdfFileName() ? 'Yes' : 'No',
                $sheet->getCreatedAt() ? $sheet->getCreatedAt()->format('Y-m-d H:i:s') : '',
            ]);
        }

        fclose($handle);
    });

    $response->headers->set('Content-Type', 'text/csv');
    $response->headers->set('Content-Disposition', sprintf(
        'attachment; filename="sheets-export-%s.csv"',
        date('Y-m-d-His')
    ));

    return $response;
}
```

**Acceptance Criteria**:
- Export button appears on index page
- CSV file generated with all filtered data
- Filters applied to export match displayed filters
- File downloads with appropriate filename
- All relevant fields included

**Deliverables**:
- Export action in SheetCrudController
- Working CSV export with filters

---

### Story 11.2: Create Dashboard Widgets

**Description**: Add informative widgets to the admin dashboard.

**Tasks**:
- [ ] Create dashboard template
- [ ] Add total counts (sheets, setlists)
- [ ] Add most used composer widget
- [ ] Add recent activity widget
- [ ] Style dashboard professionally

**Technical Details**:

**Dashboard Controller** (`src/Controller/Admin/DashboardController.php`):
```php
use App\Repository\SheetRepository;
use App\Repository\SetlistRepository;
use App\Repository\PersonRepository;

#[Route('/admin', name: 'admin')]
public function index(): Response
{
    $user = $this->getUser();
    $organization = $user->getOrganization();

    $entityManager = $this->container->get('doctrine')->getManager();

    // Get statistics
    $stats = [
        'totalSheets' => $entityManager->getRepository(Sheet::class)
            ->count(['organization' => $organization]),
        'activeSheets' => $entityManager->getRepository(Sheet::class)
            ->count(['organization' => $organization, 'status' => 'active']),
        'totalSetlists' => $entityManager->getRepository(Setlist::class)
            ->count(['organization' => $organization]),
        'totalComposers' => $entityManager->getRepository(Person::class)
            ->count(['organization' => $organization]),
    ];

    // Get most used composer
    $mostUsedComposer = $entityManager->createQuery('
        SELECT p.name, COUNT(si.id) as usage_count
        FROM App\Entity\Person p
        JOIN p.sheetsComposed s
        JOIN s.setlistItems si
        WHERE p.organization = :organization
        GROUP BY p.id
        ORDER BY usage_count DESC
    ')
        ->setParameter('organization', $organization)
        ->setMaxResults(1)
        ->getOneOrNullResult();

    // Get recent sheets
    $recentSheets = $entityManager->getRepository(Sheet::class)
        ->findBy(
            ['organization' => $organization],
            ['createdAt' => 'DESC'],
            5
        );

    // Get upcoming setlists
    $upcomingSetlists = $entityManager->createQuery('
        SELECT s
        FROM App\Entity\Setlist s
        WHERE s.organization = :organization
        AND s.eventDate >= :today
        AND s.status != :performed
        ORDER BY s.eventDate ASC
    ')
        ->setParameter('organization', $organization)
        ->setParameter('today', new \DateTime())
        ->setParameter('performed', 'performed')
        ->setMaxResults(5)
        ->getResult();

    return $this->render('admin/dashboard.html.twig', [
        'stats' => $stats,
        'mostUsedComposer' => $mostUsedComposer,
        'recentSheets' => $recentSheets,
        'upcomingSetlists' => $upcomingSetlists,
    ]);
}
```

**Dashboard Template** (`templates/admin/dashboard.html.twig`):
```twig
{% extends '@EasyAdmin/page/content.html.twig' %}

{% block content %}
    <div class="dashboard">
        <h1 class="mb-4">
            <i class="fa fa-home"></i> Dashboard
        </h1>

        {# Statistics Cards #}
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card bg-primary text-white">
                    <div class="card-body">
                        <h3 class="mb-0">{{ stats.totalSheets }}</h3>
                        <p class="mb-0">Total Sheets</p>
                        <small>{{ stats.activeSheets }} active</small>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card bg-success text-white">
                    <div class="card-body">
                        <h3 class="mb-0">{{ stats.totalSetlists }}</h3>
                        <p class="mb-0">Setlists</p>
                        <small>&nbsp;</small>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card bg-info text-white">
                    <div class="card-body">
                        <h3 class="mb-0">{{ stats.totalComposers }}</h3>
                        <p class="mb-0">Composers</p>
                        <small>&nbsp;</small>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card bg-warning text-white">
                    <div class="card-body">
                        <h3 class="mb-0">
                            {% if mostUsedComposer %}
                                {{ mostUsedComposer.usage_count }}
                            {% else %}
                                0
                            {% endif %}
                        </h3>
                        <p class="mb-0">Most Popular</p>
                        <small>
                            {% if mostUsedComposer %}
                                {{ mostUsedComposer.name }}
                            {% else %}
                                None yet
                            {% endif %}
                        </small>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            {# Recent Sheets #}
            <div class="col-md-6 mb-4">
                <div class="card">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">
                            <i class="fa fa-music"></i> Recent Sheets
                        </h5>
                    </div>
                    <div class="card-body">
                        {% if recentSheets|length > 0 %}
                            <div class="list-group list-group-flush">
                                {% for sheet in recentSheets %}
                                    <a href="{{ ea_url()
                                        .setController('App\\Controller\\Admin\\SheetCrudController')
                                        .setAction('detail')
                                        .setEntityId(sheet.id) }}"
                                       class="list-group-item list-group-item-action">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <strong>{{ sheet.title }}</strong>
                                                {% if sheet.composer %}
                                                    <br>
                                                    <small class="text-muted">{{ sheet.composer.name }}</small>
                                                {% endif %}
                                            </div>
                                            <small class="text-muted">
                                                {{ sheet.createdAt|date('Y-m-d') }}
                                            </small>
                                        </div>
                                    </a>
                                {% endfor %}
                            </div>
                        {% else %}
                            <p class="text-muted">No sheets yet. Create your first sheet to get started.</p>
                        {% endif %}
                    </div>
                </div>
            </div>

            {# Upcoming Setlists #}
            <div class="col-md-6 mb-4">
                <div class="card">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">
                            <i class="fa fa-calendar"></i> Upcoming Events
                        </h5>
                    </div>
                    <div class="card-body">
                        {% if upcomingSetlists|length > 0 %}
                            <div class="list-group list-group-flush">
                                {% for setlist in upcomingSetlists %}
                                    <a href="{{ ea_url()
                                        .setController('App\\Controller\\Admin\\SetlistCrudController')
                                        .setAction('detail')
                                        .setEntityId(setlist.id) }}"
                                       class="list-group-item list-group-item-action">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <strong>{{ setlist.name }}</strong>
                                                {% if setlist.occasion %}
                                                    <br>
                                                    <small class="text-muted">{{ setlist.occasion }}</small>
                                                {% endif %}
                                            </div>
                                            <div class="text-end">
                                                {% if setlist.eventDate %}
                                                    <strong>{{ setlist.eventDate|date('M d') }}</strong>
                                                {% endif %}
                                                <br>
                                                <span class="badge bg-{{ setlist.status == 'finalized' ? 'success' : 'warning' }}">
                                                    {{ setlist.status|title }}
                                                </span>
                                            </div>
                                        </div>
                                    </a>
                                {% endfor %}
                            </div>
                        {% else %}
                            <p class="text-muted">No upcoming events scheduled.</p>
                        {% endif %}
                    </div>
                </div>
            </div>
        </div>

        {# Quick Actions #}
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">
                            <i class="fa fa-bolt"></i> Quick Actions
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="d-flex gap-2 flex-wrap">
                            <a href="{{ ea_url().setController('App\\Controller\\Admin\\SheetCrudController').setAction('new') }}"
                               class="btn btn-primary">
                                <i class="fa fa-plus"></i> Add New Sheet
                            </a>
                            <a href="{{ ea_url().setController('App\\Controller\\Admin\\SetlistCrudController').setAction('new') }}"
                               class="btn btn-success">
                                <i class="fa fa-plus"></i> Create Setlist
                            </a>
                            <a href="{{ ea_url().setController('App\\Controller\\Admin\\PersonCrudController').setAction('new') }}"
                               class="btn btn-info">
                                <i class="fa fa-plus"></i> Add Composer
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
{% endblock %}
```

**Acceptance Criteria**:
- Dashboard displays relevant statistics
- Statistics respect organization boundaries
- Most used composer calculated correctly
- Recent sheets displayed
- Upcoming setlists displayed
- Quick action buttons work
- Professional appearance

**Deliverables**:
- Enhanced DashboardController
- `templates/admin/dashboard.html.twig`

---

### Story 11.3: Implement Custom Repository Queries

**Description**: Create custom repository methods for specific data queries.

**Tasks**:
- [ ] Unused sheets query
- [ ] Most used composer query
- [ ] Sheets by difficulty and genre query
- [ ] Test queries with various data

**Technical Details**:

**Sheet Repository** (`src/Repository/SheetRepository.php`):
```php
<?php

namespace App\Repository;

use App\Entity\Sheet;
use App\Entity\Organization;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class SheetRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Sheet::class);
    }

    /**
     * Find sheets that have never been added to any setlist
     */
    public function findUnusedSheets(Organization $organization): array
    {
        return $this->createQueryBuilder('s')
            ->leftJoin('s.setlistItems', 'si')
            ->where('si.id IS NULL')
            ->andWhere('s.organization = :organization')
            ->setParameter('organization', $organization)
            ->orderBy('s.title', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find sheets by difficulty and genre
     */
    public function findByDifficultyAndGenre(
        string $difficulty,
        string $genre,
        Organization $organization
    ): array {
        $qb = $this->createQueryBuilder('s')
            ->where('s.organization = :organization')
            ->setParameter('organization', $organization);

        if ($difficulty) {
            $qb->andWhere('s.difficulty = :difficulty')
                ->setParameter('difficulty', $difficulty);
        }

        if ($genre) {
            $qb->andWhere('s.genre LIKE :genre')
                ->setParameter('genre', '%' . $genre . '%');
        }

        return $qb->orderBy('s.title', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find most popular sheets (most used in setlists)
     */
    public function findMostPopularSheets(Organization $organization, int $limit = 10): array
    {
        return $this->createQueryBuilder('s')
            ->select('s', 'COUNT(si.id) as HIDDEN usage_count')
            ->leftJoin('s.setlistItems', 'si')
            ->where('s.organization = :organization')
            ->setParameter('organization', $organization)
            ->groupBy('s.id')
            ->orderBy('usage_count', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }
}
```

**Person Repository** (`src/Repository/PersonRepository.php`):
```php
<?php

namespace App\Repository;

use App\Entity\Person;
use App\Entity\Organization;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class PersonRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Person::class);
    }

    /**
     * Find composer whose sheets are most used in setlists
     */
    public function findMostUsedComposer(Organization $organization): ?array
    {
        return $this->createQueryBuilder('p')
            ->select('p.name', 'COUNT(si.id) as usage_count')
            ->join('p.sheetsComposed', 's')
            ->join('s.setlistItems', 'si')
            ->where('p.organization = :organization')
            ->setParameter('organization', $organization)
            ->groupBy('p.id')
            ->orderBy('usage_count', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Find composers with no sheets
     */
    public function findComposersWithoutSheets(Organization $organization): array
    {
        return $this->createQueryBuilder('p')
            ->leftJoin('p.sheetsComposed', 's')
            ->where('s.id IS NULL')
            ->andWhere('p.organization = :organization')
            ->andWhere('p.type IN (:types)')
            ->setParameter('organization', $organization)
            ->setParameter('types', ['composer', 'both'])
            ->orderBy('p.name', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
```

**Acceptance Criteria**:
- All custom queries work correctly
- Queries respect organization boundaries
- Performance is acceptable
- Results are accurate

**Deliverables**:
- Custom repository methods
- Tested queries

---

### Story 11.4: Add Global Search Functionality

**Description**: Implement search across multiple fields including JSON fields.

**Tasks**:
- [ ] Configure search fields in CRUD controllers
- [ ] Add search by reference codes (JSON field)
- [ ] Test search with various queries
- [ ] Ensure performant search

**Technical Details**:

**Sheet CRUD Controller**:
```php
public function configureCrud(Crud $crud): Crud
{
    return $crud
        ->setEntityLabelInSingular('Sheet')
        ->setEntityLabelInPlural('Sheets')
        ->setSearchFields(['title', 'genre', 'notes', 'composer.name', 'arranger.name'])
        ->setDefaultSort(['title' => 'ASC'])
        ->setPaginatorPageSize(30);
}
```

**Custom Search Including JSON Field** (`src/Doctrine/Extension/SearchExtension.php`):
```php
<?php

namespace App\Doctrine\Extension;

use Doctrine\ORM\QueryBuilder;

class SearchExtension
{
    public function applySearchToQueryBuilder(
        QueryBuilder $queryBuilder,
        string $searchQuery,
        array $searchableFields
    ): void {
        if (empty($searchQuery)) {
            return;
        }

        $alias = $queryBuilder->getRootAliases()[0];
        $orX = $queryBuilder->expr()->orX();

        // Search regular fields
        foreach ($searchableFields as $field) {
            $orX->add($queryBuilder->expr()->like("$alias.$field", ':search'));
        }

        // Search JSON field (references)
        $orX->add("JSON_SEARCH($alias.references, 'one', :search) IS NOT NULL");

        $queryBuilder
            ->andWhere($orX)
            ->setParameter('search', '%' . $searchQuery . '%');
    }
}
```

**Acceptance Criteria**:
- Search works across multiple fields
- Search includes composer and arranger names
- Search includes reference codes from JSON
- Results are relevant
- Search is performant

**Deliverables**:
- Configured search fields
- Optional custom search extension

---

### Story 11.5: Optimize Database Queries

**Description**: Add indexes and optimize queries for performance.

**Tasks**:
- [ ] Add database indexes
- [ ] Implement eager loading for associations
- [ ] Add query result caching where appropriate
- [ ] Test performance improvements

**Technical Details**:

**Database Indexes** (in entity annotations):
```php
// Sheet entity
#[ORM\Index(name: 'idx_sheet_status', columns: ['status'])]
#[ORM\Index(name: 'idx_sheet_difficulty', columns: ['difficulty'])]
#[ORM\Index(name: 'idx_sheet_genre', columns: ['genre'])]
#[ORM\Index(name: 'idx_sheet_organization', columns: ['organization_id'])]
class Sheet
{
    // ...
}

// Setlist entity
#[ORM\Index(name: 'idx_setlist_status', columns: ['status'])]
#[ORM\Index(name: 'idx_setlist_event_date', columns: ['event_date'])]
#[ORM\Index(name: 'idx_setlist_organization', columns: ['organization_id'])]
class Setlist
{
    // ...
}
```

**Eager Loading** (in CRUD controllers):
```php
use Doctrine\ORM\QueryBuilder;

public function createIndexQueryBuilder(
    SearchDto $searchDto,
    EntityDto $entityDto,
    FieldCollection $fields,
    FilterCollection $filters
): QueryBuilder {
    $queryBuilder = parent::createIndexQueryBuilder($searchDto, $entityDto, $fields, $filters);

    // Eager load associations to avoid N+1 queries
    $queryBuilder
        ->leftJoin('entity.composer', 'composer')
        ->addSelect('composer')
        ->leftJoin('entity.arranger', 'arranger')
        ->addSelect('arranger')
        ->leftJoin('entity.organization', 'organization')
        ->addSelect('organization');

    return $queryBuilder;
}
```

**Query Caching** (for dashboard):
```php
// In DashboardController
$stats = $entityManager->createQuery('...')
    ->useResultCache(true, 3600, 'dashboard_stats_' . $organization->getId())
    ->getResult();
```

**Acceptance Criteria**:
- Database indexes created
- Queries use eager loading where appropriate
- Dashboard queries cached
- Performance improved measurably
- No N+1 query problems

**Deliverables**:
- Database indexes
- Optimized query builders
- Query caching configuration

---

## Epic Acceptance Criteria

- [ ] Export with filters working
- [ ] Dashboard with statistics and widgets
- [ ] Custom repository queries implemented
- [ ] Global search working
- [ ] Database optimized with indexes
- [ ] Performance acceptable
- [ ] All features respect multi-tenancy
- [ ] Professional user experience

---

## Testing Checklist

```bash
# Export
- [ ] Export with no filters
- [ ] Export with single filter
- [ ] Export with multiple filters
- [ ] CSV file has correct data
- [ ] File downloads properly

# Dashboard
- [ ] Statistics display correctly
- [ ] Most used composer calculated correctly
- [ ] Recent sheets show (up to 5)
- [ ] Upcoming setlists show (up to 5)
- [ ] Quick actions work

# Custom Queries
- [ ] Unused sheets query returns correct results
- [ ] Most popular sheets query works
- [ ] Most used composer query works
- [ ] Queries respect organization

# Search
- [ ] Search by title works
- [ ] Search by composer works
- [ ] Search by reference code works
- [ ] Search results are relevant

# Performance
- [ ] Dashboard loads quickly
- [ ] List pages load quickly
- [ ] No N+1 query problems
- [ ] Database queries optimized
```

---

## Deliverables

- [ ] Export action in SheetCrudController
- [ ] Enhanced DashboardController
- [ ] `templates/admin/dashboard.html.twig`
- [ ] Custom repository methods
- [ ] Configured search fields
- [ ] Database indexes
- [ ] Optimized query builders
- [ ] Query caching
- [ ] Git tag: `step-4-complete`

---

## Git Tagging

```bash
git add .
git commit -m "Epic 11: Advanced features complete (walkthrough ready)"
git tag -a step-4-complete -m "Walkthrough starting point: All features implemented"
git push origin epic/11-advanced --tags
```

**This tag marks the starting point for walkthrough demonstrations!** 🟢

---

## Notes

- These advanced features showcase the full power of EasyAdmin
- Export with filters is a commonly requested feature
- Dashboard provides great UX and overview
- Custom queries demonstrate Doctrine proficiency
- Performance optimization is crucial for production apps
- This completes all features needed for the talk

---

## Next Epic

**Epic 12**: Demo Data & Testing
