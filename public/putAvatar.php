<?php
  header("Access-Control-Allow-Methods: PUT");

  session_start();

  require_once "../include/common/enableLogging.php"; //TODO remove

  require_once "../include/DatabaseHandler.php";
  require_once "../include/User.php";
  require_once "../include/UserController.php";

  // redirect if invalid request
  if ($_SERVER["REQUEST_METHOD"] !== 'PUT') {
    echo json_encode(array("error" => "requires PUT"));
    exit();
  }

  // require logged in
  if (!isset($_SESSION['username'])) {
    echo json_encode(array("error" => "requires logged in"));
    exit();
  }

  if (!isset($_GET["id"])) {
    echo json_encode(array("error" => "request missing required 'id' field"));
    exit();
  }

  $scribble_id = $_GET["id"];
  $username = $_SESSION['username'];

  $userCtr = new UserController($username);
  $userCtr->setAvatar($scribble_id, $username);
