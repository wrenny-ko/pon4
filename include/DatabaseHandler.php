<?php
class DatabaseHandler {
  private $hostname = "db";
  private $username = "root";
  private $password = "root";
  private $dbname = "pon4_db";
  
   function connect() {
    $dsn = 'mysql:host=' . $this->hostname . ';dbname=' . $this->dbname;
    $pdo = new PDO($dsn, $this->username, $this->password);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    return $pdo;
  }
}


























