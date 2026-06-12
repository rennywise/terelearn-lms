# TERELEARN

This project keeps most public entry files grouped under `public/` and exposes only the main interface shortcuts at the project root.

## Structure

- `app/pages/`
  Main page implementations for auth, admin, faculty, classroom, and student screens.
- `app/api/`
  Backend endpoints used by the page scripts.
- `app/config/`
  Shared configuration such as database connection, paths, and root page routing.
- `app/includes/`
  Shared PHP helpers, including the root page loader.
- `public/`
  Public entry points grouped by feature, plus public assets and uploads.
- `assets/`, `dist/`, `plugins/`
  Frontend assets and vendor files.
- `uploads/`, `uploads_files/`, `uploads_previews/`, `storage/`
  Uploaded and generated files.

## Root Page Routing

The main root shortcuts and grouped public entry files load their targets through:

- [app/config/root_pages.php](C:/xampp/htdocs/finalbackup1/TERELEARN/app/config/root_pages.php)
- [app/includes/root_page_loader.php](C:/xampp/htdocs/finalbackup1/TERELEARN/app/includes/root_page_loader.php)
- [.htaccess](C:/xampp/htdocs/finalbackup1/TERELEARN/.htaccess)

This keeps the URL structure stable while moving most entry files into organized folders.

## Common Pages

- Main root shortcuts:
  [admin.php](C:/xampp/htdocs/finalbackup1/TERELEARN/admin.php),
  [facultyUI.php](C:/xampp/htdocs/finalbackup1/TERELEARN/facultyUI.php),
  [signin.php](C:/xampp/htdocs/finalbackup1/TERELEARN/signin.php),
  [student.php](C:/xampp/htdocs/finalbackup1/TERELEARN/student.php),
  [subadmin.php](C:/xampp/htdocs/finalbackup1/TERELEARN/subadmin.php)
- Organized public folders:
  [public/admin](C:/xampp/htdocs/finalbackup1/TERELEARN/public/admin),
  [public/auth](C:/xampp/htdocs/finalbackup1/TERELEARN/public/auth),
  [public/faculty](C:/xampp/htdocs/finalbackup1/TERELEARN/public/faculty),
  [public/classroom](C:/xampp/htdocs/finalbackup1/TERELEARN/public/classroom),
  [public/student](C:/xampp/htdocs/finalbackup1/TERELEARN/public/student)

## Maintenance Rule

If you need to change how a screen works:

- edit the page in `app/pages/...`
- edit the backend handler in `app/api/...`
- update `.htaccess` when adding or removing a legacy root URL
