<!DOCTYPE html>
<?php session_start(); ?>
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
