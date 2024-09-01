<?php
  require_once "../include/common/enableLogging.php"; //TODO remove; for development debugging only
  require_once "../include/common/initSession.php";
  require_once "../include/DatabaseHandler.php";
  require_once "../include/Perms.php";

  $username = "anonymous";
  if (isset($_GET["username"])) {
    $username = $_GET["username"];
  } else {
    // reload this page with a param
    if (isset($_SESSION["username"])) {
      $username = $_SESSION["username"];
    }
    header("location: user.php?username=" . $username);
  }
?>
<?php require_once("../include/common/header.php"); ?>

  <link rel="stylesheet" href="css/user.css" type="text/css">
  <link rel="stylesheet" href="css/scribble-card-cart.css" type="text/css">
</head>

<body>
  <?php require_once("../include/common/navbar.php"); ?>
  <div class="bg">
    <div class="user-page">
      <div class="button-area">
        <?php if (isset($_SESSION["username"]) && $username == $_SESSION["username"]) { ?>
          <form class="logout-form" action="postLogout.php" method="post">
            <input class="logout-button" type="submit" value="Logout"/>
          </form>
        <?php } ?>
      </div>
      <div class="scribble-card-cart-container">
        <div class="scribble-card-cart-title">
          <?= "Scribbles by"; ?>
          <br>
          <?= "$username"; ?>
        </div>
        <div class="scribble-card-cart">
          No scribbles...
          <?php if (isset($_SESSION["username"]) && $username == $_SESSION["username"]) { ?>
            <a href="new.php">Create one?!</a>
          <?php } ?>
        </div>
      </div>
    </div>
  </div>
  <script src="js/fetchAvatar.js"></script>
  <script src="js/populateScribbleCardCart.js"></script>
</body>
