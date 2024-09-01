<?php
  header("Access-Control-Allow-Methods: GET, PUT, DELETE");

  require_once "../../include/common/enableLogging.php"; //TODO remove; for development debugging only
  require_once "../../include/common/initSession.php";

  require_once "../../include/DatabaseHandler.php";
  require_once "../../include/Perms.php";
  require_once "../../include/Rest.php";
  require_once "../../include/Scribble.php";
  require_once "../../include/ScribbleController.php";


  $ctrl = new ScribbleController();
  $res = $ctrl->handle();
