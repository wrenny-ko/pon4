<?php
  header("Access-Control-Allow-Methods: PUT");

  require_once "../include/common/enableLogging.php"; //TODO remove

  require_once "../include/DatabaseHandler.php";
  require_once "../include/Leaderboard.php";
  require_once "../include/LeaderboardController.php";

  $ctrl = new LeaderboardController();

  // error if invalid request
  if ($_SERVER["REQUEST_METHOD"] !== 'PUT') {
    $ctrl->error("invalid request method. Expect only PUT");
  }

  // require a scribble id
  if (!isset($_GET["maxRows"])) {
    $ctrl->error("invalid request. Requires a '?=maxRows' query field.");
  }
  $maxRows = $_GET["maxRows"];

  session_start();
  $username = "";
  if (!isset($_SESSION['username'])) {
    $ctrl->error("Route requires login.");
  } else {
    $username = $_SESSION['username'];
  }

  require_once("../include/Perms.php");
  $perms = new Perms($username);

  if (!$perms->hasAdmin()) {
    $ctrl->error("insufficient permission, requires admin");
  }

  if ($maxRows < 1) {
    $ctrl->error("can't set max rows to less than 1");
  }

  $ctrl->handlePutLeaderboard($maxRows);
