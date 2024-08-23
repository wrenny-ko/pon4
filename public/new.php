<!DOCTYPE html>
<?php
  session_start();
?>
<head>
  <link rel="icon" href="favicon.ico" type="image/x-icon" />
  <link rel="shortcut icon" href="favicon.ico" type="image/x-icon" />
  <link rel="stylesheet" href="main.css" type="text/css">
  <link rel="stylesheet" href="new.css" type="text/css">
</head>
<body>
  <div class="navbar">
    <div class="logo">
      <a href="/">
        <img src="/favicon.ico" class="icon nav-entry" alt="Pon4 logo"/>
      </a>
    </div>
    <div class="newPost">
      <a href="/new.php">
        <img src="/newpost.png" class="icon nav-entry" alt="newpost"/>
      </a>
    </div>
    <div class="account nav-entry">
      <?php
        if (session_status() === PHP_SESSION_ACTIVE and isset($_SESSION['username'])) {
          echo "<img class=\"avatar icon\"/>";
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
    <div class="newLayout">
      <div class="padContainer">
        <canvas class="pad"></canvas>
      </div>
      <div class="buttonContainer">
        <input type="button" class="button clearCanvasButton" value="Reset"/>
        <input type="button" class="button submitButton" value="Submit"/>
        <div class="spinner hidden"/>
      </div>
      <div class="error">
        placeholder
      </div>
    </div>
  </div>
  <div class="titleModal center ">
    <img class="thumb">
    <div class="titleLabel">What do you want to call it?</div>
    <input type="text" class="textInput inputTitle"/>
    <input type="button" class="button titleButton" value="Post it!"/>
    <input type="button" class="closeTitleModal button" value="Go back!"/>
  </div>
  <script src="new.js"></script>
</body>
