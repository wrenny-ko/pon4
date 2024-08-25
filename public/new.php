<!DOCTYPE html>
<?php session_start(); ?>

<?php require_once("../include/common/header.php"); ?>
  <link rel="stylesheet" href="css/new.css" type="text/css">
</head>

<body>
  <?php require_once("../include/common/navbar.php"); ?>
  <div class="bg">
    <div class="newLayout">
      <div class="padContainer">
        <canvas class="pad"></canvas>
      </div>
      <div class="buttonContainer">
        <input type="button" class="button clearCanvasButton" value="Reset"/>
        <input type="button" class="button submitButton" value="Submit"/>
        <div class="spinner hidden"/>
      </div>
      <div class="error">
      </div>
    </div>
  </div>
  <div class="titleModal center ">
    <img class="thumb">
    <div class="titleLabel">What do you want to call it?</div>
    <input type="text" class="textInput inputTitle"/>
    <input type="button" class="button titleButton" value="Post it!"/>
    <input type="button" class="closeTitleModal button" value="Go back!"/>
  </div>
  <script src="new.js"></script>
</body>
