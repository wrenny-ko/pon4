<?php
// include this file at the top of each view page
$prefix = $_SERVER["DOCUMENT_ROOT"] . "/";

require_once $prefix . "../include/common/enableLogging.php"; //TODO remove; for development debugging only
require_once $prefix . "../include/common/initSession.php";

require_once $prefix . "../include/DatabaseHandler.php";
require_once $prefix . "../include/Perms.php";
require_once $prefix . "../include/Scribble.php"; // for navbar avatar

$prefix = null;

$username = "anonymous";
if (isset($_SESSION['username'])) {
  $username = $_SESSION['username'];
}

$perms = new Perms($username);
