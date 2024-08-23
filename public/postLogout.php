<?php
  header("Access-Control-Allow-Methods: POST");

  // redirect if invalid request
  if ($_SERVER["REQUEST_METHOD"] !== 'POST') {
    header("location: index.php");
  }

  session_start();
  $_SESSION['username'] = null;

  header("location: index.php");
