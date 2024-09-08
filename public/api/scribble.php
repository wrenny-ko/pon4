<?php
  header("Access-Control-Allow-Methods: DELETE, GET, POST, PUT");

  require_once "../../include/common/includes.php";

  require_once "../../include/Rest.php";
  require_once "../../include/Scribble.php";
  require_once "../../include/User.php";
  require_once "../../include/ScribbleController.php";

  $ctrl = new ScribbleController();
  $ctrl->handle();

/*
  // TODO refactor Rest.php to not call exit()
  $ctrl->disconnect();
  $ctrl = null;

  require_once "../../include/common/cleanup.php";
*/
