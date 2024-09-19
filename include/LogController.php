<?php

enum LogAction: string {
  case Read = "read";
}

class LogController {
  const RouteMap = array(
    LogAction::Read->value => array(
      "method" => RequestMethod::GET,
      "login_required" => true,
      "auth_levels" => array(AuthLevel::Tech, AuthLevel::Admin)
    )
  );

  private $rest;
  private $action;

  public function error($msg) {
    $this->rest->error($msg);
  }

  public function success() {
    $this->rest->success($this->action->value);
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
}
