<?php
  header("Access-Control-Allow-Methods: GET, PUT");

  require_once "../../include/common/includes.php";

  require_once "../../include/Rest.php";
  require_once "../../include/Leaderboard.php";
  require_once "../../include/LeaderboardController.php";

  $ctrl = new LeaderboardController();
  $ctrl->setPDO($pdo); // $pdo declared in include/common/includes.php

  $msg = $ctrl->run();
  if (!!$msg) {
    $ctrl->error($msg);
  } else {
    $ctrl->success();
  }

  $ctrl->setPDO(null);
  $ctrl = null;

  require_once "../../include/common/cleanup.php";
