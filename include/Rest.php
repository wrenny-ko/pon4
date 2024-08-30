<?php

enum RequestMethod: string {
  case GET    = "GET";
  case PUT    = "PUT";
  case POST   = "POST";
  case DELETE = "DELETE";
}

class RestLogEntry {
  private $endpointName;
  private $username;
  private $method;
  private $success;

  public function __construct($endpointName) {
    $this->endpointName = $endpointName;
    $this->username = "anonymous";
    $this->method = "unknown";
    $this->success = "unknown";
  }

  public function setUsername($username) {
    $this->username = $username;
  }

  public function setMethod($method) {
    $this->method = $method;
  }

  public function setSuccess($s) {
    $this->success = $s;
  }

  public function getEntry($msg) {
   return date("Y-m-d H:i:s") . " | " . $this->endpointName . " | " . $this->method . " | " . $this->username . " | " . $this->success . " | " . $msg . "\n";
  }
}

class Rest {
  private RequestMethod $method;
  private bool $loginRequired;
  private array $auths;
  private string $logFilename;
  private RestLogEntry $logEntry;
  private Perms $perms;

  public function __construct($method = RequestMethod::GET, $loginRequired = false, $auths = array()) {
    $this->method = $method;
    $this->loginRequired = $loginRequired;
    $this->auths = $auths;
  }

  public function setAuths($auths) {
    $this->auths = $auths;
  }

  public function setupLogging($logFilename, $endpointName) {
    date_default_timezone_set('UTC');
    $this->logEntry = new RestLogEntry($endpointName);
    $this->logEntry->setMethod($this->method->value);
    $this->logFilename = "/var/log/pon4/" . $logFilename;
  }

  public function log($msg) {
    file_put_contents($this->logFilename, $this->logEntry->getEntry($msg), FILE_APPEND);
  }

  public function error($msg) {
    $this->logEntry->setSuccess("FAILED");
    $this->log($msg);
    echo json_encode(array("error" => $msg));
    http_response_code(400);
    exit();
  }

  public function success($msg) {
    $this->logEntry->setSuccess("SUCCESS");
    $this->log($msg);
    echo json_encode(array("success" => $msg));
    http_response_code(200);
    exit();
  }

  // exits with error reporting if method invalid
  public function validateMethod() {
    // error if invalid request
    if ($_SERVER["REQUEST_METHOD"] !== $this->method->value) {
      $this->error("invalid request method. Expect only " . $this->method->value);
    }
  }

  // exits with error reporting if unauthorized
  public function auth() {
    // automatically authorize route if no login required
    if (!$this->loginRequired) {
      return;
    }

    // if login required, check for session token (username)
    session_start(); //TODO move this?
    if (!isset($_SESSION['username'])) {
      $this->error("Route requires login.");
    }

    $username = $_SESSION['username'];
    $this->logEntry->setUsername($username);
    $this->perms = new Perms($username);

    if (count($this->auths) > 0) {
      // allow access if user has perms for at least one of the listed levels
      $authorized = false;
      foreach ($this->auths as $level) {
        if ($perms->hasLevel($level)) {
          $authorized = true;
          break 1;
        }
      }

      if (!$authorized) {
        $this->error("unauthorized");
      }
    }
  }
}
