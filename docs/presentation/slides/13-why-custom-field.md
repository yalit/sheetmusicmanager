---
layout: default
---

# Why a custom field?

`Sheet.files` stores `StoredFile[]` — a value object serialized as JSON. EA has no built-in field for it.

<div class="grid grid-cols-2 gap-6 mt-8">

<div v-click class="p-5">
  <div class="text-xs font-semibold uppercase tracking-wide opacity-40 mb-1">Problem</div>
  <div class="font-bold text-lg mb-3">1. List & detail</div>
  <div class="text-md opacity-80 text-center">
    EA sees an array — renders nothing useful
  </div>
  <div class="text-xs font-semibold uppercase tracking-wide opacity-40 mb-1">Solution</div>
  <div class="text-sm text-center">
    A Twig template: loop over files, render download links
  </div>
</div>

<div v-click class="p-5">
  <div class="text-xs font-semibold uppercase tracking-wide opacity-40 mb-1">Problem</div>
  <div class="font-bold text-lg mb-3">2. New & edit forms</div>
  <div class="text-md opacity-80 text-center">
    EA defaults to a text input for the JSON — unusable
  </div>
  <div class="text-xs font-semibold uppercase tracking-wide opacity-40 mb-1">Solution</div>
  <div class="text-sm text-center">
    A file upload input + existing file list with delete
  </div>
</div>

</div>

<!--
StoredFile is a value object with a name and a path, stored as a JSON array. EasyAdmin doesn't know what a StoredFile is, so it can't render it. And the form side is even harder — we need a file input, a list of already-uploaded files the user can delete, and hidden inputs to track what's kept and what's removed. Nothing in EA covers this combination, so we build a custom field.
-->
