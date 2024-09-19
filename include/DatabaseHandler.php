<?php

class DatabaseHandler {
  private $hostname = "db";
  private $username;
  private $password;
  private $dbname;
  protected $pdo;

  private function loadIni() {
    $env = parse_ini_file($_SERVER["DOCUMENT_ROOT"] . '/../.env');
    if ($env === false) {
      return "Error parsing database credentials.";
    }

    $this->hostname = $env["DB_HOSTNAME"];
    $this->username = $env["DB_USERNAME"];
    $this->password = $env["DB_PASSWORD"];
    $this->dbname   = $env["DB_NAME"];
    return "";
  }

  public function connect() {
    $msg = $this->loadIni();
    if (!!$msg) {
      return "ini error. " . $msg;
    }

    $pdo;
    try {
      $dsn = 'mysql:host=' . $this->hostname . ';dbname=' . $this->dbname;
      $pdo = new PDO($dsn, $this->username, $this->password);
      $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
      $this->pdo = $pdo;
    } catch(PDOException $e) {
      return "PDO error.";
    } finally {
      $pdo = null;
    }
    return "";
  }

  public function __destruct() {
    $this->pdo = null;
  }

  public function getPDO() {
    return $this->pdo;
  }

  public function setPDO($pdo) {
    $this->pdo = $pdo;
  }
}
