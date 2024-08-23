<?php
  header("Access-Control-Allow-Methods: POST");

  require_once "../include/enableLogging.php"; //TODO remove

  require_once "../include/DatabaseHandler.php";
  require_once "../include/Signup.php";
  require_once "../include/SignupController.php";

  // redirect if invalid request
  if ($_SERVER["REQUEST_METHOD"] !== 'POST') {
    header("location: index.php");
  }

  $username = $_POST["username"];
  $email = $_POST["email"];
  $password = $_POST["password"];

  $signupCtr = new SignupController($username, $email, $password);

  $signupCtr->signupUser();

  header("location: user.php");
