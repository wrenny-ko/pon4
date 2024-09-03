<?php
  require_once "../../include/common/includes.php";

  require_once "../../include/Rest.php";
  require_once "../../include/LogController.php";

  $ctrl = new LogController();
  $ctrl->handle();
