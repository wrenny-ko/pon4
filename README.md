# The Pon4 Project

A simple sample website for drawing and viewing doodles.

## Technology:
- OOP PHP
- Vanilla JS
- MariaDB
- Docker

## Features:
- Rudimentary signup and login system with thorough checks
- New-upload page for mouse-input drawings
- User avatars using posted drawings
- Logged-in users, while viewing the drawing pages, can click on a button to set their avatar to that drawing
- Default uploader is set to "anonymous", so unauthed users can post
- Default avatar is provided
- Rough text search of drawings by their titles

## Code Sample Points:
### Docker
- Dockerized the application for ease and reliability of development
- Apache2 and PHP config settings set via shell in Docker RUN commands (Dockerfile)
- Docker compose file includes a phpMyAdmin image for manual schema generation and database debugging
### SQL
- SQL schema for MariaDB read from prepared file (schema/pon4_db.sql)
- Variety of SQL statements including INSERT, SELECT, and UPDATE. Uses JOIN for cross-referencing tables (include/Scribble.php)
### JS/PHP/HTML interactions
- Variety of JS/PHP/HTML examples including:
  - Imported re-useable HTML elements such as the header and the site's "navbar" via PHP require_once
  - Error reporting through only PHP for login/signup input validation (public/signup.php and public/login.php)
  - PHP-only POST forms (public/signup.php, public/login.php)
  - JS-intercepted POST forms (public/new.php, public/js/new.js)
  - Inline script JS intercepting a form submit. Redirects to a search page with a URL query string (include/common/navbar.php)
  - JS POST, GET, and PUT fetches
  - Page reloads and redirects via PHP and JS
  - Buttons enabled/disabled via JS checks on user input (public/js/new.js)
  - Pop-up modal view and close by HTML buttons and JS listeners (public/new.php, public/js/new.js)
  - Passing parameters through URL query string into HTML elements via PHP, accessed by JS (public/scribble.php, public/js/scribble.js)
  - Passing parameters through URL query string into JS directly (public/js/index.js)
  - Dynamic generation of HTML elements via JS (public/js/index.js)

## To run, use:
```
docker compose up --build
```
