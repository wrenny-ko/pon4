<?php
  header("Access-Control-Allow-Methods: POST");

  require_once "../../include/common/includes.php";

  require_once "../../include/Rest.php";
  require_once "../../include/User.php";
  require_once "../../include/UserController.php";
  require_once "../../include/AccountController.php";

  $ctrl = new AccountController();
  $ctrl->handle();
