---
layout: two-cols
---

# CSV export

<div class="mt-2">
  <div class="text-md font-semibold uppercase tracking-wide opacity-40 mb-2">Problem</div>
  <p class="text-md opacity-80">Export the current filtered sheet list as a downloadable CSV — without writing a temp file to disk.</p>
  <div v-click="1" class="text-md font-semibold uppercase tracking-wide opacity-40 mb-2">Solution</div>
  <v-clicks>

  - Reuses `getIndexQueryBuilder()` — respects active admin filters
  - `CsvExporter` service keeps export logic out of the controller
  - `StreamedResponse` + `fputcsv` — streams directly to the browser

  </v-clicks>
</div>

::right::

<div class="mt-[100px]" v-click="1">

````md magic-move {at:2}
```php {1,2,19,10,11|1,2,19,4-6,13-17}
// SheetCrudController — export current filtered list
class SheetCrudController extends AbstractCrudController 
{
    public function __construct(
        private readonly CSVExporter $exporter
    ) {}
    
    function export($context): StreamedResponse
    {
        $sheets = $this->getIndexQueryBuilder($context)
           ->getQuery()->getResult();

        return $this->csvExporter->stream(
           sprintf('sheets-%s.csv', date('Y-m-d')),
           ['ID', 'Title', ...], //needed headers
           array_map(fn (Sheet $sheet) => [ … ], $sheets),
        );
    }
}
```

```php {1-6,14-20}
// CsvExporter — streams without writing to disk
public function stream(string $filename, 
                        array $headers, 
                        iterable $rows
): StreamedResponse {
    $resp = new StreamedResponse(
        function () use ($headers, $rows) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, $headers);
            foreach ($rows as $row) {
                fputcsv($handle, $row);
            }
            fclose($handle);
    });

    $resp->headers->set('Content-Type', 'text/csv;...');
    $resp->headers->set('Content-Disposition',
        sprintf('attachment; filename="%s"', $filename));

    return $resp;
}
```
````

</div>

<!--
getIndexQueryBuilder() is an EasyAdmin hook that returns the already-filtered DQL query — so the export always matches what the user sees in the list, active filters included. StreamedResponse with php://output avoids buffering the whole result set in memory and never touches the filesystem. CsvExporter is a small service so the controller stays thin and the exporter stays testable in isolation.
-->
