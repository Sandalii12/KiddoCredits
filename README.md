# KiddoCredits

🎯 **KiddoCredits – Project Overview**

> KiddoCredits is a web-based task and reward management system for parents and children. Parents assign tasks, award points, and add rewards; children complete tasks, earn points and redeem rewards.

This project is built with PHP (backend), MySQL (database) and HTML/CSS/JavaScript for the frontend. It is intended to run on a local XAMPP stack or any PHP+MySQL environment.

---

## 🌟 Key Features

### Parent

- Parent signup & login
- Create and manage child accounts
- Assign tasks with title, due date, points and target child
- View task lists (pending / completed / expired)
- Approve/complete tasks and award points
- Add and manage rewards in a reward catalogue
- Redeem/assign rewards to children
- Dashboard with counts and summaries

### Child

- Child login
- View assigned tasks and mark them complete
- Track earned points and view history
- Browse reward catalogue and redeem rewards

---

## 🗂 Project Structure

```
KiddoCredits/
│── assets/         → logos, images
│── css/            → global and page-specific styles
│── js/             → frontend scripts (sidecard, tasks, children)
│── includes/       → db connection, header/footer, auth session
│── auth/           → login, logout, signup pages
│── parent/         → parent dashboard pages (children, tasks, reward_list, dashboard)
│── child/          → child dashboard and related pages
│── index.php       → entry point
│── KiddoCredits.sql → database dump (schema + sample data)
│── README.md       → this file
```

---

## 🛢 Database

The project expects a MySQL database. A SQL dump is included as `KiddoCredits.sql` in the project root. The main tables used by the application are:

1. `Parent`  — parent accounts
2. `Child`   — child accounts linked to parents
3. `Task`    — tasks assigned to children
4. `Reward`  — rewards available to redeem

Each table contains `created_at` (and typically `updated_at`) timestamp fields for bookkeeping.

Import the SQL file using phpMyAdmin or the MySQL CLI (example):

```bash
# using MySQL CLI (adjust user/password/dbname)
mysql -u root -p your_database_name < KiddoCredits.sql
```

Or open phpMyAdmin, create a database, then use Import → choose `KiddoCredits.sql`.

---

## 💻 Local development / Run (XAMPP)

1. Install XAMPP (Apache + MySQL + PHP).
2. Copy the `KiddoCredits` folder into XAMPP's `htdocs` directory (e.g. `C:\xampp\htdocs\KiddoCredits`).
3. Start Apache and MySQL from the XAMPP control panel.
4. Import `KiddoCredits.sql` into MySQL (see Database section).
5. Update database credentials if necessary in `includes/db_connection.php` (DB host, username, password, database name).
6. Open your browser and visit: `http://localhost/KiddoCredits/` (or the correct path on your local server).

Notes:
- The app uses a universal sidecard UI (see `includes/header_parent.php` and `js/sidecard.js`) for add/update forms in the Parent dashboard.
- If you change file locations or DB credentials, update `includes/db_connection.php` accordingly.

---

## 🧰 Tools & Tech

- PHP 8+
- MySQL
- HTML5 / CSS3 / JS
- XAMPP for local development
- Git & GitHub for version control

---

## 🧩 Development notes

- Frontend behaviour for sidecards is provided by `js/sidecard.js` (open/close, focus, backdrop click-to-close).
- Parent pages (tasks, children, reward_list) wire into the universal SideCard via small page-specific scripts.
- Server-side form handling (add/update/delete) is implemented in each parent page PHP file (e.g. `parent/tasks.php`, `parent/reward_list.php`) and expects the standard form field names described in the UI.

---

## ✅ Contributing / Next steps

- Clean up any remaining unused JS/CSS files before publishing.
- Add automated tests and basic input validation on both client and server sides.
- Consider adding role-based access controls and stronger input sanitization.



<!-- PROJECT STRUCTURE
KiddoCredits/
│
├── assets/
│   ├── logo.png
│   └── icons/           (optional)
│
├── css/
│   ├── style.css
│   ├── parent.css
│   ├── child.css
│   └── login.css
│
├── js/
│   ├── main.js
│   ├── timer.js         (countdown for child tasks)
│   └── validation.js
│
├── includes/
│   ├── db_connection.php
│   ├── header_parent.php
│   ├── header_child.php
│   ├── footer.php
│   └── auth_session.php   (to check login session)
│
├── parent/
│   ├── dashboard.php
│   ├── add_child.php
│   ├── task_assign.php
│   ├── task_list.php
│   ├── reward_add.php
│   ├── reward_list.php
│   └── logout.php
│
├── child/
│   ├── dashboard.php
│   ├── tasks.php
│   ├── completed_tasks.php
│   ├── reward_catalogue.php
│   └── logout.php
│
├── auth/
│   ├── login.php
│   ├── signup_parent.php
│   └── logout.php
│
└── index.php -->
