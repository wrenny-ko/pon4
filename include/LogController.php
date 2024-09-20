<?php

enum LogColumn: string {
  case Timestamp = "timestamp";
  case Endpoint  = "endpoint";
  case Method    = "method";
  case Username  = "username";
  case Success    = "success";
  case Message   = "message";
}

enum LogSortDir: int {
  case Asc = SORT_ASC;
  case Desc = SORT_DESC;
}

enum LogAction: string {
  case Read = "read";
  case FetchRows = "fetch_rows";
}

class LogController extends DatabaseHandler {
  const RouteMap = array(
    LogAction::Read->value => array(
      "method" => RequestMethod::GET,
      "login_required" => true,
      "auth_levels" => array(AuthLevel::Tech, AuthLevel::Admin)
    ),
    LogAction::FetchRows->value => array(
      "method" => RequestMethod::GET,
      "login_required" => true,
      "auth_levels" => array(AuthLevel::Tech, AuthLevel::Admin)
    )
  );

  private $rest;
  private $action;
  private $maxRows;

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
    $this->maxRows = 50;

    // set up first for logging/error response if needed
    $this->rest = new Rest();
    $this->rest->setupLogging("api.log", "log");

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

    if (!isset($_GET["action"])) {
      return "requires an 'action' query string";
    }

    $action;
    try {
      $action = LogAction::from($_GET["action"]);
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
  }

  public function __destruct() {
    $this->rest = null;
    $this->pdo = null;
  }

  public function handle() {
    return $this->handleAction();
  }

  private function handleAction() {
    switch ($this->action) {
      case LogAction::Read:
        $endpoint = $this->rest->getQueryField("endpoint");
        return $this->read($endpoint);
        break;
      case LogAction::FetchRows:
        return $this->fetchRows();
        break;
    }
    return "action not found";
  }

  //read log file, select endpoint entries, format to json
  private function read($endpoint) {
    $log = explode(PHP_EOL, file_get_contents($this->rest->getLogFilename()));
    $log = array_reverse($log); // serve the new entries first
    $curated = array();
    foreach ($log as $line) {
      if (!$line) {
        continue;
      }

      // date | endpoint | method | username | success | message
      $lineArr = explode(" | ", $line);

      if (!$endpoint) {
        $curated[] = $line; // show all logs if no endpoint given
      } else {
        if ($lineArr[1] === $endpoint) {
          $curated[] = $line;
        }
      }
    }

    $this->rest->setResponseField("lines", $curated);
    return "";
  }

  private function fetchRows() {
    $draw = $this->rest->getRequiredQueryField("draw");
    $this->rest->setResponseField("draw", $draw);

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
      $sortDir = LogSortDir::Asc;
    } else if ($dir === "desc"){
      $sortDir = LogSortDir::Desc;
    } else {
      return "invalid datatables query. order dir not matched";
    }

    if (!isset($order[0]["name"])) {
      return "invalid datatables query. order column name not present";
    }
    $name = $order[0]["name"];

    $sortCol = LogColumn::tryFrom($name);
    if ($sortCol === null) {
      return "invalid datatables query. order column name not matched";
    }

    $err = $this->populate($sortCol, $sortDir);
    if (!!$err) {
      return "Error populating leaderboard. " . $err;
    }

    //$this->rest->setData($this->board);
    //$this->rest->setResponseField("recordsFiltered", $this->numFilteredEntries);
    $this->rest->setSuccessMessage("sortCol: '" . $sortCol->value . "', sortDir: '" . $dir . "'");
  }

  public function populate(LogColumn $sortCol = LogColumn::Timestamp, LogSortDir $sortDir = LogSortDir::Desc) {
    $entries = array();
    $statement;
    try {
      $sql = "SELECT * FROM logs";

      $search = $_GET["search"]["value"];
      if (!!$search) {
        $sql .= " WHERE {$sortCol->value} LIKE '%$search%'";
      }

      $statement = $this->pdo->prepare($sql);
      if ( !$statement->execute(array()) ) {
        return "Database lookup failed.";
      }

      while ($row = $statement->fetch()) {
        $entries[] = $row;
      }
    } catch (PDOException $e) {
      return "Database error.";
    } finally {
      $statement = null;
    }

    $this->rest->setResponseField("recordsTotal", count($entries));

    // sort users by column and direction
    $dir = $sortDir->value; // have to break this out of the function because of a 'read-only' error below
    array_multisort(array_column($entries, $sortCol->value), $dir, SORT_REGULAR, $entries);

    // truncate for final leaderboard
    $curated = array_slice($entries, 0, $this->maxRows);

    $this->rest->setData($curated);
    $this->rest->setResponseField("recordsFiltered", count($curated));

    return "";
  }
}
