---
transition: fade-out
layout: two-cols
---

# What we're building

A **sheet music manager** for choirs, bands, and orchestras.

<v-clicks>

- Manage sheets (PDFs, tags, refs, credits)
- Manage persons (composers, arrangers)
- Build setlists and reorder items
- Role-based access for 4 types of users
- PDF generation and export

</v-clicks>

::right::

<div class="mt-[110px]" v-click="1">

````md magic-move {at:2}
``` {*}
Sheet
  ├── title, refs, tags, notes
  ├── files (PDFs)
```

``` {1,4-6}
Sheet
  ├── title, refs, tags, notes
  ├── files (PDFs)
  └── credits → CreditedPerson
                  ├── Person
                  └── PersonType
```

``` {1,8-10}
Sheet
  ├── title, refs, tags, notes
  ├── files (PDFs)
  └── credits → CreditedPerson
                  ├── Person
                  └── PersonType

Setlist
  └── items → SetListItem
                  └── Sheet
```

``` {12-13|*}
Sheet
  ├── title, refs, tags, notes
  ├── files (PDFs)
  └── credits → CreditedPerson
                  ├── Person
                  └── PersonType

Setlist
  └── items → SetListItem
                  └── Sheet

Member (user)
  └── role: member > contributor > librarian > admin
```
````

</div>

<!--
The app is realistic enough to hit non-trivial EasyAdmin patterns — not just a blog with posts and comments.
-->
