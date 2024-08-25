<?php
  header("Access-Control-Allow-Methods: GET");

  require_once "../include/common/enableLogging.php"; //TODO remove

  require_once "../include/DatabaseHandler.php";
  require_once "../include/Scribble.php";
  require_once "../include/ScribbleController.php";

  // redirect if invalid request
  if ($_SERVER["REQUEST_METHOD"] !== 'GET') {
    header("location: index.php");
  }

  $scrbCtr = new ScribbleController();
  $scrbCtr->handleScribbleGet();
