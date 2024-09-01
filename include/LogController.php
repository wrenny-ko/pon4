<?php
require_once "../include/Perms.php";

enum LogAction: string {
  case Read = "read";
}

class LogController {
  private $data;
  private $logFilename;

  const AuthMap = array(
    LogAction::Read->value => array(AuthLevel::Tech, AuthLevel::Admin)
  );

  public function __construct($logFilename) {
    $this->logFilename = $logFilename;
  }

  public function getData() {
    return $data;
  }

  public function handleAction($action, $endpoint) {
    switch ($action) {
      case LogAction::Read:
        return $this->read($endpoint);
        break;
    }
    return "Action not found";
  }

  //read log file, select endpoint entries, format to json
  public function read($endpoint) {
    //TODO try catch wrapping on file read
    $log = explode(PHP_EOL, file_get_contents($this->logFilename));
    $curated = array();
    foreach ($log as $line) {
      // date | endpoint | method | username | success | message
      $lineArr = explode(" | ", $line);
      
      //TODO make this case insensitive?
      if ($lineArr[1] === $endpoint) {
        $curated[] = $line;
      }
    }

    $this->data = $curated;
    return "";
  }
}
