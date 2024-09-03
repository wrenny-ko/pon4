<?php
  require_once "../../include/common/includes.php";

  require_once "../../include/Rest.php";
  require_once "../../include/Leaderboard.php";
  require_once "../../include/LeaderboardController.php";

  $ctrl = new LeaderboardController();
  $ctrl->handle();
