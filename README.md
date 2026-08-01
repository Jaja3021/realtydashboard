# Realty Dashboard

A real estate sales dashboard: analytics overview, a form for logging sold
properties, and a monthly top-sales leaderboard by agent.

## Stack

PHP + MySQL. No frameworks, no build step.

## Local setup (XAMPP)

1. Copy this folder into `htdocs/` (or run PHP's built-in server from the
   project root instead — see note below).
2. Import the schema: `mysql -u root < db.sql` (or import `db.sql` via
   phpMyAdmin). This creates the `realty_dashboard` database and seeds a few
   sample rows.
3. `config/db.php` defaults to `root` with no password on `localhost`,
   matching XAMPP's defaults — no changes needed for local use.
4. Visit the site through Apache, or for a setup that matches production
   exactly, run from the project root:
   ```
   php -S localhost:8000
   ```
   (All internal links are root-relative, so serving from a subfolder like
   `htdocs/realtydashboard/` under Apache will break CSS/nav — use a vhost
   pointed at this folder, or the built-in server above, instead.)

## Deploying to Railway

Railway runs PHP + MySQL directly, so no code changes are needed.

1. Push this repo to GitHub (already done if you're reading this from the repo).
2. On [railway.app](https://railway.app), create a new project → **Deploy from GitHub repo** → select this repo.
3. Add a **MySQL** plugin to the project (`+ New` → `Database` → `MySQL`).
   Railway automatically injects `MYSQLHOST`, `MYSQLPORT`, `MYSQLDATABASE`,
   `MYSQLUSER`, `MYSQLPASSWORD` into your app's environment — `config/db.php`
   already reads these, falling back to local XAMPP defaults when absent.
4. Import the schema into the Railway MySQL database: open its **Data** tab
   (or connect via the provided connection string with a MySQL client) and
   run the contents of `db.sql`.
5. Once deployed, Railway gives you a public URL — the app serves from the
   domain root, matching the root-relative links already in the code.
