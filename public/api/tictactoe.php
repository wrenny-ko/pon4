<?php
  header("Access-Control-Allow-Methods: GET, POST");

  require_once "../../include/common/includes.php";

  require_once "../../include/Rest.php";

  // call this instead in common/includes.php, before session_start() for session object deserialization
  ///////////////
  //require_once "../../include/TicTacToe.php";

  require_once "../../include/TicTacToeController.php";

  $ctrl = new TicTacToeController();
  //$ctrl->setPDO($pdo); // $pdo declared in include/common/includes.php

  $msg = $ctrl->run();
  if (!!$msg) {
    $ctrl->error($msg);
  } else {
    $ctrl->success();
  }

  //$ctrl->setPDO(null);
  $ctrl = null;

  require_once "../../include/common/cleanup.php";
