<?php

enum LeaderboardAction: string {
  case SetMaxRows = "set_max_rows";
  case SetHiddenColumn = "set_hidden_column";
  case UnsetHiddenColumn = "unset_hidden_column";
}

class LeaderboardController extends Leaderboard {
  const RouteMap = array(
    LeaderboardAction::SetMaxRows->value => array(
      "method" => RequestMethod::PUT,
      "login_required" => true,
      "auth_levels" => array(AuthLevel::Admin)
    )
  );

  private $rest;
  private $action;

  public function __construct() {
    // set up first for logging/error response if needed
    $this->rest = new Rest();
    $this->rest->setupLogging("api.log", "leaderboard");

    if (!isset($_SERVER["REQUEST_METHOD"])) {
      $this->rest->error("request method not set");
    }

    $method;
    try {
      $method = RequestMethod::from($_SERVER["REQUEST_METHOD"]);
    } catch (\Throwable $e) {
      $this->rest->error("request method not supported");
    }

    $this->rest->setMethod($method);

    if (!isset($_GET["action"])) {
      $this->rest->error("requires an 'action' query string");
    }

    $action;
    try {
      $action = LeaderboardAction::from($_GET["action"]);
    } catch (\Throwable $e) {
      $this->rest->error("action not supported");
    }
    $this->action = $action;

    $this->rest->setLoginRequired( self::RouteMap[$action->value]["login_required"] );
    $this->rest->setAuths( self::RouteMap[$action->value]["auth_levels"] );

    $this->rest->compareMethod(self::RouteMap[$action->value]["method"] );
    $this->rest->auth();
  }

  public function handle() {
    $msg = $this->handleAction();
    if (!empty($msg)) {
      $this->rest->error($msg);
    }
    $this->rest->success($this->action->value);
  }

  private function handleAction() {
    switch ($this->action) {
      case LeaderboardAction::SetMaxRows:
        $maxRows = $this->rest->getQueryField("max_rows");
        return $this->setMaxRows($maxRows);
        break;
      case LeaderboardAction::SetHiddenColumn:
      case LeaderboardAction::UnsetHiddenColumn:
        return "not implemented";
        break;
    }
    return "action not found";
  }

  private function setMaxRows($maxRows) {
    $max = "10";
    if (!empty($maxRows)) {
      if (!filter_var( $maxRows, FILTER_VALIDATE_INT, array('options' => array( 'min_range' => 0)) )) {
        $this->error("invalid max_rows value: requires integer greater than 0");
      }
      $max = $maxRows;
    }

    $this->writeMaxRows($max);
    return "";
  }
}
