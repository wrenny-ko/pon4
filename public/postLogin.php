<?php
  header("Access-Control-Allow-Methods: POST");

  require_once "../include/enableLogging.php"; //TODO remove

  require_once "../include/DatabaseHandler.php";
  require_once "../include/Login.php";
  require_once "../include/LoginController.php";

  // redirect if invalid request
  if ($_SERVER["REQUEST_METHOD"] !== 'POST') {
    header("location: index.php");
  }

  $username = $_POST["username"];
  $password = $_POST["password"];

  $loginCtr = new LoginController($username, $password);

  $loginCtr->loginUser();

  header("location: index.php");
