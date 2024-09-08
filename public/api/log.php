<?php
  header("Access-Control-Allow-Methods: GET");

  require_once "../../include/common/includes.php";

  require_once "../../include/Rest.php";
  require_once "../../include/LogController.php";

  $ctrl = new LogController();
  $ctrl->handle();

/*
  // TODO refactor Rest.php to not call exit()
  $ctrl->disconnect();
  $ctrl = null;

  require_once "../../include/common/cleanup.php";
*/
