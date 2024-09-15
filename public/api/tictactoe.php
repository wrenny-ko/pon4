<?php
  header("Access-Control-Allow-Methods: GET, POST");

  require_once "../../include/common/includes.php";

  require_once "../../include/Rest.php";
  //require_once "../../include/TicTacToe.php"; // call this in common/includes.php, before session_start()
  require_once "../../include/TicTacToeController.php";

  $ctrl = new TicTacToeController();
  $ctrl->handle();

/*
  // TODO refactor Rest.php to not call exit()
  $ctrl->disconnect();
  $ctrl = null;

  require_once "../../include/common/cleanup.php";
*/
