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
      <form class="login-form" action="postLogin.php" method="post">
        <div class="form-title">
          Login
        </div>
        <div class="input-field">
          <div class="field-label">Username</div>
          <input type="text" class="text-input input-username" name="username"/>
        </div>
        <div class="input-field">
          <div class="field-label">Password</div>
          <input type="text" class="text-input input-password" name="password"/>
        </div>
        <input class="submit-button" type="submit" value="Submit"/>
        <div class="nav-to-signup">
          Don't have an account? <a href="signup.php">Sign up</a> instead.
        </div>
        <div class="error-box">
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
