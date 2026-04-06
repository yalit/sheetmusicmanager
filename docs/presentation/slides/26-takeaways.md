---
layout: center
---

# EasyAdmin is extensible — at every layer

<div class="mt-6 grid grid-cols-2 gap-x-12 gap-y-3 text-sm">

<div v-click>
  <span class="font-semibold">1. Basic CRUD</span><br/>
  <span class="opacity-60">AbstractCrudController + hook methods</span>
</div>

<div v-click>
  <span class="font-semibold">2. Security & roles</span><br/>
  <span class="opacity-60">VoterInterface + configureActions()</span>
</div>

<div v-click>
  <span class="font-semibold">3. Custom filters</span><br/>
  <span class="opacity-60">FilterInterface + FilterTrait + one DQL condition</span>
</div>

<div v-click>
  <span class="font-semibold">4. Custom actions</span><br/>
  <span class="opacity-60">Action + Controller + Handler — simple and testable</span>
</div>

<div v-click>
  <span class="font-semibold">5. Custom fields</span><br/>
  <span class="opacity-60">Field + FormType + Twig blocks</span>
</div>

<div v-click>
  <span class="font-semibold">6. Inline collections</span><br/>
  <span class="opacity-60">CollectionTableField — table rendering + JS add/delete/sort</span>
</div>

<div v-click class="col-span-2">
  <span class="font-semibold">7. PDF & export</span><br/>
  <span class="opacity-60">StreamedResponse for CSV — Gotenberg for HTML→PDF and merge</span>
</div>

</div>

<!--
Each of these builds on the previous one. The patterns are consistent: hook into EasyAdmin at the right layer, keep business logic out of the controller, return a response. The framework does the scaffolding; you fill in the gaps.
-->
