<!DOCTYPE html>
<?php
  session_start();

  // if logged in, don't show login page
  if (isset($_SESSION['username'])) {
    header("location: user.php");
  }
?>

<?php require_once("../include/common/header.php"); ?>
  <link rel="stylesheet" href="css/login.css" type="text/css">
</head>

<body>
  <?php require_once("../include/common/navbar.php"); ?>
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
  <script src="js/fetchAvatar.js"></script>
  <script src="js/login.js"></script>
</body>
