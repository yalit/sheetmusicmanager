# forScore Integration — Technical User Stories

## Epic: forScore Integration

### Implementation Order
1. TUS-01 — Authentication foundation first, everything else depends on it
2. TUS-02 — Sheet library tree, establishes the SabreDAV infrastructure
3. TUS-03 — Setlist tree, builds on top of TUS-02's infrastructure
4. TUS-04 — The .4SS exporter, shares the filename service from TUS-03
5. TUS-05 — The UI, once the backend is fully working

---

### TUS-01 — WebDAV Server Setup & Authentication

**Architecture:**
A dedicated WebDAV endpoint is added to the Symfony application, separate from the main application routing. It handles all WebDAV protocol communication and authenticates requests independently from the main session-based authentication system.

**Key Decisions:**
- SabreDAV library is integrated as the WebDAV server implementation
- A new Symfony firewall is configured exclusively for the `/dav` route prefix, accepting HTTP Basic Auth
- A dedicated `WebDAV Access Token` credential is introduced per user — a randomly generated token stored in the database, used as the password for Basic Auth, completely separate from the user's main account password
- The user's email address is used as the username for the WebDAV connection
- Token generation, display, and revocation are managed through a new section in the user's account settings
- The Symfony security system validates incoming Basic Auth credentials against the stored token before any WebDAV request is processed

**Entities / Concepts Introduced:**
- `WebDavToken` — belongs to a `Member`, stores a hashed access token, a creation date, and an expiration date; one active token per user at a time; the token can be recycled (new secret generated in-place, expiry reset) or revoked (deleted) from the UI

---

### TUS-02 — WebDAV Virtual Filesystem: Sheet Library

**Architecture:**
A virtual filesystem tree is built on top of SabreDAV's collection and file abstractions. No files are moved or copied on disk — the tree is constructed dynamically from the existing database entities at request time.

**Key Decisions:**
- A `SheetsRootDirectory` node serves as the entry point for the `/dav/sheets/` branch of the tree
- The folder structure mirrors the organisational categories already existing in the application (e.g. composer, collection, instrument) — these become virtual subdirectories
- Each sheet at the leaf level is represented as a virtual file node that streams the PDF content directly from its stored location when requested
- Only `PROPFIND` and `GET` verbs are required for this branch — the tree is read-only from the WebDAV side since file management stays in the web app
- A dedicated service handles filename sanitisation to ensure valid, readable filenames across all operating systems

**Entities / Concepts Used:**
- Existing `Sheet`, `Composer`, `Collection` (or equivalent) entities are the data source
- No new entities required

---

### TUS-03 — WebDAV Virtual Filesystem: Setlists

**Architecture:**
A second branch of the WebDAV tree exposes setlists as folders, each containing the ordered scores of that setlist as virtual PDF files. This branch shares the same file streaming infrastructure as the sheet library branch but applies setlist-specific ordering and naming.

**Key Decisions:**
- A `SetlistsRootDirectory` node serves as the entry point for the `/dav/setlists/` branch
- Each setlist is represented as a virtual directory named after the setlist
- Each entry within a setlist directory is a virtual file node with a filename prefixed by its position in the setlist (e.g. `01 - Title.pdf`, `02 - Title.pdf`) to preserve order when viewed in iOS Files
- The virtual files point to the same underlying PDF as the sheet library — no duplication on disk
- A centralised `ForScoreFilenameGenerator` service is responsible for all filename generation logic, shared between this virtual tree and the .4SS exporter (TUS-04), ensuring filenames are always consistent between the two
- The tree is read-only — setlist management stays in the web app

**Entities / Concepts Used:**
- Existing `Setlist`, `SetlistEntry`, `Sheet` entities are the data source
- `ForScoreFilenameGenerator` service introduced and shared with TUS-04

---

### TUS-04 — Setlist Export to forScore (.4SS)

**Architecture:**
A new export endpoint generates a `.4SS` file on the fly for a given setlist and serves it as a file download. When opened on an iPad, iOS recognises the `.4ss` extension and hands it to forScore, which imports it as a setlist.

**Key Decisions:**
- A new Symfony route is added for each setlist: `GET /setlists/{id}/export/forscore`
- A `ForScoreExporter` service generates the `.4SS` XML structure from the setlist's entries
- Filenames in the generated XML are produced by the same `ForScoreFilenameGenerator` service used in TUS-03, guaranteeing that the filenames in the `.4SS` match exactly what was downloaded via WebDAV
- The response is served with the appropriate content-disposition header to trigger a file download
- If a sheet in the setlist has no associated PDF file, it is exported as a `placeholder` element rather than a `score` element, so forScore still creates the setlist entry without breaking the import
- Access to the export endpoint is restricted to authenticated users who own the setlist

**Entities / Concepts Used:**
- Existing `Setlist`, `SetlistEntry`, `Sheet` entities
- `ForScoreExporter` service introduced
- `ForScoreFilenameGenerator` service shared with TUS-03

---

### TUS-05 — User-Facing Connection Setup UI

**Architecture:**
A new section is added to the user account settings area, dedicated to the forScore integration. It provides everything the user needs to configure the connection on their iPad, including credential management and step-by-step instructions.

**Key Decisions:**
- A new page is added under account settings: "Connect to forScore" or "iOS Files Integration"
- The page displays the WebDAV server URL (fixed, pointing to `/dav`), the username (their email), and the current access token status if one exists
- The token is only displayed once at generation time — after that only its creation date, expiration date, and available actions are shown
- A "Generate Access Password" action creates a new `WebDavToken` for the user (invalidating any previous one)
- A "Recycle" action regenerates the token secret in-place on the existing `WebDavToken` entity, resetting the expiration date — the old secret is immediately invalidated
- A "Revoke Access" action deletes the current token, immediately disabling the WebDAV connection
- Step-by-step instructions for adding the server in iOS Files are displayed inline on the page
- The "Export to forScore" button on each setlist detail page triggers the download from TUS-04 and is only visible when the user is browsing on an iOS device (detected via user agent) or always visible with a note about using it on iPad

---
