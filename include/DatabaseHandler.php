<?php
class DatabaseHandler {
  private $hostname = "db";
  private $username;
  private $password;
  private $dbname;
  //private $pdo;

  private function loadIni() {
    $env = parse_ini_file($_SERVER["DOCUMENT_ROOT"] . '/../.env');
    $this->hostname = $env["DB_HOSTNAME"];
    $this->username = $env["DB_USERNAME"];
    $this->password = $env["DB_PASSWORD"];
    $this->dbname   = $env["DB_NAME"];
  }

  public function connect() {
    $this->loadIni();
    try {
      $dsn = 'mysql:host=' . $this->hostname . ';dbname=' . $this->dbname;
      $pdo = new PDO($dsn, $this->username, $this->password);
      $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    } catch(PDOException $e) {
      return null;
    }
    return $pdo; //legacy support
  }
}


























