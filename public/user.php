<?php
  require_once "../include/common/includes.php";

  $uname = "anonymous";
  if (isset($_GET["username"])) {
    $uname = $_GET["username"];
  } else {
    // reload this page with a param
    if (isset($_SESSION["username"])) {
      $uname = $_SESSION["username"];
    }
    header("location: user.php?username=" . $uname);
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
        <?php if (isset($_SESSION["username"]) && $uname == $_SESSION["username"]) { ?>
          <form class="logout-form" action="postLogout.php" method="post">
            <input class="logout-button" type="submit" value="Logout"/>
          </form>
        <?php } ?>
      </div>
      <div class="scribble-card-cart-container">
        <div class="scribble-card-cart-title">
          <?= "Scribbles by"; ?>
          <br>
          <?= "$uname"; ?>
        </div>
        <div class="scribble-card-cart">
          No scribbles...
          <?php if (isset($_SESSION["username"]) && $uname == $_SESSION["username"]) { ?>
            <a href="new.php">Create one?!</a>
          <?php } ?>
        </div>
      </div>
    </div>
  </div>
  <script src="js/populateScribbleCardCart.js"></script>
</body>
