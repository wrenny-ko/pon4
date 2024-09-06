<?php
  header("Access-Control-Allow-Methods: GET, PUT, DELETE");

  require_once "../../include/common/includes.php";

  require_once "../../include/Rest.php";
  require_once "../../include/Scribble.php";
  require_once "../../include/User.php";
  require_once "../../include/ScribbleController.php";

  $ctrl = new ScribbleController();
  $ctrl->handle();
