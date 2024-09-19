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

  public function getDataURL() {
    return $this->data_url;
  }

  public function getNumWipedAvatars() {
    return $this->numWipedAvatars;
  }

  protected function createScribble($username, $title, $data_url) {
    $statement;
    try {
      $sql = "INSERT INTO scribbles (user, title, data_url) SELECT id, ?, ? FROM users WHERE username = ?";
      $statement = $this->pdo->prepare($sql);

      if ( !$statement->execute(array(htmlspecialchars($title), htmlspecialchars($data_url), $username)) ) {
        return "Database insert failed.";
      }

      $this->id = $this->pdo->lastInsertId();
      $this->data_url = $data_url;
      $this->title = $title;
      $this->likes    = 0;
      $this->dislikes = 0;
      $this->user_data = array(
        "liked" => false,
        "disliked" => false
      );
    } catch (PDOException $e) {
        return "Database execute error.";
    } finally {
      $statement = null;
    }

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
    } finally {
      $statement = null;
    }

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
    } finally {
      $statement = null;
    }

    return "";
  }

  public function getScribble() {
    return array(
      'id'       => $this->id,
      'username' => $this->username,
      'title'    => $this->title,
      'data_url' => $this->data_url,
      'likes'    => $this->likes,
      'dislikes' => $this->dislikes,
      'user_data' => $this->user_data
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

  public function readMetadata($id, $username, $readScribble = true) {
    if ($readScribble) {
      $err = $this->readScribble($id);
      if (!!$err) {
        return "Error reading scribble. " . $err;
      }
    }

    $user_data = array();

    $statement;
    try {
      $sql = "SELECT * FROM likes JOIN users WHERE likes.scribble = ? AND likes.user = users.id AND users.username = ?";
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
    } finally {
      $statement = null;
    }

    return "";
  }

  public function readScribbleAvatar($username) {
    $statement;
    try {
      $sql = <<<EOF
      SELECT users.username, scribbles.id, scribbles.likes, scribbles.dislikes,
             scribbles.title, scribbles.data_url
      FROM scribbles INNER JOIN users ON users.avatar = scribbles.id
      WHERE users.username = ?
EOF;
      $statement = $this->pdo->prepare($sql);
      if ( !$statement->execute(array($username)) ) {
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
    } finally {
      $statement = null;
    }

    return "";
  }

  // sets all user avatars with $id to the default avatar
  protected function setDefaultAvatars($id) {
    $statement;
    try {
      $sql = "UPDATE users SET avatar = 1 WHERE avatar = ?";
      $statement = $this->pdo->prepare($sql);

      if ( !$statement->execute(array($id)) ) {
        return "Database update failed.";
      }

      $this->numWipedAvatars = $statement->rowCount();
    } catch (PDOException $e) {
        return "Database execute error.";
    } finally {
      $statement = null;
    }

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

    return "";
  }

  protected function getScribbleList($searchingUsername) {
    $statement;
    try {
      $sql = "SELECT users.username, scribbles.id, scribbles.likes, scribbles.dislikes,
                     scribbles.title, scribbles.data_url
              FROM scribbles INNER JOIN users ON users.id = scribbles.user";
      $statement = $this->pdo->prepare($sql);

      if ( !$statement->execute(array()) ) {
        return "Database lookup failed.";
      }

      if ($statement->rowCount() === 0) {
        $this->scribbleList = array();
        return ""; // let controller return an empty list
      }

      $list = array();
      $total = 1;
      while ($row = $statement->fetch()) {
        $row["title"] = htmlspecialchars_decode($row["title"]);

        $msg = $this->readMetadata($row["id"], $searchingUsername, false);
        if (!!$msg) {
          return "Error reading metadata. " . $msg;
        }

        $row["user_data"] = $this->user_data;

        $list[$row["id"]] = $row;
        if ($total++ >= 30) {
          break;
        }
      }

      $this->scribbleList = $list;
    } catch (PDOException $e) {
        return "Database execute error.";
    } finally {
      $statement = null;
    }

    return "";
  }

  protected function getScribbleListByUsername($username, $searchingUsername) {
    $statement;
    try {
      $sql = "SELECT users.username, scribbles.id, scribbles.likes, scribbles.dislikes,
                     scribbles.title, scribbles.data_url
              FROM scribbles INNER JOIN users ON users.id = scribbles.user WHERE users.username = ?";
      $statement = $this->pdo->prepare($sql);

      if ( !$statement->execute(array($username)) ) {
        return "Database lookup failed.";
      }

      if ($statement->rowCount() === 0) {
        $this->scribbleList = array();
        return ""; // let controller return an empty list
      }

      $list = array();
      $total = 1;
      while ($row = $statement->fetch()) {
        $row["title"] = htmlspecialchars_decode($row["title"]);

        $msg = $this->readMetadata($row["id"], $searchingUsername, false);
        if (!!$msg) {
          return "Error reading metadata. " . $msg;
        }

        $row["user_data"] = $this->user_data;
        $list[$row["id"]] = $row;
        if ($total++ >= 30) {
          break;
        }
      }

      $this->scribbleList = $list;
    } catch (PDOException $e) {
        return "Database execute error.";
    } finally {
      $statement = null;
    }

    return "";
  }

  protected function getScribbleSearchTitle($search, $searchingUsername) {
    $statement;
    try {
      $sch = '%' . htmlspecialchars($search) . '%';
      $sql = "SELECT users.username, scribbles.id, scribbles.likes, scribbles.dislikes,
                     scribbles.title, scribbles.data_url
              FROM scribbles INNER JOIN users ON users.id = scribbles.user WHERE scribbles.title LIKE ?";
      $statement = $this->pdo->prepare($sql);
      if ( !$statement->execute(array($sch)) ) {
        return "Database lookup failed.";
      }

      if ($statement->rowCount() === 0) {
        $this->scribbleList = array();
        return ""; // let controller return an empty list
      }

      $list = array();
      $total = 1;
      while ($row = $statement->fetch()) {
        $row["title"] = htmlspecialchars_decode($row["title"]);

        $msg = $this->readMetadata($row["id"], $searchingUsername, false);
        if (!!$msg) {
          return "Error reading metadata. " . $msg;
        }

        $row["user_data"] = $this->user_data;
        $list[$row["id"]] = $row;
        if ($total++ >= 30) {
          break;
        }
      }

      $this->scribbleList = $list;
    } catch (PDOException $e) {
        return "Database execute error.";
    } finally {
      $statement = null;
    }

    return "";
  }

  protected function removeLike($id, $username) {
    $statement;
    try {
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
    } finally {
      $statement = null;
    }

    return "";
  }

  protected function addLike($id, $username) {
    $statement;
    try {
      // create like
      $sql = "INSERT INTO likes (user, scribble)
              SELECT id, ? FROM users
              WHERE users.username = ? AND ? IN (SELECT id FROM scribbles WHERE scribbles.id = ?)";
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
      $sql = "DELETE dl FROM dislikes dl JOIN users u ON u.username = ? 
              WHERE u.id = dl.user AND dl.scribble = ?";
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
    } finally {
      $statement = null;
    }

    return "";
  }

  protected function like($id, $username) {
    $statement;
    try {
      // check if the like already exists
      $sql = "SELECT * FROM likes JOIN users
              WHERE likes.scribble = ? AND users.username = ? AND likes.user = users.id";
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
    } finally {
      $statement = null;
    }
  }

  protected function removeDislike($id, $username) {
    $statement;
    try {
      $sql = "DELETE dl FROM dislikes dl JOIN users u ON u.username = ?
              WHERE u.id = dl.user AND dl.scribble = ?";
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
    } finally {
      $statement = null;
    }

    return "";
  }

  protected function addDislike($id, $username) {
    $statement;
    try {
      // create dislike
      $sql = "INSERT INTO dislikes (user, scribble)
              SELECT id, ? FROM users 
              WHERE users.username = ? AND ? IN (SELECT id FROM scribbles WHERE scribbles.id = ?)";
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
      $sql = "DELETE l FROM likes l JOIN users u ON u.username = ?
              WHERE u.id = l.user AND l.scribble = ?";
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
    } finally {
      $statement = null;
    }

    return "";
  }

  protected function dislike($id, $username) {
    $statement;
    try {
      // check if the dislike already exists
      $sql = "SELECT * FROM dislikes JOIN users 
              WHERE dislikes.scribble = ? AND users.username = ? AND dislikes.user = users.id";
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
    } finally {
      $statement = null;
    }
  }

  protected function importScribble($id) {
    $statement;
    try {
      $sql = "SELECT data_url FROM scribbles WHERE scribbles.id = ?";
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
    } finally {
      $statement = null;
    }

    $this->processForImport();
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

      imagepng($img, $tmpFilename);

      $encoded = base64_encode(file_get_contents($tmpFilename));
      $this->data_url = "data:image/png;base64," . $encoded;
      unlink($tmpFilename);
    } catch (Exception $e) {
      return "Error converting image for import. " . $e;
    }
  }

  protected function comment($id, $username, $msg) {
    //TODO
    return "Not implemented";
  }
}
