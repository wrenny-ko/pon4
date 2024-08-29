<?php
class DatabaseHandler {
  private $hostname = "db";
  private $username = "root";
  private $password = "root";
  private $dbname = "pon4_db";
  //private $pdo;
  //private $statement;

  public function connect() {
    $dsn = 'mysql:host=' . $this->hostname . ';dbname=' . $this->dbname;
    $pdo = new PDO($dsn, $this->username, $this->password);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    //$this->pdo = $pdo;
    return $pdo; //legacy support
  }
/*
  public function query($sql) {
    $this->connect();
    $statement = $this->pdo->prepare($sql);
    if ($statement === false) {
      return "PDO prepare failed."
    }

    if ( !$statement->execute(array($this->username)) ) {
      return "Database lookup failed.";
    }

    $statement
    return $statement;
  }*/
}


























