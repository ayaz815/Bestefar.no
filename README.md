# Bestefar.no

Bestefar.no is a Norwegian competition and quiz site aimed at older users ("bestefar"
means grandfather). Visitors read short articles, browse competitions ("shows") and
take part in quizzes. Behind it there is an admin panel where the team adds articles,
competitions, categories and quiz content.

This repository holds the working code for the site as it runs today.

## How it is built

The site is a classic PHP application built on **CodeIgniter 3**. Nothing fancy: server
rendered pages, a MySQL database and a folder of assets.

- **PHP** 7.x / 8.x with the `mysqli` extension
- **MySQL** (or MariaDB)
- **CodeIgniter 3** – the framework lives in `httpd.www/system`
- Frontend is plain HTML/CSS/JS with some jQuery; a few pages come from Elementor exports
- The admin panel uses AdminLTE, CKEditor / CKFinder and TinyMCE for content editing

## Folder layout

```
httpd.www/                     the web root (point your server here)
├── index.php                  CodeIgniter front controller
├── .htaccess                  sends every request through index.php
├── application/               our code
│   ├── controllers/           Home (frontend), Admin, Ajax, Login, Modal
│   ├── models/                Admin_model (most queries), Excel_model, Strip_model
│   ├── views/
│   │   ├── frontend/          public pages + theme partials
│   │   └── admin/             admin panel pages + theme partials
│   ├── config/                database.php, config.php, routes.php ...
│   └── uploads/, logs/, cache/, sessions/   runtime files (ignored by git)
├── system/                    CodeIgniter framework (do not edit)
├── assets/                    frontend + admin CSS, JS, images, editors
├── uploads/                   media uploaded from the admin (ignored by git)
├── bestefar-image/            small standalone tool for image quizzes
├── bestefar-imagemusic/       small standalone tool for image + music quizzes
└── html-generator-app/        small standalone tool for generating quiz HTML

httpd.private/                 reserved for files that must sit outside the web root
```

Things that are deliberately **not** in git (see `.gitignore`): user uploads, logs,
cache and session files, local database dumps, zip/tar backups and the large
`New folder/` copy of old material.

## Getting it running locally

You need PHP, MySQL and a copy of the database.

1. **Database**

   Create the database and a user that matches the config:

   ```sql
   CREATE DATABASE bestefar_no CHARACTER SET utf8mb4;
   CREATE USER 'bestefar_no'@'localhost' IDENTIFIED BY 'your_db_password';
   GRANT ALL ON bestefar_no.* TO 'bestefar_no'@'localhost';
   ```

   Copy `.env.example` to `.env` and put the same database name, user and
   password there.

   Then import the dump you were given:

   ```bash
   mysql -ubestefar_no -p bestefar_no < bestefar.no_database_bestefar_no_live.sql
   ```

   Main tables: `admin`, `users`, `members`, `blogs`, `shows`, `shows_extra`,
   `fake_shows`, `main_categories`, `categories`, `sub_categories`,
   `system_setting`, `visitors`, `payments`.

2. **Config**

   Database settings live in `httpd.www/application/config/database.php`.
   The base URL is in `httpd.www/application/config/config.php` – set it to whatever
   address you use locally, for example `http://localhost:8080/`.

3. **Run it**

   The quickest way is PHP's built in server:

   ```bash
   cd httpd.www
   php -S localhost:8080
   ```

   Open <http://localhost:8080/>. The admin panel is at
   <http://localhost:8080/admin>.

   For something closer to production, point Apache or nginx at `httpd.www` and make
   sure `mod_rewrite` (or the nginx equivalent) is on so `.htaccess` can route
   everything through `index.php`.

## How the site is organised

### Frontend – `Home` controller

- `/` – landing page. If an article is flagged as the front page article it redirects
  straight to it, otherwise it shows the home view.
- `/home/blogs`, `/home/blog_details/{id}` – articles
- `/home/shows`, `/home/show_details/{id}` – competitions
- `/home/omideen` – about page
- `/home/registration_msg` – message shown after signing up
- a few themed landing pages (`yellow`, `blue`)

### Admin – `Admin` controller

Login at `/admin`. From there:

- **Dashboard** – quick numbers
- **System settings** – site wide texts and toggles
- **Users / Members / Visitors** – people and simple stats
- **Categories** – three levels: main category → category → sub category
- **Shows** – the real competitions, plus **Fake shows** for demo/filler content
- **Blogs** – articles, with draft ("unpublished") and preview support
- **Admin emails** – email templates and recipients

### Data access

Almost every query goes through `application/models/Admin_model.php`. It has a set of
small generic helpers (`get_row_id`, `get_data_conditions`, `get_data_desc` and so on)
that the controllers reuse. Passwords are currently compared directly in SQL, so keep
that in mind if you touch the login code.

## Working on the code

- Branch off `main` for any change.
- Keep commits small and describe them in plain English.
- Don't edit anything under `httpd.www/system` – that's the framework.
- Runtime folders (`logs`, `cache`, `sessions`, `uploads`) should stay empty in git.

## Ownership

Bestefar.no is owned by GA-prosjekt AS, Oslo.
