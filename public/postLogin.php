<?php
  header("Access-Control-Allow-Methods: POST");

  require_once "../include/common/enableLogging.php"; //TODO remove

  require_once "../include/DatabaseHandler.php";
  require_once "../include/User.php";
  require_once "../include/LoginController.php";

  $loginCtr = new LoginController();

  // redirect if invalid request
  if ($_SERVER["REQUEST_METHOD"] !== 'POST') {
    $loginCtr->error("Expected POST requests only.");
  }

  if (!isset($_POST["username"]) || !isset($_POST["password"])) {
    $loginCtr->error("POST data lacking username and password.");
  }

  $loginCtr->setUsername($_POST["username"]);
  $loginCtr->setPassword($_POST["password"]);
  $loginCtr->loginUser();

  header("location: user.php?username=" . $_POST["username"]);
