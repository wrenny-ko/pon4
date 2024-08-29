<?php
require_once "DatabaseHandler.php";

class Perms extends DatabaseHandler {
  private $username;
  private $admin = false; // access to mod powers, tech views, and additional ability to assign roles
  private $mod   = false; // can edit certain things, delete scribbles
  private $tech  = false; // for technical support roles. access to logs but not application perms
  private $beta  = false; // for beta testers. allows any user to view a new version

  public function __construct($username) {
    $this->username = $username;
    $this->readPerms();
  }

  protected function readPerms() {
    $sql = "SELECT * FROM perms INNER JOIN users ON users.username = ? AND perms.user = users.id";
    $pdo = $this->connect();
    $statement = $pdo->prepare($sql);

    if ( !$statement->execute(array($this->username)) ) {
      return "Database lookup failed.";
    }

    if ($statement->rowCount() !== 0) {
      $row = $statement->fetch();
      $this->admin = $row["admin"];
      $this->mod   = $row["mod"];
      $this->tech  = $row["tech"];
      $this->beta  = $row["beta"];
    }

    $statement = null;
    return "";
  }

  public function hasAdmin() {
    return $this->admin;
  }

  public function hasMod() {
    return $this->mod;
  }

  public function hasTech() {
    return $this->tech;
  }

  public function hasBeta() {
    return $this->beta;
  }
/*
  protected function writePerms() {
    // first test for existence of a perms entry
    $sql = "SELECT * FROM perms INNER JOIN users ON users.username = ? AND perms.user = users.id";
    $pdo = $this->connect();
    $statement = $pdo->prepare($sql);

    if ( !$statement->execute(array($this->username)) ) {
      return "Database lookup failed.";
    }

    $sql = "";
    $values = array([]);
    if ($statement->rowCount() === 0) {
      $sql = "INSERT INTO perms (user, admin, mod, tech, beta) VALUES (users.id, ?, ?, ?, ?) FROM users WHERE ";

      $values = array($this->admin, )
    } else {
      $sql = "UPDATE perms SET admin = ?, mod = ?, tech = ?, beta = ? JOIN users WHERE users.username = ? AND perms.user = users.id"
    }

    $statement = $pdo->prepare($sql);
    if ( !$statement->execute(array($this->username)) ) {
      return "Database store failed.";
    }

    $statement = null;
    return "";
  }
*/
}
