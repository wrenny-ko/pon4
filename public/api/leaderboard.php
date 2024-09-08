<?php
  header("Access-Control-Allow-Methods: GET, PUT");

  require_once "../../include/common/includes.php";

  require_once "../../include/Rest.php";
  require_once "../../include/Leaderboard.php";
  require_once "../../include/LeaderboardController.php";

  $ctrl = new LeaderboardController();
  $ctrl->handle();

/*
  // TODO refactor Rest.php to not call exit()
  $ctrl->disconnect();
  $ctrl = null;

  require_once "../../include/common/cleanup.php";
*/
