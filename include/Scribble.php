<?php

class Scribble extends DatabaseHandler {
  protected $username;
  protected $userID;
  protected $title;
  protected $data_url;
  protected $id;
  protected $avatarID;
  protected $scribbleList;

  public function getDataURL() {
    return $this->data_url;
  }

  protected function createScribble($username, $title, $data_url) {
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

    $sql = "INSERT INTO scribbles (user, title, data_url) VALUES (?, ?, ?)";
    $pdo = $this->connect();
    $statement = $pdo->prepare($sql);

    try {
      if ( !$statement->execute(array($uid, $title, $data_url)) ) {
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
    $sql = "SELECT users.username, scribbles.id, scribbles.title, scribbles.data_url 
     FROM scribbles INNER JOIN users ON users.id = scribbles.user  WHERE scribbles.id = ?";
    $pdo = $this->connect();
    $statement = $pdo->prepare($sql);

    if ( !$statement->execute(array($id)) ) {
      return "Database lookup failed.";
    }

    if ($statement->rowCount() === 0) {
      return "Scribble does not exist.";
    }

    $row = $statement->fetch();
    $this->id       = $row['id'];
    $this->username = $row['username'];
    $this->title    = $row['title'];
    $this->data_url = $row['data_url'];

    return "";
  }

  protected function deleteScribble($id) {
    $sql = "DELETE FROM scribbles WHERE id = ?";
    $pdo = $this->connect();
    $statement = $pdo->prepare($sql);

    if ( !$statement->execute(array($id)) ) {
      return "Database delete failed.";
    }

    // mysql should return 1 row
    if ($statement->rowCount() === 0) {
      return "Scribble does not exist.";
    }

    return "";
  }

  protected function getScribble() {
    return array(
      'id'       => $this->id,
      'username' => $this->username,
      'title'    => $this->title,
      'data_url' => $this->data_url
    );
  }

  protected function setScribble($username, $title, $data_url) {
    $this->username = $username;
    $this->title    = $title;
    $this->data_url = $data_url;
  }

  protected function readScribbleAvatar($username) {
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

  // sets all user avatars with $id to the default avatar
  protected function setDefaultAvatars($id) {
    $sql = "UPDATE scribbles SET avatar = 1 WHERE id = ?";
    $pdo = $this->connect();
    $statement = $pdo->prepare($sql);

    if ( !$statement->execute(array($id)) ) {
      return "Database update failed.";
    }

    return "";
  }

  // delete database row for the scribble
  // set all users with scribble avatar to the default avatar
  public function deleteScribbleUpdateAvatars($id) {
    $error = $this->deleteScribble($id);
    if (!empty($error)) {
      return "Couldn't delete scribble. " . $error;
    }

    $error = $this->setAvatars($id);
    if (!empty($error)) {
      return "Couldn't update avatars. " . $error;
    }

    return "";
  }

  // returns an array of scribble ids and titles
  protected function getScribbleList() {
    $sql = "SELECT users.username, scribbles.id, scribbles.title, scribbles.data_url 
     FROM scribbles INNER JOIN users ON users.id = scribbles.user";
    $pdo = $this->connect();
    $statement = $pdo->prepare($sql);

    if ( !$statement->execute() ) {
      return "Database lookup failed.";
    }

    if ($statement->rowCount() === 0) {
      return "No scribbles exist.";
    }

    $this->scribbleList = $statement->fetchAll();

    return "";
  }
}
