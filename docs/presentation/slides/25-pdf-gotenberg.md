---
layout: two-cols
---

# PDF generation

<div class="mt-2">
  <div class="text-md font-semibold uppercase tracking-wide opacity-40 mb-2">Problem</div>
  <p class="text-md opacity-80">Two PDF actions on a setlist: generate a formatted sheet from its data, or merge all the attached score PDFs into one file.</p>
  <div v-click="1" class="text-md font-semibold uppercase tracking-wide opacity-40 mb-2">Solution</div>
  <v-clicks>

  - **Gotenberg** — headless Chrome as a service, via `sensiolabs/gotenberg-bundle`
  - Generate: streams a PDF from a Twig template
  - Merge: streams a merged PDF from setlist sheets filepath

  </v-clicks>
</div>

::right::

<div class="mt-[100px]" v-click="1">

````md magic-move {at:2}
```yaml 
###> sensiolabs/gotenberg-bundle ###
  gotenberg:
    image: 'gotenberg/gotenberg:8'
###< sensiolabs/gotenberg-bundle ###
```

```php {2-9,15,16}
// GenerateSetlistPdfController — Twig template → PDF
#[AdminRoute('/setlist/{id}/pdf', name: '...')]
public function __invoke(Setlist $setlist): Response
{
    return $this->gotenberg->html()
        ->content(
                'admin/pdf/setlist.html.twig', 
                ['setlist' => $setlist]
        )
        ->fileName(
            sprintf('setlist-%s', $setlist->getId()),
            HeaderUtils::DISPOSITION_ATTACHMENT
        )
        ->generate()
        ->stream(); // StreamedResponse
}
```

```php {2-4,7-10,13,14,19,20}
// MergeSetlistSheetsPdfController — merge existing PDFs
#[AdminRoute('/setlist/{id}/merge-pdf', name: '...')]
public function __invoke(Setlist $setlist): Response
{
    $paths = [];
    foreach ($setlist->getItems() as $item) {
        foreach ($item->getSheet()?->getFiles() as $file){
            $path = $this->storage->absolutePath($file);
            if (is_file($path)) $paths[] = $path;
        }
    }

    return $this->gotenberg->merge()
        ->files(...$paths)
        ->fileName(
            sprintf('partitions-%s', $setlist->getId()),
            HeaderUtils::DISPOSITION_ATTACHMENT)
        ->generate()
        ->stream(); //StreamedResponse
}
```
````

</div>

<div class="mt-3" v-click="1" v-click.hide="2">

````md magic-move {at:2}
```json 
{
    "require": {
        ...
        "sensiolabs/gotenberg-bundle": "^1.2",
        ...
    },
}
```
````
</div>
<!--
Gotenberg runs as a sidecar Docker container — the bundle talks to it over HTTP. html() sends a rendered Twig template to headless Chrome and gets a PDF back. merge() sends multiple local PDF files and gets a single merged PDF back. Both cases end with .generate().stream(), which pipes the Gotenberg response body straight into a Symfony StreamedResponse — no temp file, no memory spike. Both controllers follow the same simple action pattern: one route, one __invoke(), one return.
-->
