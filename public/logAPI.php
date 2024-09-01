<?php
  header("Access-Control-Allow-Methods: PUT, DELETE");

  require_once "../include/common/enableLogging.php"; //TODO remove; for development debugging only

  require_once "../include/Rest.php";
  require_once "../include/Perms.php";

  $method;
  try {
    $method = RequestMethod::from($_SERVER["REQUEST_METHOD"]);
  } catch (Exception $e) {
    $method = RequestMethod::PUT;
  }

  $loginRequired = true;
  $rest = new Rest($method, $loginRequired);
  $rest->setupLogging("pon4-api.log", "log");

  // this either passes through or calls exit() with error reporting
  $rest->validateMethod();

  // require a scribble id
  if (!isset($_GET["endpoint"])) {
    $rest->error("invalid request. Requires an 'endpoint' query field.");
  }
  $endpoint = $_GET["endpoint"];

  // require an action
  if (!isset($_GET["action"])) {
    $rest->error("invalid request. Requires an 'action' query field.");
  }
  $action = $_GET["action"];

  //require_once "../include/DatabaseHandler.php";
  require_once "../include/Log.php";

  $action;
  try {
    $action = LogAction::from($_GET["action"]);
  } catch (Exception $e) {
    $rest->error("invalid action");
  }

  if(isset(LogController::AuthMap[$action->value])) {
    $rest->setAuths(LogController::AuthMap[$action->value]);
  }

  $rest->auth(); // exit() with error reporting if unauthorized

  //session_start(); //called in Rest()
  /*
  $username = "anonymous";
  if (isset($_SESSION["username"])) {
    $username = $_SESSION["username"];
  }
  */

  $ctrl = new LogController($rest->getLogFilename());
  $res = $ctrl->handleAction($action, $endpoint);

  if ($res !== "") {
    $rest->error($res);
  }

  $rest->success("performed '$action->value' for endpoint '$endpoint'", $ctrl->getData());
