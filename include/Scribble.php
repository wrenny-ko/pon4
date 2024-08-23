<?php

class Scribble extends DatabaseHandler {
  protected $username;
  protected $userID;
  protected $data_url;
  protected $id;
  protected $avatarID;

  public function getDataURL() {
    return $this->data_url;
  }

  protected function createScribble($username, $data_url) {
    $sql = "SELECT id FROM users WHERE username = ?";
    $pdo = $this->connect();
    $statement = $pdo->prepare($sql);

    if ( !$statement->execute(array($username)) ) {
      return "Database username lookup failed.";
    }

    $uid = 1;
    if ($statement->rowCount() !== 0) {
      $row = $statement->fetch();
      $uid = $row['id'];
    }

    $sql = "INSERT INTO scribbles (user, data_url) VALUES (?, ?)";
    $pdo = $this->connect();
    $statement = $pdo->prepare($sql);

    try {
      if ( !$statement->execute(array($uid, $data_url)) ) {
        return "Database insert failed.";
      }
    } catch (Exception $e) {
        return "Database error.";
    }

    $this->id = $pdo->lastInsertId();

    $statement = null;
    return "";
  }

  protected function readUserID($username) {
    $sql = "SELECT id FROM users WHERE username = ?";
    $pdo = $this->connect();
    $statement = $pdo->prepare($sql);

    if ( !$statement->execute(array($username)) ) {
      return "Database lookup failed.";
    }

    if ($statement->rowCount() === 0) {
      return "User does not exist.";
    }

    $row = $statement->fetch();
    $this->userID = $row['id'];
    return "";
  }

  protected function readAvatarID($username) {
    $sql = "SELECT avatar FROM users WHERE username = ?";
    $pdo = $this->connect();
    $statement = $pdo->prepare($sql);

    if ( !$statement->execute(array($username)) ) {
      return "Database lookup failed.";
    }

    if ($statement->rowCount() === 0) {
      return "User does not exist.";
    }

    $row = $statement->fetch();
    $this->avatarID = $row['avatar'];
    return "";
  }

  protected function readUserName($id) {
    $sql = "SELECT username FROM users WHERE id = ?";
    $pdo = $this->connect();
    $statement = $pdo->prepare($sql);

    if ( !$statement->execute(array($id)) ) {
      return "Database lookup failed.";
    }

    if ($statement->rowCount() === 0) {
      return "User does not exist.";
    }

    $row = $statement->fetch();
    $this->username = $row['username'];
    return "";
  }

  protected function readScribble($id) {
    $sql = "SELECT * FROM scribbles WHERE id = ?";
    $pdo = $this->connect();
    $statement = $pdo->prepare($sql);

    if ( !$statement->execute(array($id)) ) {
      return "Database lookup failed.";
    }

    if ($statement->rowCount() === 0) {
      return "Scribble does not exist.";
    }

    $row = $statement->fetch();
    $this->username = $row['user'];
    $this->data_url = $row['data_url'];

    return "";
  }

  protected function getScribble() {
    return array(
      'username' => $this->username, 
      'data_url' => $this->data_url
    );
  }

  protected function setScribble($username, $data_url) {
    $this->username = $username;
    $this->data_url = $data_url;
  }

  public function readScribbleAvatar($username) {
    $error = $this->readAvatarID($username);
    if (!empty($error)) {
      return "Error reading avatar ID. " . $error;
    }

    $error = $this->readScribble($this->avatarID);
    if (!empty($error)) {
      return "Error reading scribble avatar. " . $error;
    }

    return "";
  }
}
