# forScore Integration — User Stories

## Epic: forScore Integration

---

### US-01 — Connect My Sheet Library to forScore

**As a** musician using the app,
**I want to** connect my sheet music library to forScore on my iPad,
**so that** I can access and download my scores directly from within the iOS Files app without having to manually transfer files.

**Acceptance Criteria:**
- I can find my server connection details (URL and credentials) in my account settings
- I can generate and revoke a dedicated access password for this connection, separate from my main account password
- The connection details are clearly presented in a "Connect to forScore" section with step-by-step instructions on how to add the server in the iOS Files app
- Once connected, I can see my sheet library from iOS Files on my iPad

---

### US-02 — Browse My Sheet Library from iOS Files

**As a** musician who has connected the app to iOS Files,
**I want to** browse my sheet music organised the same way as in the app,
**so that** I can find and download individual scores into forScore without losing my familiar organisation.

**Acceptance Criteria:**
- My sheets are browsable by the same categories I use in the app (e.g. composer, collection, instrument)
- Each sheet appears as a PDF file with a clear, readable name
- I can download any individual sheet from iOS Files directly into forScore

---

### US-03 — Browse My Setlists from iOS Files

**As a** musician who has connected the app to iOS Files,
**I want to** see each of my setlists as a folder containing its scores in order,
**so that** I can download all the files I need for a performance in one go without having to search for them individually.

**Acceptance Criteria:**
- Each setlist I've created in the app appears as a folder in iOS Files
- Inside each folder, the scores appear in the same order as in the setlist
- The filenames clearly reflect the order and title of each piece
- I can select all files in a setlist folder and import them into forScore at once
- If I reorder or rename items in a setlist in the app, the folder contents reflect those changes next time I browse

---

### US-04 — Export a Setlist to forScore

**As a** musician who has downloaded a setlist's scores into forScore,
**I want to** export my setlist from the app directly into forScore,
**so that** forScore automatically creates the setlist with the correct order without me having to manually recreate it.

**Acceptance Criteria:**
- Each setlist has an "Export to forScore" button
- Tapping it on my iPad downloads a file that, when opened, automatically creates the setlist in forScore
- The setlist in forScore contains all the pieces in the correct order
- If a score from the setlist hasn't been downloaded into forScore yet, it appears as a placeholder so I know what's missing
- I can re-export a setlist at any time if I've made changes to it in the app
