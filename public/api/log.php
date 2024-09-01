<?php
  require_once "../../include/common/enableLogging.php"; //TODO remove; for development debugging only
  require_once "../../include/common/initSession.php";

  require_once "../../include/DatabaseHandler.php";
  require_once "../../include/Perms.php";
  require_once "../../include/Rest.php";
  require_once "../../include/LogController.php";

  $ctrl = new LogController();
  $ctrl->handle();
