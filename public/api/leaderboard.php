<?php
  require_once "../../include/common/enableLogging.php"; //TODO remove; for development debugging only
  require_once "../../include/common/initSession.php";

  require_once "../../include/DatabaseHandler.php";
  require_once "../../include/Perms.php";
  require_once "../../include/Rest.php";
  require_once "../../include/Leaderboard.php";
  require_once "../../include/LeaderboardController.php";

  //echo json_encode($_GET);
  file_put_contents("/var/log/pon4/tab.log", json_encode($_GET) . "\n", FILE_APPEND);

  $ctrl = new LeaderboardController();
  $ctrl->handle();
