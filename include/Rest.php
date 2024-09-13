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
    $this->username = "unknown";
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

  // date | endpoint | method | username | success | message
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
  private $data;
  private $responseFields;
  private int $successCode;
  private $perms;

  // Construct this after calling session_start()
  public function __construct() {
    $this->method = RequestMethod::GET;
    $this->loginRequired = false;
    $this->auths = array();
    $this->data = array();
    $this->responseFields = array();
    $this->successCode = 200;
    $this->perms = null;
  }

  public function __destruct() {
    if (!!$this->perms) {
      $this->perms->disconnect();
    }
    $this->data = null;
    $this->responseFields = null;
  }

  public function setMethod($method) {
    $this->method = $method;
  }

  public function setLoginRequired($loginRequired) {
    $this->loginRequired = $loginRequired;
  }

  public function setAuths($auths) {
    $this->auths = $auths;
  }

  public function setDataField($field, $val) {
    $this->data[$field] = $val;
  }

  public function setResponseField($field, $val) {
    $this->responseFields[$field] = $val;
  }

  public function setData($data) {
    $this->data = $data;
  }

  public function setSuccessCode($code) {
    $this->successCode = $code;
  }

  public function compareMethod($method) {
    if ($this->method !== $method) {
      $this->error("method does not match expected (" . $method->value . ")");
    }
  }

  public function getQueryField($field) {
    if (!isset($_GET[$field])) {
      return "";
    }
    return $_GET[$field];
  }

  public function getRequiredQueryField($field) {
    if (!isset($_GET[$field])) {
      $this->error("requires a '$field' query field.");
    }
    return $_GET[$field];
  }

  public function checkRequiredQueryFieldPresent($field) {
    if (!isset($_GET[$field])) {
      $this->error("requires a '$field' query field.");
    }
  }

  public function getUsername() {
    require_once "common/initSession.php"; // session_start();

    $username = "anonymous";
    if (isset($_SESSION["username"])) {
      $username = $_SESSION["username"];
    }

    return $username;
  }

  public function setupLogging($logFilename, $endpointName) {
    date_default_timezone_set("UTC");
    $this->logEntry = new RestLogEntry($endpointName);
    $this->logEntry->setMethod($this->method->value);

    $env = parse_ini_file($_SERVER["DOCUMENT_ROOT"] . '/../.env');
    $this->logFilename = $env["LOG_DIR"] . $logFilename;

    $username;
    if (isset($_SESSION["username"])) {
       $username = $_SESSION["username"];
    } else {
      $username = "anonymous";
    }
    $this->logEntry->setUsername($username);
  }

  public function getLogFilename() {
    return $this->logFilename;
  }

  // only call this once, when sending the response.
  // calling this earlier apparently causes an http_response_code header to be sent early
  private function log($msg) {
    file_put_contents($this->logFilename, $this->logEntry->getEntry($msg), FILE_APPEND);
  }

  public function error($msg) {
    if (!!$this->perms) {
      $this->perms->disconnect();
    }

    http_response_code(400);

    $this->logEntry->setSuccess("FAILED");
    $this->log($msg);

    echo json_encode(array("error" => $msg));
    exit();
  }

  public function success($msg) {
    if (!!$this->perms) {
      $this->perms->disconnect();
    }

    http_response_code($this->successCode);

    $this->logEntry->setSuccess("SUCCESS");
    $this->log($msg);

    $response = array("success" => $msg);

    if ( !!array_keys($this->data) ) {
      $response["data"] = $this->data;
    }

    foreach ($this->responseFields as $key => $value) {
      $response[$key] = $value;
    }

    echo json_encode($response);
    exit();
  }

  // exits with error reporting if unauthorized
  public function auth() {
    // automatically authorize route if no login required
    if (!$this->loginRequired) {
      return;
    }

    // if login required, check for session token (username)
    if (!isset($_SESSION['username'])) {
      $this->error("Route requires login.");
    }

    $username = $_SESSION['username'];
    //$this->logEntry->setUsername($username);
    $this->perms = new Perms($username);

    if (count($this->auths) === 0) {
      return; // authorize if no auth roles required
    } else {
      // allow access if user has perms for at least one of the listed levels
      $authorized = false;
      foreach ($this->auths as $level) {
        if ($this->perms->hasLevel($level)) {
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
