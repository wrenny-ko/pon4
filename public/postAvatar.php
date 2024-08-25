<?php
  header("Access-Control-Allow-Methods: POST");

  require_once "../include/common/enableLogging.php"; //TODO remove

  require_once "../include/DatabaseHandler.php";
  require_once "../include/User.php";
  require_once "../include/UserController.php";

  // redirect if invalid request
  if ($_SERVER["REQUEST_METHOD"] !== 'POST') {
    header("location: index.php");
  }

  $scribble_id = $_POST["id"];
  $username = $_POST["username"];

  $userCtr = new UserController($username);
  $userCtr->setAvatar($scribble_id, $username);
