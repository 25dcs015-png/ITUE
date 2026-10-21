# StudentHub Portal 🎓

> **A Comprehensive Full-Stack Academic & Campus Management Web Application**  
> Developed for **Web Development Frameworks (ITUE203)** | Semester: **3rd (Odd, 2026–27)**  
> **Faculty of Technology and Engineering (FTE) — Charotar University of Science and Technology (CHARUSAT)**

---

## 📌 1. Project Overview & Scope

**StudentHub Portal** is a unified, secure, and responsive web portal engineered for undergraduate academic institutions. The platform centralizes student records, departmental timetables, course curricula, event announcements, workshop registrations, placement statistics, and role-based administrative workflows.

The project is structured and implemented across **15 progressive practical modules**, fulfilling all core frontend and backend learning outcomes—from semantic HTML5 and CSS Grid/Flexbox design to PHP session security, MySQL database architecture, and asynchronous AJAX/Fetch API CRUD operations.

---

## 👥 2. User Roles & Permission Model (RBAC)

The application enforces strict **Role-Based Access Control (RBAC)** managed through PHP sessions:

| Role | Accessible Pages & Features |
| :--- | :--- |
| **Guest / Public** | • Registration form with client-side & server-side regex validation (`signup.php`)<br>• Secure login with CSRF token verification (`login-form.php`)<br>• Public About Us page & FAQ accordion (`about.html`) |
| **Student** | • Personalized Student Home (`home.php`) with campus photo carousel and quick-access grid<br>• Profile details viewer (`profile.php`)<br>• Campus timetable viewer (`timetable.html`), courses catalog (`courses.html`), and upcoming events list (`events.html`)<br>• Placement statistics (`placement.html`), scholarship details (`scholarship.html`), and workshops (`workshop.html`)<br>• Theme switcher (Light/Dark mode) and account settings (`settings.php`) |
| **Administrator** | • Dedicated Admin Dashboard (`admin-dashboard.php`) with KPI analytics & SQL aggregations<br>• Student Management CRUD with live search, department filters, and MySQLi transactions (`student-management.php`)<br>• Event Management CRUD with file size/type validation and poster upload (`event-management.php`)<br>• Database-stored student records inspector (`records.php`)<br>• Live MySQL PDO connection status monitor (`database-test.php`)<br>• Traceable audit logging of all administrative actions (`audit_logs` table) |

---

## 💻 3. Technology Stack

* **Frontend**: Semantic HTML5, CSS3 (CSS Grid, Flexbox, custom media queries), Vanilla JavaScript (ES6+, DOM Manipulation, Fetch API, `localStorage`).
* **Backend**: PHP 8.x (Strict typing, PDO, MySQLi, session regeneration, CSRF tokens, password hashing with `password_hash()` and `password_verify()`).
* **Database**: MySQL 8.x / MariaDB (InnoDB engine, normalized 3NF schema, foreign keys with cascade constraints, stored procedures, indexed audit tables).
* **Server Environment**: Apache Web Server (XAMPP local stack), `.htaccess` directory security guards.

---

## 📂 4. Project Directory Structure

```text
studenthub/
├── .gitignore                   # Git exclusion rules
├── README.md                    # Comprehensive project documentation
├── index.php                    # Application entrypoint & role-based router
│
├── ── AUTHENTICATION & SESSIONS ──
├── auth.php                     # RBAC helper, session timeout (20 min), CSRF & remember-me token handling
├── login-form.php               # Accessible Login UI with CSRF protection
├── login.php                    # Secure authentication processor with password_verify()
├── logout.php                   # Session destroy, remember-me token clearance & audit logging
├── signup.php                   # Accessible Student Registration Form with real-time regex validation
├── register.php                 # Backend registration validation, duplicate email check & password_hash()
│
├── ── STUDENT PORTAL PAGES ──
├── home.php                     # Student dashboard with CSS Grid cards & dynamic campus image slider
├── profile.php                  # Student profile details viewer
├── settings.php                 # Account settings & Dark Mode toggle
├── about.html                   # About college & interactive FAQ accordion
├── courses.html                 # Academic degree course catalog
├── events.html                  # Public event list with dynamic JSON/API rendering
├── placement.html               # Corporate placement records & recruiter highlights
├── scholarship.html             # Institutional & government scholarship guidelines
├── timetable.html               # Departmental class & lab schedule
├── workshop.html                # Technical workshop & seminar schedules
│
├── ── ADMIN & MANAGEMENT MODULES ──
├── admin-dashboard.php          # Admin command center with dynamic KPI cards and navigation
├── student-management.php       # Student CRUD module with search, filter, pagination (MySQLi)
├── student-management.js        # AJAX/Fetch asynchronous CRUD logic for student operations
├── event-management.php         # Event CRUD module with poster image upload & validation
├── records.php                  # Database-stored student records overview
├── database-test.php            # Standalone MySQL PDO connection diagnostic page
│
├── ── REST APIS & DATASETS ──
├── students-api.php             # RESTful JSON API endpoint for student CRUD operations
├── events-api.php               # RESTful JSON API endpoint calling stored procedure
├── students.json                # Seed dataset for Practical 6 (15+ student records)
├── events.json                  # Seed dataset for Practical 6 (15+ event records)
├── faqs.json                    # Seed dataset for Practical 6 (15+ FAQ records)
│
├── ── DATABASE & ASSETS ──
├── studenthub.sql               # Complete MySQL schema, tables, seed data & stored procedures
├── style.css                    # Light theme styles, responsive grid, flexbox & accessibility styles
├── style2.css                   # Dark theme stylesheet toggled dynamically via JS & localStorage
├── script.js                    # Core frontend script: theme switch, slider, FAQ, fetch filters
├── P1.jpg, P2.jpg, P3.JPG, P4.jpg # Campus photos used in the Home carousel
│
└── uploads/
    └── event-posters/           # Protected storage for event banners
        ├── .htaccess            # Security guard blocking script execution in uploads
        └── index.php            # 404 forbidden index silence file
```

---

## 📋 5. Academic Syllabus Mapping (Practicals 1 to 15)

The repository directly fulfills every practical assignment in the **CHARUSAT ITUE203** curriculum:

| Practical No. | Practical Title | Key Implementation in Repository |
| :---: | :--- | :--- |
| **1** | Project Initiation, Requirement Analysis, Sitemap, Wireframe & GitHub Setup | `README.md`, `.gitignore`, `Practical Files/STUDENTHUB PORTAL.svg`, modular folder structure. |
| **2** | Semantic HTML5 Pages with Accessibility-Ready Structure | 11+ structured pages: `home.php`, `about.html`, `signup.php`, `login-form.php`, `events.html`, `profile.php`, `courses.html`, `placement.html`, `scholarship.html`, `timetable.html`, `workshop.html`. |
| **3** | Responsive UI Design using CSS Grid, Flexbox & Mobile-First Layout | `style.css` implementing CSS Grid on dashboard cards and Flexbox on topbars, forms, and navigation menus. |
| **4** | JavaScript DOM Manipulation, Event Handling & UI Interactivity | `script.js` driving the collapsible FAQ accordion, campus image carousel (`P1.jpg`–`P4.jpg`), and `localStorage`-backed Light/Dark theme switcher (`style2.css`). |
| **5** | Registration Form with Frontend Validation & Error Handling | `signup.php` & `script.js` featuring real-time RegExp validation for Name, Email, 10-digit Mobile, Password complexity, and terms acceptance. |
| **6** | Rendering External JSON Data using Fetch API, Search & Filter | `events.json`, `students.json`, `faqs.json` (each containing 15+ records) dynamically fetched and filtered in `about.html` and `events.html`. |
| **7** | PHP Form Processing with Server-Side Validation & File Storage | `register.php` sanitizing `$_POST` inputs, enforcing strict server-side regex validations, and displaying user-friendly error banners. |
| **8** | MySQL Schema Design, ER Model, PDO Connectivity & Prepared Statements | `studenthub.sql` schema with InnoDB tables, `db.php` utilizing PHP PDO with `PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION`, and `database-test.php`. |
| **9** | Secure User Registration with Database Insert, Duplicate Check & Hashing | `register.php` checking duplicate email addresses and hashing passwords using `password_hash($rawPassword, PASSWORD_DEFAULT)`. |
| **10** | Secure Login/Logout with Sessions, RBAC, Timeout & Remember-Me Token | `login.php` using `password_verify()`, `session_regenerate_id(true)`, 20-minute inactivity timeout, and secure SHA-256 hashed Remember-Me cookie tokens in `auth.php`. |
| **11** | Student Management CRUD with Search, Filter & MySQLi | `student-management.php` executing procedural/object-oriented `mysqli` prepared statements for Add, Edit, Soft-Delete, Search, and Department Filtering. |
| **12** | Event Management CRUD with Poster Upload & File Validation | `event-management.php` featuring server-side MIME type verification (`image/jpeg`, `image/png`, `image/webp`), file size checks (max 2 MB), and `.htaccess` upload directory protection. |
| **13** | PHP JSON API and AJAX/Fetch-Based CRUD without Full Page Reload | `students-api.php` and `events-api.php` consumed by `student-management.js` using `async`/`await` `fetch()` for dynamic table updates. |
| **14** | Admin Dashboard with Role-Based Access Control, Dynamic Menu & Audit Log | `admin-dashboard.php` showing database aggregation metrics, role-specific dynamic menus (`studenthub_menu_items()`), and audit logging (`audit_logs` table). |
| **15** | Final Integration, Testing, Documentation, GitHub Submission & Local Deployment | Complete end-to-end integration running through `index.php`, fully documented in `README.md`, verified under local Apache/XAMPP. |

---

## ⚙️ 6. Installation & Local Setup Guide

Follow these steps to deploy and run **StudentHub** on your local machine using **XAMPP**:

### Step 1: Clone or Copy Project Files
Place the project inside your XAMPP web root (`htdocs`):
```text
C:\xampp\htdocs\studenthub\
```

### Step 2: Start Apache and MySQL
1. Launch the **XAMPP Control Panel**.
2. Click **Start** next to **Apache**.
3. Click **Start** next to **MySQL**.

### Step 3: Import the Database Schema
1. Open your browser and navigate to **phpMyAdmin**: [http://localhost/phpmyadmin/](http://localhost/phpmyadmin/)
2. Click on the **Import** tab in the top navigation bar.
3. Click **Choose File** and select:
   ```text
   C:\xampp\htdocs\studenthub\studenthub.sql
   ```
4. Click **Go** at the bottom of the page.
   * *This will create the `studenthub` database, all required tables, seed data (20 students and 15 events), and the stored procedure `get_events_with_registration_counts`.*

### Step 4: Verify Database Connection
Open your browser and visit:
```text
http://localhost/studenthub/database-test.php
```
You should see a green confirmation badge confirming that the **MySQL PDO Connection is Successful**.

### Step 5: Launch the Application
Access the portal entrypoint:
```text
http://localhost/studenthub/
```
You will be greeted by the secure login screen (`login-form.php`).

---

## 🔑 7. Default Credentials & Creating an Admin Account

### 🧑‍🎓 Creating a Student Account:
1. Navigate to `http://localhost/studenthub/signup.php` (or click **Sign Up** from the login page).
2. Fill in the registration form with valid credentials (e.g., Name, Email, 10-digit mobile number, and a password containing at least 1 letter and 1 number).
3. Click **REGISTER STUDENT**.
4. You will be redirected to the login page; log in with your newly registered credentials to access the **Student Dashboard** (`home.php`).

### 👨‍💼 Setting up an Administrator Account:
By default, all registrations are created with the `student` role. To promote an account to **Administrator**:
1. Open phpMyAdmin: [http://localhost/phpmyadmin/](http://localhost/phpmyadmin/)
2. Select the `studenthub` database and open the `students` table.
3. Locate your registered email address and execute the following SQL command in the SQL tab:
   ```sql
   UPDATE students SET role = 'admin' WHERE email = 'your.email@example.com';
   ```
4. Log out and log back in. The system will automatically redirect you to the **Admin Dashboard** (`admin-dashboard.php`) with full management privileges.

---

## 🔒 8. Security Measures Implemented

* **Password Protection**: Passwords are never stored in plaintext. They are encrypted using PHP's standard `password_hash($password, PASSWORD_DEFAULT)` and verified using constant-time `password_verify()`.
* **SQL Injection Prevention**: All queries across both PDO (`db.php`) and MySQLi (`db-mysqli.php`) use parameterized prepared statements.
* **CSRF Mitigation**: Anti-Cross-Site-Request-Forgery tokens are generated via `bin2hex(random_bytes(32))` and validated using `hash_equals()`.
* **Session Security**:
  * Cookie flags: `httponly = true`, `samesite = Lax`.
  * Session IDs are refreshed on login using `session_regenerate_id(true)`.
  * Automatic session termination after 20 minutes (1200 seconds) of inactivity.
* **Secure Remember-Me Mechanism**: Remember-me authentication uses cryptographically secure random tokens stored in the database as SHA-256 hashes (`remember_token_hash`), preventing token exposure in the event of a database leak.
* **Upload Directory Hardening**: The `uploads/event-posters/` folder includes an Apache `.htaccess` policy that disables directory indexing (`Options -Indexes`) and blocks script execution for PHP files.

---

## 🧪 9. Evaluation & Viva Checklist

* **Theme Persistence**: Light and dark mode states persist across all page reloads via `localStorage`.
* **Dynamic Menus**: Navigation bars adapt dynamically depending on whether a student or an admin is logged in (`studenthub_menu_items()`).
* **AJAX Interactivity**: Adding or editing students in the admin panel operates smoothly without full browser reloads using Fetch API.
* **Traceable Audit Trail**: Administrator inserts, status toggles, and deletions are recorded in the `audit_logs` table with admin IDs, timestamps, and IP addresses.

---

## 📄 License & Academic Declaration

This project was built as part of the academic curriculum for the course **Web Development Frameworks (ITUE203)** at **Charotar University of Science and Technology (CHARUSAT)**. All source code was developed in accordance with academic integrity and web standards.
