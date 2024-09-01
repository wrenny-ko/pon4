<?php
  require_once "../include/common/enableLogging.php"; //TODO remove; for development debugging only
  require_once "../include/common/initSession.php";
  require_once "../include/DatabaseHandler.php";
  require_once "../include/Perms.php";

  // if logged in, don't show signup page
  if (isset($_SESSION['username'])) {
    header("location: user.php");
  }
?>

<?php require_once("../include/common/header.php"); ?>
  <link rel="stylesheet" href="css/signup.css" type="text/css">
</head>

<body>
  <?php require_once("../include/common/navbar.php"); ?>
  <div class="bg">
    <div class="signup">
      <form class="signupForm" action="postSignup.php" method="post">
        <div class="formTitle">
          Sign up
        </div>
        <div class="inputField">
          <div class="fieldLabel">Username (alphanumeric)</div>
          <input type="text" class="textInput inputUsername" name="username" maxlength="20"/>
        </div>
        <div class="inputField">
          <div class="fieldLabel">Email</div>
          <input type="text" class="textInput inputEmail" name="email" maxlength="40"/>
        </div>
        <div class="inputField">
          <div class="fieldLabel">Password (alphanumeric)</div>
          <input type="text" class="textInput inputPassword" name="password" maxlength="40"/>
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
  <script src="js/fetchAvatar.js"></script>
</body>
