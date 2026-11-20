# Jeremiah.dev — Full-stack portfolio starter

## Requirements
- Windows
- XAMPP (Apache + MySQL)
- A modern browser
- A code editor such as VS Code

## 1. Put the project in XAMPP
1. Extract the `jeremiah-dev-fullstack` folder.
2. Copy it into `C:\xampp\htdocs\`.
3. Start **Apache** and **MySQL** from the XAMPP Control Panel.

## 2. Create the database
1. Visit `http://localhost/phpmyadmin`.
2. Select **Import**.
3. Choose this project's `database.sql`.
4. Click **Import** or **Go**.

The SQL creates a database named `jeremiah_portfolio`, plus `messages` and `admins` tables.

## 3. Check database credentials
Open `config/database.php`. The default XAMPP MySQL username is often `root` with a blank password on a local development installation. If you configured a password, enter it there.

## 4. Create your admin account
1. Visit `http://localhost/jeremiah-dev-fullstack/admin/create_admin.php`.
2. Create an email and a strong password (at least 12 characters).
3. **Immediately delete `admin/create_admin.php` after creating the account.**
4. Visit `http://localhost/jeremiah-dev-fullstack/admin/login.php` and sign in.

## 5. Open the website
Visit `http://localhost/jeremiah-dev-fullstack/`.

## Project structure
- `index.php` — portfolio homepage
- `contact.php` — validates and saves contact messages
- `config/database.php` — PDO database connection
- `includes/` — shared header and footer
- `assets/css/style.css` — responsive styling
- `assets/js/main.js` — mobile navigation behavior
- `admin/` — login, dashboard, logout, one-time admin setup
- `database.sql` — database and table definitions

## Important security notes
This is a local learning starter, not a production-ready deployment. Before publishing it:
- Use HTTPS and secure cookie/session settings.
- Move database credentials into environment configuration outside the public web root.
- Add CSRF protection and login rate limiting.
- Keep PHP, Apache, and MySQL updated.
- Remove setup scripts and disable public error display.
- Add privacy, retention, and spam-protection measures to the contact form.
- Use a proper production hosting configuration and backups.

## Learning tasks
1. Change the introduction and skills to your real experience.
2. Replace the sample projects with your own project descriptions.
3. Add project screenshots and links to GitHub repositories.
4. Test the contact form and confirm messages appear in the admin dashboard.
5. Explain what `prepare()`, `execute()`, `password_hash()`, and `password_verify()` do.


## Modernized portfolio update

- Replaced the public portfolio styling with a responsive dark navy / violet / cyan design.
- Added the supplied profile photograph at `assets/image/profile.png`.
- Added responsive project preview illustrations built with CSS (no extra image downloads required).
- Improved the mobile navigation and contact-form feedback display.
- Kept the existing PHP contact handler, database connection, and admin pages.

### Run locally with XAMPP

1. Extract this folder into `C:\xampp\htdocs\`.
2. Start Apache and MySQL in the XAMPP Control Panel.
3. Import `database.sql` into phpMyAdmin if the database has not already been created.
4. Check the database credentials in `config/database.php`.
5. Open `http://localhost/jeremiah-dev-fullstack/` in your browser.

The profile image is already included at `assets/image/profile.png`.


## Version 2.0 features

- Dark/light theme switch with preference saved in the browser.
- Respects the device theme preference on first visit.
- Scroll-reveal motion for sections and project/skill cards.
- Social profile link placeholders in the hero and footer.
- A Download CV navigation link appears automatically after you add your own `assets/files/cv.pdf`.

### Finish your personal links and CV

1. Replace the `#contact` destinations for LinkedIn and GitHub in `index.php` and `includes/footer.php` with your real profile URLs.
2. Create `assets/files/` and put your own CV there with the filename `cv.pdf`. The navigation link will then appear automatically.
3. Refresh the page with `Ctrl + F5`.


## Version 3.0 — Admin dashboard and dynamic projects

V3 adds:
- Admin dashboard summary cards (total messages, unread messages, projects).
- Message inbox with read/unread status and delete actions.
- Project management: add, edit, delete, publish/draft, ordering, technology tags, external project URL, and image upload.
- Public homepage project cards are loaded from the database.
- Project images are restricted to JPG, PNG, and WEBP and limited to 3 MB.
- POST actions use a session CSRF token.

### Important database upgrade

Before using V3, back up your existing database in phpMyAdmin (Export), then import `database_v3_upgrade.sql` into the existing `jeremiah_portfolio` database. Run this migration only once. It creates the `projects` table and adds the `is_read` column to `messages`; it does not delete existing admins or messages.

### Use the V3 admin area

1. Sign in at `http://localhost/jeremiah-dev-fullstack/admin/login.php`.
2. Open Manage projects from the dashboard, or visit `/admin/projects.php`.
3. Add a project, choose whether to publish it, and save.
4. The published project will appear on the homepage.
5. Review messages, mark them read/unread, or delete them from the dashboard.

### Upload folder

Uploaded project images are stored in `assets/uploads/`. PHP execution is denied in that folder by its `.htaccess` file. Keep Apache's `.htaccess` support enabled.

Back up the database and project folder before upgrading. Do not expose `admin/create_admin.php` on a public server; delete it after creating the first admin account.
