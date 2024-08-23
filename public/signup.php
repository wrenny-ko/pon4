<!DOCTYPE html>
<?php
  session_start();

  // if logged in, don't show signup page
  if (isset($_SESSION['username'])) {
    header("location: user.php");
  }
?>
<head>
  <link rel="icon" href="favicon.ico" type="image/x-icon" />
  <link rel="shortcut icon" href="favicon.ico" type="image/x-icon" />
  <link rel="stylesheet" href="main.css" type="text/css">
  <link rel="stylesheet" href="signup.css" type="text/css">
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
    <div class="signup">
      <form class="signupForm" action="postSignup.php" method="post">
        <div class="formTitle">
          Sign up
        </div>
        <div class="inputField">
          <div class="fieldLabel">Username (alphanumeric)</div>
          <input type="text" class="textInput inputUsername" name="username"/>
        </div>
        <div class="inputField">
          <div class="fieldLabel">Email</div>
          <input type="text" class="textInput inputEmail" name="email"/>
        </div>
        <div class="inputField">
          <div class="fieldLabel">Password (alphanumeric)</div>
          <input type="text" class="textInput inputPassword" name="password"/>
        </div>
        <input class="submitButton" type="submit" value="Submit"/>
        <div class="navToLogin">
          Already a member? <a href="login.php">Log in</a> instead.
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
</body>
