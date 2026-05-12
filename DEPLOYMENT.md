# LeaveFlow Deployment Guide

This project is a plain PHP and MySQL application. It does not need Composer, Node.js, or a build step.

## Requirements

- PHP 8.0 or newer with the `mysqli` extension enabled
- MySQL or MariaDB
- Apache, Nginx, or a shared-hosting PHP runtime
- A database user with permission to create/import the app tables

## Files to upload

Upload the project files to your hosting web root, usually one of these:

- `public_html/`
- `htdocs/`
- `www/`

Keep the same folder structure:

```text
api/
config/
database/
partials/
approvals.php
calendar.php
dashboard.php
index.php
my-requests.php
```

## Database setup

1. Create a MySQL database, for example `leave_management`.
2. Create a MySQL user and password for the app.
3. Import `database/schema_hosting.sql` into that database if your host already created the database for you. Use `database/schema.sql` only when you can create databases yourself.

In cPanel/phpMyAdmin:

1. Open phpMyAdmin.
2. Select the database.
3. Choose Import.
4. Upload `database/schema_hosting.sql`.
5. Run the import.

## Database configuration

The app reads these environment variables when the host supports them:

```text
DB_HOST
DB_USER
DB_PASS
DB_NAME
```

If your host does not expose environment variables, edit `config/db.php` after upload and replace the fallback values with the hosting database details.

## First login

The seeded demo accounts use this password:

```text
password123
```

Demo users:

- `manager@company.com`
- `alex@company.com`
- `priya@company.com`

Change or remove demo accounts before using the app with real employee data.

## Recommended deployment options

The simplest path is a PHP shared host with cPanel and MySQL, because this app is already structured for that model. VPS hosting also works, but you will need to configure Apache/Nginx, PHP, MySQL, SSL, backups, and security updates yourself.

## Pre-production checklist

- Import the database schema.
- Set the real database credentials.
- Confirm PHP `mysqli` is enabled.
- Enable HTTPS/SSL on the domain.
- Change demo passwords or replace demo users.
- Remove visible demo-account hints from `index.php` if this will be used publicly.
- Back up the database regularly.

## Quick smoke test

After deployment:

1. Open the site domain.
2. Log in as `manager@company.com` with `password123`.
3. Confirm the dashboard loads leave balances.
4. Open Approvals and Calendar.
5. Log out.
6. Log in as `alex@company.com`.
7. Submit a future leave request.
