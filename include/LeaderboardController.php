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

  public function error($msg) {
    $this->rest->setPDO($this->pdo);
    $this->rest->error($msg);
    $this->rest->setPDO(null);
  }

  public function success() {
    $this->rest->setPDO($this->pdo);
    $this->rest->success($this->action->value);
    $this->rest->setPDO(null);
  }

  public function run() {
    $msg = $this->init();
    if (!!$msg) {
      return $msg;
    }

    $msg = $this->handle();
    if (!!$msg) {
      return $msg;
    }

    return "";
  }

  public function init() {
    // set up first for logging/error response if needed
    $this->rest = new Rest();
    $this->rest->setupLogging("api.log", "leaderboard");

    if (!isset($_SERVER["REQUEST_METHOD"])) {
      return "request method not set";
    }

    $method;
    try {
      $method = RequestMethod::from($_SERVER["REQUEST_METHOD"]);
    } catch (\Throwable $e) {
      return "request method not supported";
    }
    $this->rest->setMethod($method);
    $this->rest->updateLogRequestMethod();

    if (!isset($_GET["action"])) {
      return "requires an 'action' query string";
    }

    $action;
    try {
      $action = LeaderboardAction::from($_GET["action"]);
    } catch (\Throwable $e) {
      return "action not supported";
    }
    $this->action = $action;

    $this->rest->setLoginRequired( self::RouteMap[$action->value]["login_required"] );
    $this->rest->setAuths( self::RouteMap[$action->value]["auth_levels"] );

    $msg = $this->rest->compareMethod(self::RouteMap[$action->value]["method"] );
    if (!!$msg) {
      return $msg;
    }

    $msg = $this->rest->auth();
    if (!!$msg) {
      return $msg;
    }

    $err = $this->read();
    if (!!$err) {
      return "Database read error. " . $err;
    }
  }

  public function handle() {
    return $this->handleAction();
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
    // if no max given, set default of 10
    $max = "10";

    // if max given, validate int
    if (!!$maxRows) {
      if (!filter_var( $maxRows, FILTER_VALIDATE_INT, array('options' => array( 'min_range' => 0)) )) {
        return "invalid max_rows value: requires integer greater than 0";
      }
      $max = $maxRows;
    }

    $this->writeMaxRows($max);
    $this->rest->setSuccessMessage("set max rows to '$max'");
    return "";
  }

  private function fetchRows() {
    $draw = $this->rest->getRequiredQueryField("draw");
    $this->rest->setResponseField("draw", $draw);

    $this->rest->setResponseField("recordsTotal", $this->numTotalEntries);

    if (!isset($_GET["order"])) {
      return "invalid datatables query. sort order not present";
    }
    $order = $_GET["order"];

    if (!isset($order[0])) {
      return "invalid datatables query. sort order not present";
    }

    if (!isset($order[0]["dir"])) {
      return "invalid datatables query. order dir not present";
    }
    $dir = $order[0]["dir"];

    $sortDir;
    if ($dir === "asc") {
      $sortDir = LeaderboardSortDir::Up;
    } else if ($dir === "desc"){
      $sortDir = LeaderboardSortDir::Down;
    } else {
      return "invalid datatables query. order dir not matched";
    }

    if (!isset($order[0]["name"])) {
      return "invalid datatables query. order column name not present";
    }
    $name = $order[0]["name"];

    $sortCol = LeaderboardColumn::tryFrom($name);
    if ($sortCol === null) {
      return "invalid datatables query. order column name not matched";
    }

    if (!isset($_GET["search"])) {
      return "Invalid datatables request. 'search' query parameter not set.";
    }

    if (!isset($_GET["search"]["value"])) {
      return "Invalid datatables request. search value query parameter not set.";
    }

    $search = $_GET["search"]["value"];
    $err = $this->populate($sortCol, $sortDir, $search);
    if (!!$err) {
      return "Error populating leaderboard. " . $err;
    }

    $this->rest->setData($this->board);
    $this->rest->setResponseField("recordsFiltered", $this->numFilteredEntries);
    $this->rest->setSuccessMessage("sortCol: '" . $sortCol->value . "', sortDir: '" . $dir . "'");
  }
}
