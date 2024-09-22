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

  public function getEntryForDB($msg) {
    return array(
      date("Y-m-d H:i:s"),
      $this->endpointName,
      $this->method,
      $this->username,
      $this->success,
      $msg
    );
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
  private string $successMessage;
  private $perms;
  private $pdo;

  // Construct this after calling session_start()
  public function __construct() {
    $this->method = RequestMethod::GET;
    $this->loginRequired = false;
    $this->auths = array();
    $this->data = array();
    $this->responseFields = array();
    $this->successCode = 200;
    $this->successMessage = "";
  }

  public function __destruct() {
    $this->pdo = null;
    $this->data = null;
    $this->responseFields = null;
  }

  public function setPDO($pdo) {
    $this->pdo = $pdo;
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

  public function setSuccessMessage($msg) {
    $this->successMessage = $msg;
  }

  public function compareMethod($method) {
    if ($this->method !== $method) {
      return "method does not match expected (" . $method->value . ")";
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
      return "requires a '$field' query field.";
    }
    return $_GET[$field];
  }

  public function checkRequiredQueryFieldPresent($field) {
    if (!isset($_GET[$field])) {
      return "requires a '$field' query field.";
    }
  }

  public function getUsername() {
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

  public function updateLogRequestMethod() {
    $this->logEntry->setMethod($this->method->value);
  }

  public function getLogFilename() {
    return $this->logFilename;
  }

  // only call this once, when sending the response.
  // calling this earlier apparently causes an http_response_code header to be sent early
  private function log($msg) {
    file_put_contents($this->logFilename, $this->logEntry->getEntry($msg), FILE_APPEND);
  }

  private function logDB($msg) {
    $entry = $this->logEntry->getEntryForDB($msg);
    $statement;
    try {
      $sql = "INSERT INTO logs (timestamp, endpoint, method, username, success, message) VALUES (?, ?, ?, ?, ?, ?)";
      $statement = $this->pdo->prepare($sql);
      if ( !$statement->execute($entry) ) {
        return "Database store failed.";
      }
    } catch (PDOException $e) {
      return "Database query error.";
    } finally {
      $statement = null;
    }

    return "";
  }

  public function error($msg) {
    http_response_code(400);

    $this->logEntry->setSuccess("FAILED");
    $this->log($msg);
    $this->logDB($msg);

    echo json_encode(array("error" => $msg));
  }

  public function success($msg = "") {
    if (!$msg) {
      $msg = $this->successMessage;
    }

    http_response_code($this->successCode);

    $this->logEntry->setSuccess("SUCCESS");
    $this->log($msg);
    $this->logDB($msg);

    $response = array("success" => $msg);

    if ( !!array_keys($this->data) ) {
      $response["data"] = $this->data;
    }

    foreach ($this->responseFields as $key => $value) {
      $response[$key] = $value;
    }

    echo json_encode($response);
  }

  // exits with error reporting if unauthorized
  public function auth() {
    // automatically authorize route if no login required
    if (!$this->loginRequired) {
      return "";
    }

    // if login required, check for session token (username)
    if (!isset($_SESSION['username'])) {
      return "Route requires login.";
    }

    $username = $_SESSION['username'] ?? "anonymous";
    //$this->logEntry->setUsername($username);

    if (count($this->auths) === 0) {
      return ""; // authorize if no auth roles required
    } else {
      // allow access if user has perms for at least one of the listed levels
      $perms = $_SESSION['perms'] ?? new Perms($username);
      $authorized = false;
      foreach ($this->auths as $level) {
        if ($perms->hasLevel($level)) {
          $authorized = true;
          break 1;
        }
      }
      $perms = null;

      if (!$authorized) {
        return "unauthorized";
      }
    }
    return "";
  }
}
