<?php

class Scribble extends DatabaseHandler {
  protected $username;
  protected $userID;
  protected $likes;
  protected $dislikes;
  protected $title;
  protected $data_url;
  protected $id;
  protected $avatarID;
  protected $scribbleList;
  protected $numWipedAvatars;

  public function getDataURL() {
    return $this->data_url;
  }

  public function getNumWipedAvatars() {
    return $this->numWipedAvatars;
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

    //$title = html_entity_decode($title, ENT_QUOTES | ENT_XML1, 'UTF-8');

    try {
      if ( !$statement->execute(array($uid, htmlspecialchars($title), htmlspecialchars($data_url))) ) {
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

    $statement = null;
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

    $statement = null;
    return "";
  }

  protected function readScribble($id) {
    $sql = <<<EOF
    SELECT users.username, scribbles.id, scribbles.likes, scribbles.dislikes, 
           scribbles.title, scribbles.data_url
    FROM scribbles
    INNER JOIN users ON users.id = scribbles.user  WHERE scribbles.id = ?
EOF;

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
    $this->title    = htmlspecialchars_decode($row['title']);
    $this->data_url = htmlspecialchars_decode($row['data_url']);
    $this->likes    = $row['likes'];
    $this->dislikes = $row['dislikes'];

    $statement = null;
    return "";
  }

  protected function deleteScribble($id) {
    $sql = "DELETE FROM likes WHERE likes.scribble = ?";
    $pdo = $this->connect();
    $statement = $pdo->prepare($sql);

    if ( !$statement->execute(array($id)) ) {
      return "Database delete failed.";
    }

    $sql = "DELETE FROM dislikes WHERE dislikes.scribble = ?";
    $pdo = $this->connect();
    $statement = $pdo->prepare($sql);

    if ( !$statement->execute(array($id)) ) {
      return "Database delete failed.";
    }

    $sql = "DELETE FROM scribbles WHERE id = ?";
    $statement = $pdo->prepare($sql);

    if ( !$statement->execute(array($id)) ) {
      return "Database delete failed.";
    }

    // mysql should return 1 row
    if ($statement->rowCount() === 0) {
      return "Scribble does not exist.";
    }

    $statement = null;
    return "";
  }

  public function getScribble() {
    return array(
      'id'       => $this->id,
      'username' => $this->username,
      'title'    => $this->title,
      'data_url' => $this->data_url,
      'likes'    => $this->likes,
      'dislikes' => $this->dislikes
      //'comments' => $this->comments
    );
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

  // sets all user avatars with $id to the default avatar
  protected function setDefaultAvatars($id) {
    $sql = "UPDATE users SET avatar = 1 WHERE avatar = ?";
    $pdo = $this->connect();
    $statement = $pdo->prepare($sql);

    if ( !$statement->execute(array($id)) ) {
      return "Database update failed.";
    }

    $this->numWipedAvatars = $statement->rowCount();

    $statement = null;
    return "";
  }

  // delete database row for the scribble
  // set all users with scribble avatar to the default avatar
  public function deleteScribbleUpdateAvatars($id) {
    if ($id === "1") {
      return "wont delete the default avatar scribble";
    }

    $error = $this->deleteScribble($id);
    if (!empty($error)) {
      return "Couldn't delete scribble. " . $error;
    }

    $error = $this->setDefaultAvatars($id);
    if (!empty($error)) {
      return "Couldn't update avatars. " . $error;
    }

    $statement = null;
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

    $list = array();
    while ($row = $statement->fetch()) {
      $row['title'] = htmlspecialchars_decode($row['title']);
      $list[] = $row;
    }

    $this->scribbleList = $list;

    $statement = null;
    return "";
  }

  // returns an array of scribble ids and titles
  protected function getScribbleListByUsername($username) {
    $sql = "SELECT users.username, scribbles.id, scribbles.title, scribbles.data_url 
     FROM scribbles INNER JOIN users ON users.id = scribbles.user WHERE users.username = ?";
    $pdo = $this->connect();
    $statement = $pdo->prepare($sql);

    if ( !$statement->execute(array($username)) ) {
      return "Database lookup failed.";
    }

    if ($statement->rowCount() === 0) {
      return "No scribbles exist.";
    }

    $list = array();
    while ($row = $statement->fetch()) {
      $row['title'] = htmlspecialchars_decode($row['title']);
      $list[] = $row;
    }

    $this->scribbleList = $list;

    $statement = null;
    return "";
  }

    // returns an array of scribble ids and titles
  protected function getScribbleSearchTitle($search) {
    $sql = "SELECT users.username, scribbles.id, scribbles.title, scribbles.data_url 
     FROM scribbles INNER JOIN users ON users.id = scribbles.user WHERE scribbles.title LIKE ?";
    $pdo = $this->connect();
    $statement = $pdo->prepare($sql);

    $search = '%' . htmlspecialchars($search) . '%';
    if ( !$statement->execute(array($search)) ) {
      return "Database lookup failed.";
    }

    if ($statement->rowCount() === 0) {
      return "No scribbles exist.";
    }

    $list = array();
    while ($row = $statement->fetch()) {
      $row['title'] = htmlspecialchars_decode($row['title']);
      $list[] = $row;
    }

    $this->scribbleList = $list;

    $statement = null;
    return "";
  }

  protected function removeLike($id, $username) {
    // remove like
    $sql = "DELETE l FROM likes l JOIN users u ON u.username = ? WHERE u.id = l.user AND l.scribble = ?";
    $pdo = $this->connect();
    $statement = $pdo->prepare($sql);

    if ( !$statement->execute(array($username, $id)) ) {
      return "Database delete failed.";
    }

    // update scribble like count if a like was deleted
    if ($statement->rowCount() > 0) {
      $sql = "UPDATE scribbles SET likes = likes - 1 WHERE id = ?";
      $statement = $pdo->prepare($sql);

      if ( !$statement->execute(array($id)) ) {
        return "Database update failed.";
      }
    }

    return "";
  }

  protected function addLike($id, $username) {
    // create like
    // example of using a subquery
    $sql = "INSERT INTO likes (user, scribble) SELECT id, ? FROM users WHERE users.username = ? AND ? IN (SELECT id FROM scribbles WHERE scribbles.id = ?)";
    $pdo = $this->connect();
    $statement = $pdo->prepare($sql);

    if ( !$statement->execute(array($id, $username, $id, $id)) ) {
      return "Database insert failed.";
    }

    // update scribble like count
    $sql = "UPDATE scribbles SET likes = likes + 1 WHERE id = ?";
    $statement = $pdo->prepare($sql);

    if ( !$statement->execute(array($id)) ) {
      return "Database update failed.";
    }

    // remove dislike, if any
    $sql = "DELETE dl FROM dislikes dl JOIN users u ON u.username = ? WHERE u.id = dl.user AND dl.scribble = ?";
    $statement = $pdo->prepare($sql);

    if ( !$statement->execute(array($username, $id)) ) {
      return "Database delete failed.";
    }

    // update scribble dislike count if a dislike was deleted
    if ($statement->rowCount() > 0) {
      $sql = "UPDATE scribbles SET dislikes = dislikes - 1 WHERE id = ?";
      $statement = $pdo->prepare($sql);

      if ( !$statement->execute(array($id)) ) {
        return "Database update failed.";
      }
    }

    return "";
  }

  protected function like($id, $username) {
    // check if the like already exists
    //TODO this sql could be refined
    $sql = "SELECT * FROM likes JOIN users WHERE likes.scribble = ? AND users.username = ? AND likes.user = users.id";
    $pdo = $this->connect();
    $statement = $pdo->prepare($sql);

    if ( !$statement->execute(array($id, $username)) ) {
      return "Database lookup failed.";
    }

    if ($statement->rowCount() === 0) {
      return $this->addLike($id, $username); // like does not exist yet
    }
    return $this->removeLike($id, $username); // like exists
  }

  protected function removeDislike($id, $username) {
    // remove dislike
    $sql = "DELETE dl FROM dislikes dl JOIN users u ON u.username = ? WHERE u.id = dl.user AND dl.scribble = ?";
    $pdo = $this->connect();
    $statement = $pdo->prepare($sql);

    if ( !$statement->execute(array($username, $id)) ) {
      return "Database delete failed.";
    }

    // update scribble dislike count if a dislike was deleted
    if ($statement->rowCount() > 0) {
      $sql = "UPDATE scribbles SET dislikes = dislikes - 1 WHERE id = ?";
      $statement = $pdo->prepare($sql);

      if ( !$statement->execute(array($id)) ) {
        return "Database update failed.";
      }
    }

    return "";
  }

  protected function addDislike($id, $username) {
    // create dislike
    // example of using a subquery
    $sql = "INSERT INTO dislikes (user, scribble) SELECT id, ? FROM users WHERE users.username = ? AND ? IN (SELECT id FROM scribbles WHERE scribbles.id = ?)";
    $pdo = $this->connect();
    $statement = $pdo->prepare($sql);

    if ( !$statement->execute(array($id, $username, $id, $id)) ) {
      return "Database insert failed.";
    }

    // update scribble dislike count
    $sql = "UPDATE scribbles SET dislikes = dislikes + 1 WHERE id = ?";
    $statement = $pdo->prepare($sql);

    if ( !$statement->execute(array($id)) ) {
      return "Database update failed.";
    }

    // remove like, if any
    $sql = "DELETE l FROM likes l JOIN users u ON u.username = ? WHERE u.id = l.user AND l.scribble = ?";
    $statement = $pdo->prepare($sql);

    if ( !$statement->execute(array($username, $id)) ) {
      return "Database delete failed.";
    }


    // update scribble like count if a like was deleted
    if ($statement->rowCount() > 0) {
      $sql = "UPDATE scribbles SET likes = likes - 1 WHERE id = ?";
      $statement = $pdo->prepare($sql);

      if ( !$statement->execute(array($id)) ) {
        return "Database update failed.";
      }
    }

    return "";
  }

  protected function dislike($id, $username) {
    // check if the dislike already exists
    //TODO this sql could be refined
    $sql = "SELECT * FROM dislikes JOIN users WHERE dislikes.scribble = ? AND users.username = ? AND dislikes.user = users.id";
    $pdo = $this->connect();
    $statement = $pdo->prepare($sql);

    if ( !$statement->execute(array($id, $username)) ) {
      return "Database lookup failed.";
    }

    if ($statement->rowCount() === 0) {
      return $this->addDislike($id, $username);
    }
    return $this->removeDislike($id, $username);
  }

  protected function comment($id, $username, $msg) {
    //TODO
    return "Not implemented";
  }
}
