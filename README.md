# The Pon4 Project

A simple sample website for drawing and viewing doodles.

<img src="https://github.com/user-attachments/assets/36bf3238-1237-4c77-813a-5634c0455bdd" alt="cardcart" width="400"/>
<img src="https://github.com/user-attachments/assets/46217a65-b01c-4847-814d-4c8f8e1c8a99" alt="canteen" width="400"/>
<img src="https://github.com/user-attachments/assets/44e54990-f7fe-4625-96bb-342c8efd6fec" alt="canteen" width="400"/>
<img src="https://github.com/user-attachments/assets/93aef1c7-5621-4dd8-9cf0-a671fa466084" alt="canteen" width="400"/>

## Technology:
- OOP PHP
- Vanilla JS
- MariaDB
- Docker

## Features:
- Rudimentary signup and login system
- New-upload page for mouse-input drawings
- User avatars selectable using posted drawings
- Anonymous uploads allowed
- Rough text search of drawings by their titles
- Search of drawings by uploader's username
- Likes and dislikes for drawings (logged-in only)
- Sortable leaderboard page
- Mods/Admins can delete drawings
- Admins/Techs can view an API log page

## Code Sample Points:
### Docker
- Dockerized the application for ease and reliability of development
- Apache2 and PHP config settings set via shell in Docker RUN commands (Dockerfile)
- Docker compose file includes a phpMyAdmin image for manual schema generation and database debugging
### SQL
- SQL schema for MariaDB read from prepared file [(schema/pon4_db.sql)](https://github.com/wrenny-ko/pon4/blob/8f0d6db1fcc3a932278d856412bc8843eebcfcc7/schema/pon4_db.sql)
- Variety of SQL statements including INSERT, SELECT, UPDATE, and DELETE
- Aggragate functions and Joins [(include/Leaderboard.php)](https://github.com/wrenny-ko/pon4/blob/master/include/Leaderboard.php#L142)
### PHP
- OOP
- Re-usable Rest API class with authentication, authorization, and logging [(include/Rest.php)](https://github.com/wrenny-ko/pon4/blob/8f0d6db1fcc3a932278d856412bc8843eebcfcc7/include/Rest.php#L43)
- Salted and Hashed Passwords using hash_pbkdf2 [(include/User.php)](https://github.com/wrenny-ko/pon4/blob/8f0d6db1fcc3a932278d856412bc8843eebcfcc7/include/User.php#L112)
- Re-usable HTML header and navbar template [(include/common/)](https://github.com/wrenny-ko/pon4/tree/8f0d6db1fcc3a932278d856412bc8843eebcfcc7/include/common)
- API route with file I/O and data curation [(include/LogController.php)](https://github.com/wrenny-ko/pon4/blob/8f0d6db1fcc3a932278d856412bc8843eebcfcc7/include/LogController.php#L75-L99)
- Authentication via sessions
- Authorization via user roles [(include/Perms.php)](https://github.com/wrenny-ko/pon4/blob/8f0d6db1fcc3a932278d856412bc8843eebcfcc7/include/Perms.php)
- Login and signup system with checks for unique usernames, valid emails, and required password format [(public/signup.php)](https://github.com/wrenny-ko/pon4/blob/8f0d6db1fcc3a932278d856412bc8843eebcfcc7/public/signup.php) and [(public/login.php)](https://github.com/wrenny-ko/pon4/blob/8f0d6db1fcc3a932278d856412bc8843eebcfcc7/public/login.php)
- ETL to form a sortable leaderboard page [(include/Leaderboard.php)](https://github.com/wrenny-ko/pon4/blob/8f0d6db1fcc3a932278d856412bc8843eebcfcc7/include/Leaderboard.php#L89-L168)
### JS/PHP/HTML interactions
- Variety of JS/PHP/HTML examples including:
  - PHP-only POST forms. Client error viewing through only PHP for login/signup input validation [(public/signup.php)](https://github.com/wrenny-ko/pon4/blob/8f0d6db1fcc3a932278d856412bc8843eebcfcc7/public/signup.php), [(public/login.php)](https://github.com/wrenny-ko/pon4/blob/8f0d6db1fcc3a932278d856412bc8843eebcfcc7/public/login.php)
  - JS-intercepted POST forms [(public/new.php)](https://github.com/wrenny-ko/pon4/blob/8f0d6db1fcc3a932278d856412bc8843eebcfcc7/public/new.php), [(public/js/new.js)](https://github.com/wrenny-ko/pon4/blob/8f0d6db1fcc3a932278d856412bc8843eebcfcc7/public/js/new.js#L151-L169)
  - Inline script JS intercepting a form submit. Redirects to a search page with a URL query string [(include/common/navbar.php)](https://github.com/wrenny-ko/pon4/blob/8f0d6db1fcc3a932278d856412bc8843eebcfcc7/include/common/navbar.php#L61-L69)
  - JS POST, GET, PUT, and DELETE fetches
  - Page reloads and redirects via both PHP and JS
  - Buttons enabled/disabled via JS checks on user input [(public/js/new.js)](https://github.com/wrenny-ko/pon4/blob/8f0d6db1fcc3a932278d856412bc8843eebcfcc7/public/js/new.js#L159-L166)
  - Pop-up modal view and close by HTML buttons and JS listeners [(public/new.php)](https://github.com/wrenny-ko/pon4/blob/8f0d6db1fcc3a932278d856412bc8843eebcfcc7/public/new.php), [(public/js/new.js)](https://github.com/wrenny-ko/pon4/blob/8f0d6db1fcc3a932278d856412bc8843eebcfcc7/public/js/new.js)
  - Passing parameters through URL query string into HTML elements via PHP, accessed by JS [(public/scribble.php)](https://github.com/wrenny-ko/pon4/blob/8f0d6db1fcc3a932278d856412bc8843eebcfcc7/public/scribble.php#L35), [(public/js/scribble.js)](https://github.com/wrenny-ko/pon4/blob/8f0d6db1fcc3a932278d856412bc8843eebcfcc7/public/js/scribble.js#L2-L3)
  - Passing parameters through URL query string into JS directly [(public/js/index.js)](https://github.com/wrenny-ko/pon4/blob/8f0d6db1fcc3a932278d856412bc8843eebcfcc7/public/js/populateScribbleCardCart.js#L3-L4)
  - Dynamic generation of HTML elements via JS [(public/api/log.js)](https://github.com/wrenny-ko/pon4/blob/8f0d6db1fcc3a932278d856412bc8843eebcfcc7/public/js/log.js#L23-L50)
  - UI buttons and state management through PHP and JS manipulation of HTML and query strings [(public/js/leaderboard.js)](https://github.com/wrenny-ko/pon4/blob/8f0d6db1fcc3a932278d856412bc8843eebcfcc7/public/js/leaderboard.js#L22-L35), [(public/leaderboard.php)](https://github.com/wrenny-ko/pon4/blob/8f0d6db1fcc3a932278d856412bc8843eebcfcc7/public/leaderboard.php#L81)

## To run, use:
```
docker compose up --build
```
