<!DOCTYPE html>
<?php
  session_start();

  // if logged in, don't show login page
  if (isset($_SESSION['username'])) {
    header("location: user.php");
  }
?>

<head>
  <link rel="icon" href="favicon.ico" type="image/x-icon" />
  <link rel="shortcut icon" href="favicon.ico" type="image/x-icon" />
  <link rel="stylesheet" href="main.css" type="text/css">
  <link rel="stylesheet" href="login.css" type="text/css">
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
          echo "<img class=\"avatar\"/>";
          $username = $_SESSION['username'];
          echo "<a href=\"/user?username=$username\" class=\"account\">";
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
    <div class="login">
      <form class="loginForm" action="postLogin.php" method="post">
        <div class="formTitle">
          Login
        </div>
        <div class="inputField">
          <div class="fieldLabel">Username</div>
          <input type="text" class="textInput inputUsername" name="username"/>
        </div>
        <div class="inputField">
          <div class="fieldLabel">Password</div>
          <input type="text" class="textInput inputPassword" name="password"/>
        </div>
        <input class="submitButton" type="submit" value="Submit"/>
        <div class="navToSignup">
          Don't have an account? <a href="signup.php">Sign up</a> instead.
        </div>
        <div class="errorBox">
          <?php
            if (isset($_GET["error"])) {
              echo "Error: " . htmlspecialchars_decode($_GET["error"]);
            }
          ?>
        </div>
      </form>
    </div>
  </div>
  <script src="login.js"></script>
</body>
