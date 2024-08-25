<?php
  header("Access-Control-Allow-Methods: POST");

  require_once "../include/common/enableLogging.php"; //TODO remove

  require_once "../include/DatabaseHandler.php";
  require_once "../include/Scribble.php";
  require_once "../include/ScribbleController.php";
  require_once "../include/common/util.php";

  $ScribCtr = new ScribbleController();
  $ScribCtr->handlePost();
