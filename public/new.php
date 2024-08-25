<!DOCTYPE html>
<?php session_start(); ?>

<?php require_once("../include/common/header.php"); ?>
  <link rel="stylesheet" href="css/new.css" type="text/css">
</head>

<body>
  <?php require_once("../include/common/navbar.php"); ?>
  <div class="bg">
    <div class="new-layout">
      <div class="pad-container">
        <canvas class="pad"></canvas>
      </div>
      <div class="button-container">
        <input type="button" class="button clear-canvas-button" value="Reset"/>
        <input type="button" class="button submit-button" value="Submit"/>
        <div class="spinner hidden"/>
      </div>
      <div class="error">
      </div>
    </div>
  </div>
  <div class="title-modal center ">
    <img class="thumb">
    <div class="title-label">What do you want to call it?</div>
    <input type="text" class="text-input input-title" maxlength="30"/>
    <input type="button" class="button title-button" value="Post it!"/>
    <input type="button" class="button close-title-modal" value="Go back!"/>
  </div>
  <script src="js/fetchAvatar.js"></script>
  <script src="js/new.js"></script>
</body>
