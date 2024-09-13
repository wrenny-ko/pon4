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
  protected $user_data;

  public function __construct() {
    $err = $this->connect();
    if (!!$err) {
      return "Database connect error. " . $err;
    }
  }

  public function __destruct() {
    $this->pdo = null;
  }

  public function disconnect() {
    $this->pdo = null;
  }

  public function getDataURL() {
    return $this->data_url;
  }

  public function getNumWipedAvatars() {
    return $this->numWipedAvatars;
  }

  protected function createScribble($username, $title, $data_url) {
    $sql = "SELECT id FROM users WHERE username = ?";
    $statement;
    try {
      $statement = $this->pdo->prepare($sql);
      if ( !$statement->execute(array($username)) ) {
        return "Database username lookup failed.";
      }

      $uid = 1;
      if ($statement->rowCount() !== 0) {
        $row = $statement->fetch();
        $uid = $row['id'];
      }

      $sql = "INSERT INTO scribbles (user, title, data_url) VALUES (?, ?, ?)";
      $statement = $this->pdo->prepare($sql);

      if ( !$statement->execute(array($uid, htmlspecialchars($title), htmlspecialchars($data_url))) ) {
        return "Database insert failed.";
      }

      $this->id = $this->pdo->lastInsertId();
    } catch (PDOException $e) {
        return "Database execute error.";
    }

    $statement = null;
    return "";
  }

  protected function readUserID($username) {
    $sql = "SELECT id FROM users WHERE username = ?";

    $statement;
    try {
      $statement = $this->pdo->prepare($sql);
      if ( !$statement->execute(array($username)) ) {
        return "Database lookup failed.";
      }

      if ($statement->rowCount() === 0) {
        return "User does not exist.";
      }

      $row = $statement->fetch();
      $this->userID = $row['id'];
    } catch (PDOException $e) {
        return "Database execute error.";
    }

    $statement = null;
    return "";
  }

  protected function readAvatarID($username) {
    $sql = "SELECT avatar FROM users WHERE username = ?";

    $statement;
    try {
      $statement = $this->pdo->prepare($sql);

      if ( !$statement->execute(array($username)) ) {
        return "Database lookup failed.";
      }

      if ($statement->rowCount() === 0) {
        return "User does not exist.";
      }

      $row = $statement->fetch();
      $this->avatarID = $row['avatar'];
    } catch (PDOException $e) {
        return "Database execute error.";
    }

    $statement = null;
    return "";
  }

  public function readScribble($id) {
    $sql = <<<EOF
    SELECT users.username, scribbles.id, scribbles.likes, scribbles.dislikes, 
           scribbles.title, scribbles.data_url
    FROM scribbles
    INNER JOIN users ON users.id = scribbles.user  WHERE scribbles.id = ?
EOF;

    $statement;
    try {
      $statement = $this->pdo->prepare($sql);
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
    } catch (PDOException $e) {
        return "Database execute error.";
    }

    $statement = null;
    return "";
  }

  protected function deleteScribble($id) {
    $sql = "DELETE FROM likes WHERE likes.scribble = ?";

    $statement;
    try {
      $statement = $this->pdo->prepare($sql);

      if ( !$statement->execute(array($id)) ) {
        return "Database delete failed.";
      }

      $sql = "DELETE FROM dislikes WHERE dislikes.scribble = ?";
      $statement = $this->pdo->prepare($sql);

      if ( !$statement->execute(array($id)) ) {
        return "Database delete failed.";
      }

      $sql = "DELETE FROM scribbles WHERE id = ?";
      $statement = $this->pdo->prepare($sql);

      if ( !$statement->execute(array($id)) ) {
        return "Database delete failed.";
      }

      // mysql should return 1 row
      if ($statement->rowCount() === 0) {
        return "Scribble does not exist.";
      }
    } catch (PDOException $e) {
        return "Database execute error.";
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

  public function getMetadata() {
    return array(
      'id'       => $this->id,
      'username' => $this->username,
      'title'    => $this->title,
      'likes'    => $this->likes,
      'dislikes' => $this->dislikes,
      'user_data' => $this->user_data
    );
  }

  public function readMetadata($id, $username) {
    $err = $this->readScribble($id);
    if (!!$err) {
      return "Error reading scribble. " . $err;
    }

    $user_data = array();

    $sql = "SELECT * FROM likes JOIN users WHERE likes.scribble = ? AND likes.user = users.id AND users.username = ?";

    $statement;
    try {
      $statement = $this->pdo->prepare($sql);
      if ( !$statement->execute(array($id, $username)) ) {
        return "Database update failed.";
      }

      $user_data['liked'] = ($statement->rowCount() > 0) ? true : false;

      
      $sql = "SELECT * FROM dislikes JOIN users WHERE dislikes.scribble = ? AND dislikes.user = users.id AND users.username = ?";

      $statement = $this->pdo->prepare($sql);
      if ( !$statement->execute(array($id, $username)) ) {
        return "Database update failed.";
      }

      $user_data['disliked'] = ($statement->rowCount() > 0) ? true : false;

      $this->user_data = $user_data;
    } catch (PDOException $e) {
        return "Database execute error.";
    }

    $statement = null;
    return "";
  }

  public function readScribbleAvatar($username) {
    $error = $this->readAvatarID($username);
    if (!!$error) {
      return "Error reading avatar ID. " . $error;
    }

    $error = $this->readScribble($this->avatarID);
    if (!!$error) {
      return "Error reading scribble avatar. " . $error;
    }

    return "";
  }

  // sets all user avatars with $id to the default avatar
  protected function setDefaultAvatars($id) {
    $sql = "UPDATE users SET avatar = 1 WHERE avatar = ?";

    $statement;
    try {
      $statement = $this->pdo->prepare($sql);

      if ( !$statement->execute(array($id)) ) {
        return "Database update failed.";
      }

      $this->numWipedAvatars = $statement->rowCount();
    } catch (PDOException $e) {
        return "Database execute error.";
    }

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
    if (!!$error) {
      return "Couldn't delete scribble. " . $error;
    }

    $error = $this->setDefaultAvatars($id);
    if (!!$error) {
      return "Couldn't update avatars. " . $error;
    }

    $statement = null;
    return "";
  }

  // returns an array of scribble ids and titles
  protected function getScribbleList() {
    $sql = "SELECT users.username, scribbles.id, scribbles.title, scribbles.data_url 
     FROM scribbles INNER JOIN users ON users.id = scribbles.user";

    $statement;
    try {
      $statement = $this->pdo->prepare($sql);

      if ( !$statement->execute() ) {
        return "Database lookup failed.";
      }

      if ($statement->rowCount() === 0) {
        $this->scribbleList = array();
        return ""; // let controller return an empty list
      }

      $list = array();
      while ($row = $statement->fetch()) {
        $row["title"] = htmlspecialchars_decode($row['title']);
        $list[$row["id"]] = $row;
      }

      $this->scribbleList = $list;
    } catch (PDOException $e) {
        return "Database execute error.";
    }

    $statement = null;
    return "";
  }

  // returns an array of scribble ids and titles
  protected function getScribbleListByUsername($username) {
    $sql = "SELECT users.username, scribbles.id, scribbles.title, scribbles.data_url 
     FROM scribbles INNER JOIN users ON users.id = scribbles.user WHERE users.username = ?";

    $statement;
    try {
      $statement = $this->pdo->prepare($sql);

      if ( !$statement->execute(array($username)) ) {
        return "Database lookup failed.";
      }

      if ($statement->rowCount() === 0) {
        $this->scribbleList = array();
        return ""; // let controller return an empty list
      }

      $list = array();
      while ($row = $statement->fetch()) {
        $row['title'] = htmlspecialchars_decode($row['title']);
        $list[] = $row;
      }

      $this->scribbleList = $list;
    } catch (PDOException $e) {
        return "Database execute error.";
    }

    $statement = null;
    return "";
  }

    // returns an array of scribble ids and titles
  protected function getScribbleSearchTitle($search) {
    $sch = '%' . htmlspecialchars($search) . '%';

    $sql = "SELECT users.username, scribbles.id, scribbles.title, scribbles.data_url 
     FROM scribbles INNER JOIN users ON users.id = scribbles.user WHERE scribbles.title LIKE ?";

    $statement;
    try {
      $statement = $this->pdo->prepare($sql);
      if ( !$statement->execute(array($sch)) ) {
        return "Database lookup failed.";
      }

      if ($statement->rowCount() === 0) {
        $this->scribbleList = array();
        return ""; // let controller return an empty list
      }

      $list = array();
      while ($row = $statement->fetch()) {
        $row['title'] = htmlspecialchars_decode($row['title']);
        $list[] = $row;
      }

      $this->scribbleList = $list;
    } catch (PDOException $e) {
        return "Database execute error.";
    }

    $statement = null;
    return "";
  }

  protected function removeLike($id, $username) {
    $sql = "DELETE l FROM likes l JOIN users u ON u.username = ? WHERE u.id = l.user AND l.scribble = ?";

    $statement;
    try {
      $statement = $this->pdo->prepare($sql);

      if ( !$statement->execute(array($username, $id)) ) {
        return "Database delete failed.";
      }

      // update scribble like count if a like was deleted
      if ($statement->rowCount() > 0) {
        $sql = "UPDATE scribbles SET likes = likes - 1 WHERE id = ?";
        $statement = $this->pdo->prepare($sql);

        if ( !$statement->execute(array($id)) ) {
          return "Database update failed.";
        }
      }
    } catch (PDOException $e) {
        return "Database execute error.";
    }

    $statement = null;
    return "";
  }

  protected function addLike($id, $username) {
    // create like
    // example of using a subquery
    $sql = "INSERT INTO likes (user, scribble) SELECT id, ? FROM users WHERE users.username = ? AND ? IN (SELECT id FROM scribbles WHERE scribbles.id = ?)";

    $statement;
    try {
      $statement = $this->pdo->prepare($sql);

      if ( !$statement->execute(array($id, $username, $id, $id)) ) {
        return "Database insert failed.";
      }

      // update scribble like count
      $sql = "UPDATE scribbles SET likes = likes + 1 WHERE id = ?";
      $statement = $this->pdo->prepare($sql);

      if ( !$statement->execute(array($id)) ) {
        return "Database update failed.";
      }

      // remove dislike, if any
      $sql = "DELETE dl FROM dislikes dl JOIN users u ON u.username = ? WHERE u.id = dl.user AND dl.scribble = ?";
      $statement = $this->pdo->prepare($sql);

      if ( !$statement->execute(array($username, $id)) ) {
        return "Database delete failed.";
      }

      // update scribble dislike count if a dislike was deleted
      if ($statement->rowCount() > 0) {
        $sql = "UPDATE scribbles SET dislikes = dislikes - 1 WHERE id = ?";
        $statement = $this->pdo->prepare($sql);

        if ( !$statement->execute(array($id)) ) {
          return "Database update failed.";
        }
      }
    } catch (PDOException $e) {
        return "Database execute error.";
    }

    $statement = null;
    return "";
  }

  protected function like($id, $username) {
    // check if the like already exists
    $sql = "SELECT * FROM likes JOIN users WHERE likes.scribble = ? AND users.username = ? AND likes.user = users.id";

    $statement;
    try {
      $statement = $this->pdo->prepare($sql);

      if ( !$statement->execute(array($id, $username)) ) {
        return "Database lookup failed.";
      }

      if ($statement->rowCount() === 0) {
        $statement = null;
        return $this->addLike($id, $username); // like does not exist yet
      }
      $statement = null;
      return $this->removeLike($id, $username); // like exists
    } catch (PDOException $e) {
        return "Database execute error.";
    }
  }

  protected function removeDislike($id, $username) {
    $sql = "DELETE dl FROM dislikes dl JOIN users u ON u.username = ? WHERE u.id = dl.user AND dl.scribble = ?";

    $statement;
    try {
      $statement = $this->pdo->prepare($sql);

      if ( !$statement->execute(array($username, $id)) ) {
        return "Database delete failed.";
      }

      // update scribble dislike count if a dislike was deleted
      if ($statement->rowCount() > 0) {
        $sql = "UPDATE scribbles SET dislikes = dislikes - 1 WHERE id = ?";
        $statement = $this->pdo->prepare($sql);

        if ( !$statement->execute(array($id)) ) {
          return "Database update failed.";
        }
      }
    } catch (PDOException $e) {
        return "Database execute error.";
    }

    $statement = null;
    return "";
  }

  protected function addDislike($id, $username) {
    // create dislike
    // example of using a subquery
    $sql = "INSERT INTO dislikes (user, scribble) SELECT id, ? FROM users WHERE users.username = ? AND ? IN (SELECT id FROM scribbles WHERE scribbles.id = ?)";

    $statement;
    try {
      $statement = $this->pdo->prepare($sql);

      if ( !$statement->execute(array($id, $username, $id, $id)) ) {
        return "Database insert failed.";
      }

      // update scribble dislike count
      $sql = "UPDATE scribbles SET dislikes = dislikes + 1 WHERE id = ?";
      $statement = $this->pdo->prepare($sql);

      if ( !$statement->execute(array($id)) ) {
        return "Database update failed.";
      }

      // remove like, if any
      $sql = "DELETE l FROM likes l JOIN users u ON u.username = ? WHERE u.id = l.user AND l.scribble = ?";
      $statement = $this->pdo->prepare($sql);

      if ( !$statement->execute(array($username, $id)) ) {
        return "Database delete failed.";
      }


      // update scribble like count if a like was deleted
      if ($statement->rowCount() > 0) {
        $sql = "UPDATE scribbles SET likes = likes - 1 WHERE id = ?";
        $statement = $this->pdo->prepare($sql);

        if ( !$statement->execute(array($id)) ) {
          return "Database update failed.";
        }
      }
    } catch (PDOException $e) {
        return "Database execute error.";
    }

    $statement = null;
    return "";
  }

  protected function dislike($id, $username) {
    // check if the dislike already exists
    $sql = "SELECT * FROM dislikes JOIN users WHERE dislikes.scribble = ? AND users.username = ? AND dislikes.user = users.id";

    $statement;
    try {
      $statement = $this->pdo->prepare($sql);

      if ( !$statement->execute(array($id, $username)) ) {
        return "Database lookup failed.";
      }

      if ($statement->rowCount() === 0) {
        $statement = null;
        return $this->addDislike($id, $username);
      }
      $statement = null;
      return $this->removeDislike($id, $username);
    } catch (PDOException $e) {
        return "Database execute error.";
    }
  }

  protected function importScribble($id) {
    $sql = "SELECT data_url FROM scribbles WHERE scribbles.id = ?";

    $statement;
    try {
      $statement = $this->pdo->prepare($sql);
      if ( !$statement->execute(array($id)) ) {
        return "Database lookup failed.";
      }

      if ($statement->rowCount() === 0) {
        return "Scribble does not exist.";
      }

      $row = $statement->fetch();
      $this->data_url = htmlspecialchars_decode($row['data_url']);
    } catch (PDOException $e) {
        return "Database execute error.";
    }

    $this->processForImport();

    $statement = null;
    return "";
  }

  private function calcGrey($r, $g, $b) {
    return ($r * 0.299) + ($g * 0.587) + ($b * 0.114);
  }

  private function calcAvg($r, $g, $b) {
    return (min($r, $g, $b) + max($r, $g, $b)) / 0.5;
  }

  // recolors (fades)
  private function processForImport() {
    require_once $_SERVER['DOCUMENT_ROOT'] . "/../include/common/util.php";

    try {
      $url = substr($this->data_url, 22); // strip out "data:image/png;base64,"
      str_replace(' ', '+', $url);
      $data = base64_decode($url);

      $rand = bin2hex(random_bytes(8));
      $tmpFilename = "/var/www/tmp/" . $rand . ".png";
      file_put_contents($tmpFilename, $data);

      
      $img = imagecreatefrompng($tmpFilename);

      //imagecolortransparent($img, imagecolorallocate($img, 0, 0, 0));
      imagealphablending($img, true);
      imagesavealpha($img, true);
      
      $converted = array();
      $ww = imagesx($img);
      $hh = imagesy($img);
      for ($xx = 0; $xx < $ww; $xx++) {
        //echo "x: {$xx}\n";
        for ($yy = 0; $yy < $hh; $yy++) {
          //echo "y: {$yy}\n";
          $rgba = imagecolorat($img, $xx, $yy);

          $a = ($rgba >> 24) & 0x7F;
          $r = ($rgba >> 16) & 0xFF;
          $g = ($rgba >> 8) & 0xFF;
          $b = $rgba & 0xFF;

          $setPalette = false;
          $newColor;
          if (isset($palette[$rgba])) {
            // already converted
            $newColor = $palette[$rgba];
          } else {
            // new color detected
            $setPalette = true;

            // convert to HSV
            // https://www.rapidtables.com/convert/color/rgb-to-hsv.html
            /////////////////////////////////////////////////////////////////////////////
            $max = max($r, $g, $b);
            $min = min($r, $g, $b);
            $chroma = $max - $min;

            $h = 0;
            $s = 0;
            $v = $max / 255.0;
            if ( abs($chroma) > 1e-6 ) {
              $s = $chroma / (float) $max;

              $inner;
              switch ($max) {
                case $r:
                  $inner = ( intval(($g - $b) / (float) $chroma) % 6);
                  break;
                case $g:
                  $inner = ((($b - $r) / (float) $chroma) + 2);
                  break;
                case $b:
                  $inner = ((($r - $g) / (float) $chroma) + 4);
                  break;
              }
              $h = 60 * $inner;
            }

            // edit in HSV
            /////////////////////////////////////////////////////////////////////////////
            $s *= 0.6; //slightly desaturate for import effect

            // convert back to rgb
            // https://www.rapidtables.com/convert/color/hsv-to-rgb.html
            /////////////////////////////////////////////////////////////////////////////
            $c = $v * $s;
            $x = $c * (1 - abs( (($h / 60) % 2) - 1));
            $m = $v - $c;
            $rp = 0;
            $gp = 0;
            $bp = 0;
            if ($h < 60) {
              $rp = $c;
              $gp = $x;
            } else if ($h < 120) {
              $rp = $x;
              $gp = $c;
            } else if ($h < 180) {
              $gp = $c;
              $bp = $x;
            } else if ($h < 240) {
              $gp = $x;
              $bp = $c;
            } else if ($h < 300) {
              $rp = $x;
              $bp = $c;
            } else { //if ($h < 360) {
              $rp = $c;
              $bp = $x;
            }
            $r = intval(($rp + $m) * 255);
            $b = intval(($gp + $m) * 255);
            $g = intval(($bp + $m) * 255);

            $newColor = imagecolorallocatealpha($img, $r, $g, $b, $a);
          }

          if ($setPalette) {
            $palette[$rgba] = $newColor;
          }
          //$newColor = imagecolorallocatealpha($img, 0, 255, 255, $a);
          imagesetpixel($img, $xx, $yy, $newColor);
        }
      }

      imagepng($img, "/var/www/tmp/" . $rand . "_mod.png");

      $encoded = base64_encode(file_get_contents("/var/www/tmp/" . $rand . "_mod.png"));
      $this->data_url = "data:image/png;base64," . $encoded;
    } catch (Exception $e) {
      return "Error converting image for import. " . $e;
    }
  }

  protected function comment($id, $username, $msg) {
    //TODO
    return "Not implemented";
  }
}
