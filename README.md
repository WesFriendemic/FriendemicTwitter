#Installation

Should be simple.

* Do a `composer update`.
* Create the database. This is kinda annoying: Twitter tends to have 4-byte UTF-8 characters, so you have to do something like:

    `mysql> create database some_database_name CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;`

    Then, just:

    `bash> mysql -u user -p some_database_name < schema.sql`

    It's not the end of the world if MySQL isn't using utf8mb4, but 4-byte characters will just appear as '?', which is no fun.

* Alter `app/config.php` with the correct values.
* Add a VirtualHost in apache to set the DocumentRoot to the app/public directory.

#Requirements

* PHP 5.3+
* curl extension for PHP

