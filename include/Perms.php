<?php

enum AuthLevel: string {
  case Admin     = "admin"; // access to mod powers, tech views, and additional ability to assign roles
  case Moderator = "moderator";   // can edit certain things, delete scribbles
  case Tech      = "tech";  // for technical support roles. access to logs but not application perms
  case Beta      = "beta";  // for beta testers. allows any user to view a new version
}

class Perms extends DatabaseHandler {
  private string $username;
  private array $roles;

  public function __construct($username) {
    $this->username = $username;
    $this->roles["admin"] = false;
    $this->roles["moderator"]   = false;
    $this->roles["tech"]  = false;
    $this->roles["beta"]  = false;
  }

  public function readPerms() {
    $statement;
    try {
      $sql = "SELECT * FROM perms INNER JOIN users ON users.username = ? AND perms.user = users.id";
      $statement = $this->pdo->prepare($sql);
      if ( !$statement->execute(array($this->username)) ) {
        return "Database lookup failed.";
      }

      if ($statement->rowCount() !== 0) {
        $row = $statement->fetch();
        $this->roles["admin"] = $row["admin"];
        $this->roles["moderator"]   = $row["moderator"];
        $this->roles["tech"]  = $row["tech"];
        $this->roles["beta"]  = $row["beta"];
      }
    } catch (PDOException $e) {
      return "Database execute error.";
    } finally {
      $statement = null;
    }

    return "";
  }

  public function hasAdmin() {
    return $this->roles["admin"];
  }

  public function hasModerator() {
    return $this->roles["moderator"];
  }

  public function hasTech() {
    return $this->roles["tech"];
  }

  public function hasBeta() {
    return $this->roles["beta"];
  }

  public function hasLevel(AuthLevel $level) {
    return $this->roles[$level->value];
  }

  // write one auth level
  protected function writeLevel($username, AuthLevel $level, $has) {
    $statement;
    try {
      $sql = "INSERT INTO perms (user, ?) SELECT id, ? FROM users WHERE users.username = ?
              ON DUPLICATE KEY UPDATE ? = ?";
      $statement = $this->pdo->prepare($sql);
      if ( !$statement->execute(array($level->value, $has, $username, $level->value, $has)) ) {
        return "Database store failed.";
      }
    } catch (PDOException $e) {
      return "Database execute error.";
    } finally {
      $statement = null;
    }

    return "";
  }

  // remove all perms from a user
  protected function wipeAuths($username) {
    $sql = "DELETE FROM perms JOIN users WHERE users.username = ? AND perms.user = users.id";
    try {
      $statement = $this->pdo->prepare($sql);
      if ( !$statement->execute(array($this->username)) ) {
        return "Database store failed.";
      }
    } catch (PDOException $e) {
      return "Database execute error.";
    } finally {
      $statement = null;
    }

    return "";
  }
}
