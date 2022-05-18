# dubai-pasabuy

## Setup

1. `cd` into your `htdocs` directory.
2. `git clone` the repo.
3. `cd` into `dubai-pasabuy`.
4. Start Apache and MySQL from the XAMPP control panel.
5. Go to phpMyAdmin and create a new database.
6. Import the database tables into your new database. Use the sql file inside the sql directory of the repo.
7. For development, open the .htaccess file and edit the `RewriteRule` directives.

    Prefix all `/public` with the name of the directory containing the source files, that is, `/dubai-pasabuy`. Example: `/public/admin.php` should be `/dubai-pasabuy/public/admin.php`.

8. Go to [http://localhost/dubai-pasabuy](http://localhost/dubai-pasabuy).
