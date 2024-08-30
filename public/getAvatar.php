<?php
  header("Access-Control-Allow-Methods: GET");

  require_once "../include/common/enableLogging.php"; //TODO remove; debugging logs

  require_once "../include/DatabaseHandler.php";
  require_once "../include/Scribble.php";
  require_once "../include/ScribbleController.php";

  session_start();
  $username = "anonymous";
  if (isset($_GET["username"])) {
    $username = $_GET["username"];
  } else if (isset($_SESSION["username"])) {
    $username = $_SESSION["username"];
  }

  $scrbCtr = new ScribbleController();
  $scrbCtr->handleAvatarGet($username);
