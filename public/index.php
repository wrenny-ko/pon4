<!DOCTYPE html>
<?php
  session_start();
  //$_SESSION['username'] = null;
?>
<head>
  <link rel="icon" href="favicon.ico" type="image/x-icon" />
  <link rel="shortcut icon" href="favicon.ico" type="image/x-icon" />
  <link rel="stylesheet" href="main.css" type="text/css">
</head>
<body>
  <div class="navbar">
    <div class="logo">
      <a href="/">
        <img src="/favicon.ico" class="icon nav-entry" alt="Pon4 logo"/>
      </a>
    </div>
    <div class="newPost">
      <a href="/new">
        <img src="/newpost.png" class="icon nav-entry" alt="newpost"/>
      </a>
    </div>
    <div class="account nav-entry">
      <?php
        if (session_status() === PHP_SESSION_ACTIVE and isset($_SESSION['username'])) {
          $username = $_SESSION['username'];
          echo "<a href=\"/user/$username\" class=\"account\">";
          echo "$username";
          echo "</a>";
        } else {
      ?>
        <a href="/login.php" class="account">
          Login
        </a>
      <?php
        }
      ?>
    </div>
  </div>
  <div class="bg">
    
  </div>
</body>
