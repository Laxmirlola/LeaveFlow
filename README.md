# LeaveFlow

LeaveFlow is a lightweight employee leave management system built with plain PHP and MySQL. It lets employees request leave, track their balances, and view approved leave on a calendar, while managers can review requests and approve or reject them.

The project is intentionally simple: there is no framework, package manager, or build step. Upload it to a PHP/MySQL host, import the database schema, set the database credentials, and it is ready to run.

## Live Demo

[https://leaveflow.42web.io/](https://leaveflow.42web.io/)

Demo credentials:

```text
Manager:  manager@company.com / password123
Employee: alex@company.com / password123
```

Change or remove the demo users before using this application with real employee data.
<img width="1919" height="853" alt="image" src="https://github.com/user-attachments/assets/964f527b-e69a-4e1d-a44c-6f82619c957b" />


## Features

- Employee and manager login
- Role-based navigation
- Employee leave balance dashboard
- Leave request creation with working-day calculation
- Overlap and balance validation
- Manager approval and rejection workflow
- Leave request history and status filters
- Monthly approved-leave calendar
- JSON API endpoints for dashboard data
- MySQL seed data for quick setup

## Tech Stack

- PHP 8+
- MySQL or MariaDB
- HTML, CSS, and vanilla JavaScript
- PHP `mysqli` extension

## Project Structure

```text
api/                  JSON API endpoints
config/               Database and authentication helpers
database/             SQL schema files
partials/             Shared PHP layout
approvals.php         Manager approval screen
calendar.php          Leave calendar
dashboard.php         Main dashboard
index.php             Login page
my-requests.php       Employee request history
DEPLOYMENT.md         Deployment notes
```

## Requirements

- PHP 8.0 or newer
- MySQL or MariaDB
- Apache, Nginx, XAMPP, or shared PHP hosting
- Enabled PHP `mysqli` extension

## Local Setup With XAMPP

1. Copy the project folder into your XAMPP `htdocs` directory.
2. Start Apache and MySQL from the XAMPP Control Panel.
3. Open phpMyAdmin.
4. Import `database/schema.sql`.
5. Visit:

```text
http://localhost/leave_system/
```

The default local database settings are:

```text
Host: localhost
User: root
Password:
Database: leave_management
```

## Database Configuration

Database settings are defined in `config/db.php`. The app reads environment variables first, then falls back to local XAMPP defaults:

```text
DB_HOST
DB_USER
DB_PASS
DB_NAME
```

For shared hosting, either configure these environment variables if the host supports them, or update the fallback values in `config/db.php` after upload.

## Deployment

For hosts that let you create databases yourself, import:

```text
database/schema.sql
```

For shared hosts that create the database for you, import:

```text
database/schema_hosting.sql
```

Then upload the PHP files to the web root, usually `public_html`, `htdocs`, or `www`.

More details are in `DEPLOYMENT.md`.

## Security Notes

This is a demo-friendly project and should be hardened before production use:

- Replace the demo users and passwords.
- Remove the visible demo-account hint from `index.php`.
- Use HTTPS.
- Keep database credentials out of version control.
- Do not leave SQL import files publicly accessible on a live server.
- Add CSRF protection before using this for sensitive workflows.
- Restrict manager account creation to trusted administrators.

## API Overview

```text
POST  api/login.php        Log in
GET   api/logout.php       Log out
GET   api/balances.php     Current user's leave balances
GET   api/leave_types.php  Available leave types
GET   api/requests.php     Leave requests
POST  api/requests.php     Create a leave request
PATCH api/requests.php     Manager approval/rejection
GET   api/calendar.php     Approved leave events by month
```

## License

This project is available for learning, customization, and deployment. Add a formal license file if you plan to distribute it publicly.
