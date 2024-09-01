<?php
  require_once "../include/common/enableLogging.php"; //TODO remove; for development debugging only
  require_once "../include/common/initSession.php";
  require_once "../include/DatabaseHandler.php";
  require_once "../include/Perms.php";
?>
<?php require_once("../include/common/header.php"); ?>
  <link rel="stylesheet" href="css/scribble-card-cart.css" type="text/css">
</head>

<body>
  <?php require_once("../include/common/navbar.php"); ?>
  <div class="bg">
    <div class="scribble-card-cart">
      No scribbles...
    </div>
  </div>
  <script src="js/fetchAvatar.js"></script>
  <script src="js/populateScribbleCardCart.js"></script>
</body>
