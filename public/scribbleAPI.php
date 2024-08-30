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
  $rest->setupLogging("pon4-api.log", "scribble");

  // this either passes through or calls exit() with error reporting
  $rest->validateMethod();

  // require a scribble id
  if (!isset($_GET["id"])) {
    $rest->error("invalid request. Requires an 'id' query field.");
  }
  $id = $_GET["id"];

  // require an action
  if (!isset($_GET["action"])) {
    $rest->error("invalid request. Requires an 'action' query field.");
  }
  $action = $_GET["action"];

  require_once "../include/DatabaseHandler.php";
  require_once "../include/Scribble.php";
  require_once "../include/ScribbleController.php";

  $action;
  try {
    $action = ScribbleAction::from($_GET["action"]);
  } catch (Exception $e) {
    $rest->error("invalid action");
  }

  if(isset(ScribbleController::AuthMap[$action->value])) {
    $rest->setAuths(ScribbleController::AuthMap[$action->value]);
  }

  $rest->auth(); // exit() with error reporting if unauthorized

  $data = null;
  if (isset($_GET["comment"])) {
    $data = $_GET["comment"];
  }

  //session_start(); //called in Rest()
  $username = "anonymous";
  if (isset($_SESSION["username"])) {
    $username = $_SESSION["username"];
  }

  $ctrl = new ScribbleController();
  $res = $ctrl->handleAction($action, $id, $username, $data);

  if ($res !== "") {
    $rest->error($res);
  }

  $rest->success("performed '$action->value' on scribble '$id'");
