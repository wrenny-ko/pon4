<?php
  header("Access-Control-Allow-Methods: PUT, POST");

  require_once "../../include/common/includes.php";

  require_once "../../include/Rest.php";
  require_once "../../include/User.php";
  require_once "../../include/AccountController.php";

  $ctrl = new AccountController();
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
