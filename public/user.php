<!DOCTYPE html>
<?php session_start(); ?>
<?php require_once("../include/common/header.php"); ?>
</head>

<body>
  <?php require_once("../include/common/navbar.php"); ?>
  <div class="bg">
    <form class="logoutForm" action="postLogout.php" method="post">
      <input class="logoutButton" type="submit" value="Logout"/>
    </form>
  </div>
  <script src="js/fetchAvatar.js"></script>
</body>
