<?php
  header("Access-Control-Allow-Methods: DELETE, GET, POST, PUT");

  require_once "../../include/common/includes.php";

  require_once "../../include/Rest.php";
  require_once "../../include/Scribble.php";
  require_once "../../include/User.php";
  require_once "../../include/ScribbleController.php";

  $ctrl = new ScribbleController();
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
