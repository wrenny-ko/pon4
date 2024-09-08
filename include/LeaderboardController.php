<?php

enum LeaderboardAction: string {
  case SetMaxRows = "set_max_rows";
  case FetchRows  = "fetch_rows";
}

class LeaderboardController extends Leaderboard {
  const RouteMap = array(
    LeaderboardAction::SetMaxRows->value => array(
      "method" => RequestMethod::PUT,
      "login_required" => true,
      "auth_levels" => array(AuthLevel::Admin)
    ),
    LeaderboardAction::FetchRows->value => array(
      "method" => RequestMethod::GET,
      "login_required" => false,
      "auth_levels" => array()
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

    $err = $this->connect();
    if (!!$err) {
      return "Database connect error. " . $err;
    }

    $this->readMaxRows();
    $this->readNumTotalEntries();
  }

  public function handle() {
    $msg = $this->handleAction();
    if (!!$msg) {
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
      case LeaderboardAction::FetchRows:
        return $this->fetchRows();
        break;
    }
    return "action not found";
  }

  private function setMaxRows($maxRows) {
    $max = "10";
    if (!!$maxRows) {
      if (!filter_var( $maxRows, FILTER_VALIDATE_INT, array('options' => array( 'min_range' => 0)) )) {
        $this->rest->error("invalid max_rows value: requires integer greater than 0");
      }
      $max = $maxRows;
    }

    $this->writeMaxRows($max);
    return "";
  }

  private function fetchRows() {
    $draw = $this->rest->getRequiredQueryField("draw");
    $this->rest->setResponseField("draw", $draw);

    $this->rest->setResponseField("recordsTotal", $this->numTotalEntries);

    if (!isset($_GET["order"])) {
      $this->rest->error("invalid datatables query. sort order not present");
    }
    $order = $_GET["order"];

    if (!isset($order[0])) {
      $this->rest->error("invalid datatables query. sort order not present");
    }

    if (!isset($order[0]["dir"])) {
      $this->rest->error("invalid datatables query. order dir not present");
    }
    $dir = $order[0]["dir"];

    $sortDir;
    if ($dir === "asc") {
      $sortDir = LeaderboardSortDir::Up;
    } else if ($dir === "desc"){
      $sortDir = LeaderboardSortDir::Down;
    } else {
      $this->rest->error("invalid datatables query. order dir not matched");
    }

    if (!isset($order[0]["name"])) {
      $this->rest->error("invalid datatables query. order column name not present");
    }
    $name = $order[0]["name"];

    $sortCol = LeaderboardColumn::tryFrom($name);
    if ($sortCol === null) {
      $this->rest->error("invalid datatables query. order column name not matched");
    }

    $err = $this->populate($sortCol, $sortDir);
    if (!!$err) {
      return "Error populating leaderboard. " . $err;
    }

    $this->rest->setData($this->board);
    $this->rest->setResponseField("recordsFiltered", $this->numFilteredEntries);
    $this->rest->success("sortCol: '" . $sortCol->value . "', sortDir: '" . $dir . "'");
  }
}
