<?php
  header("Access-Control-Allow-Methods: DELETE");

  require_once "../include/common/enableLogging.php"; //TODO remove

  require_once "../include/DatabaseHandler.php";
  require_once "../include/Scribble.php";
  require_once "../include/ScribbleController.php";

  $ctrl = new ScribbleController();

  // error if invalid request
  if ($_SERVER["REQUEST_METHOD"] !== 'DELETE') {
    $ctrl->error("invalid request method. Expect only DELETE");
  }

  // require a scribble id
  if (!isset($_GET["id"])) {
    $ctrl->error("invalid request. Requires an '?id=' query field.");
  }
  $id = $_GET["id"];

  session_start();
  $username = "";
  if (!isset($_SESSION['username'])) {
    $ctrl->error("Route requires login.");
  } else {
    $username = $_SESSION['username'];
  }

  require_once("../include/Perms.php");
  $perms = new Perms($username);

  if (!$perms->hasModerator() && !$perms->hasAdmin()) {
    $ctrl->error("insufficient permission");
  }

  if ($id === "1") {
    $ctrl->error("can't delete the default scribble");
  }

  $ctrl->deleteScribbleUpdateAvatars($id);
  echo json_encode(array("avatars_wiped" => $ctrl->getNumWipedAvatars()));
