Let me read your CSV data first.Good — 49 ties, clean data. 2 currently on loan (Wikson, Julius Ho). Now let me read the skill files before building.Good. Now let me build everything. This is a big build — I'll create all files systematically.Now build the main owner app `index.html`:Now build the public gallery:Now copy the icon and zip everything up:Everything is built. Here's your complete step-by-step guide.

---

## What's in the zip

| File | Purpose |
|---|---|
| `index.html` | Your private app (login required) |
| `gallery.html` | Public page for friends |
| `api/config.php` | **Edit this first** — your DB credentials |
| `api/auth.php` | Magic link login |
| `api/ties.php` | Ties CRUD |
| `api/photos.php` | Photo upload |
| `api/loans.php` | Loan tracking |
| `api/requests.php` | Borrow requests + notifications |
| `setup_database.sql` | Creates all tables + imports your 49 ties |
| `sw.js` + `manifest.json` | PWA (installable on phone) |
| `.htaccess` | Server config |

---

## Step-by-Step Deployment Guide

### STEP 1 — Create MySQL Database in cPanel

1. Log in to cPanel → scroll to **Databases** section → click **MySQL Databases**
2. Under "Create New Database" → type `myties` → click **Create Database**
3. Under "MySQL Users" → Create new user: username `tieuser`, set a strong password → **Create User**
4. Under "Add User to Database" → select your new user + database → **Add** → tick **All Privileges** → **Make Changes**
5. Write down: database name (e.g. `tektwv_myties`), username (e.g. `tektwv_tieuser`), password

---

### STEP 2 — Run the SQL (Import Your 49 Ties)

1. In cPanel → **phpMyAdmin**
2. Click your database name on the left
3. Click the **SQL** tab at the top
4. Open `setup_database.sql`, select all, paste into the box → click **Go**
5. You'll see green ticks — all 49 ties imported ✅

---

### STEP 3 — Edit config.php

Open `config.php` and fill in your 4 values:

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'tektwv_myties');    // your actual DB name
define('DB_USER', 'tektwv_tieuser');   // your actual user
define('DB_PASS', 'YourPassword');      // your actual password
```

Save the file.

---

### STEP 4 — Upload to cPanel

1. cPanel → **File Manager** → navigate to `public_html/myties/`
2. Click **Upload** → upload the entire zip `myties_deploy.zip`
3. Once uploaded, right-click the zip → **Extract** → extract to `public_html/myties/`
4. Make sure these folders exist: `public_html/myties/api/` and create `public_html/myties/uploads/` (set permissions to **755**)
5. Replace the `config.php` inside `api/` with your edited version

---

### STEP 5 — GitHub (Save Your Work)

1. Go to **github.com** → Sign Up → create account
2. Click **+** → **New repository** → name it `myties` → Public → **Create**
3. Click **Add file** → **Upload files** → drag in all files from the zip (or upload zip and extract)
4. Click **Commit changes** — your code is now safely backed up

Every time you update something, upload the changed file to both GitHub and cPanel.

---

### STEP 6 — Test It

1. Visit `https://myties.tektwv.my` — you should see the login screen
2. Enter `jeremyjohnson78@gmail.com` → click **Send login link**
3. Check your email → click the link → you're in! 🎉
4. All 49 ties should be there (no photos yet — add those through the app)
5. Visit `https://myties.tektwv.my/gallery.html` — your friends' view
6. Share that gallery link with a friend to test the request flow

---

### STEP 7 — Add Your Tie Photos

Since photos can't be exported from your old Samsung app automatically:
1. On your Samsung, open your old tie app → go through each tie → long-press the photo → Save to Gallery
2. In your new app, tap Edit on each tie → Upload photo → select from gallery
3. Photos now live on your cPanel server permanently

---

**One thing to check:** cPanel sometimes names your database as `yourusername_myties` (e.g. `tektwv_myties`). Double-check the exact name shown after you create it — use that exact name in `config.php`.
