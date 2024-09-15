<?php
// include this file at the top of each view page
$prefix = $_SERVER["DOCUMENT_ROOT"] . "/";

require_once $prefix . "../include/common/enableLogging.php"; //TODO remove; for development debugging only

require_once $prefix . "../include/DatabaseHandler.php";
require_once $prefix . "../include/Perms.php";
require_once $prefix . "../include/Scribble.php"; // for navbar avatar
require_once $prefix . "../include/TicTacToe.php"; // for game object deserialization

// moved below perms, so the Perms class gets loaded before session_start()
require_once $prefix . "../include/common/initSession.php";

$prefix = null;

$username = $_SESSION['username'] ?? "anonymous";
$perms    = $_SESSION['perms']    ?? new Perms($username);
