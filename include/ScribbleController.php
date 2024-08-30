<?php
require_once "../include/Perms.php";

enum ScribbleAction: string {
  case Like = "like";
  case Dislike = "dislike";
  case Delete = "delete";
  case Comment = "comment";
}

class ScribbleController extends Scribble {
  const AuthMap = array(
    ScribbleAction::Delete->value => array(AuthLevel::Mod, AuthLevel::Admin)
  );

  public function error($msg) {
    $msg = "Scribble error. " . $msg;
    echo json_encode(array("error" => $msg));
    http_response_code(400);
    exit();
  }

  public function handlePost() {
    if ($_SERVER["REQUEST_METHOD"] !== 'POST') {
      $this->error("Only accept POST on this route");
    }

    // Checks
    /////////////////////////////////////////////////////////////////////
    // check for behavior when a form was too large to accept
    if (empty($_POST)) {
      $length = $_SERVER['CONTENT_LENGTH'];

      $umf = return_bytes(ini_get('upload_max_filesize'));
      $pms = return_bytes(ini_get('post_max_size'));
      $less = ($umf > $pms) ? $pms : $umf;
      if ($length >= $less) {
        $this->error("Exceeded upload byte size limit of " . format_bytes($less));
      }
    }

    if (empty($_POST['scribble'])) {
      $this->error("Required json not present");
    }

    session_start();

    $username = "anonymous";
    if (isset($_SESSION['username'])) {
      $username = $_SESSION['username'];
    }

    $json = $_POST['scribble'];
    if(!json_validate($json)) {
      $this->error("Invalid json");
    }

    $scribble = json_decode($json);
    if (!isset($scribble->data_url) || !isset($scribble->title)) {
      $this->error("Missing json fields");
    }

    if (!is_string($scribble->data_url)) {
      $this->error("Improperly formatted json fields.");
    }

    if (strlen($scribble->title) >= 30) {
      $this->error("Titles have max character limits of 30.");
    }

    $error = $this->createScribble($username, $scribble->title, $scribble->data_url);
    if (!empty($error)) {
      $this->error("Creation failed. " . $error);
    }

    if (!isset($this->id)) {
      $this->error("Can't find scribble id.");
    }

    http_response_code(201);
    echo json_encode(array("success" => $this->id));
  }

  public function handleAvatarGet($username) {
    if ($_SERVER["REQUEST_METHOD"] !== 'GET') {
      $this->error("Only accept GET on this route.");
    }

    $error = $this->readScribbleAvatar($username);
    if (!empty($error)) {
      $this->error("Can't read scribble avatar. " . $error);
    }

    echo json_encode(array("success" => $this->data_url));
  }

  public function handleScribbleGet() {
    if ($_SERVER["REQUEST_METHOD"] !== 'GET') {
      $this->error("Only accept GET on this route.");
    }

    if (isset($_GET["id"])) {
      // search a single scribble id

      $error = $this->readScribble($_GET["id"]);
      if (!empty($error)) {
        $this->error("Error reading scribble. " . $error);
      }

      echo json_encode( array("scribble" => $this->getScribble()) );

    } else if(isset($_GET["search"])) {
      // search titles by a query string

      //"user:<username>" for separate search
      $query = htmlspecialchars_decode($_GET["search"]);
      $matches = array([]);
      $result = preg_match('/(by):(?P<username>[[:alnum:]]+)/i', $query, $matches);
      if ($result === 1) {
        $error = $this->getScribbleListByUsername($matches['username']);
        if (!empty($error)) {
          $this->error("Error searching scribbles. " . $error);
        }
      } else {
        // default to search titles
        $error = $this->getScribbleSearchTitle($_GET["search"]);
        if (!empty($error)) {
          $this->error("Error searching scribbles. " . $error);
        }
      }

      echo json_encode( array("scribbles" => $this->scribbleList) );

    } else {
      // search for all scribbles

      $error = $this->getScribbleList();
      if (!empty($error)) {
        $this->error("Error searching scribbles. " . $error);
      }

      echo json_encode( array("scribbles" => $this->scribbleList) );

    }
  }

  public function handleAction($action, $id, $username, $data = null) {
    switch ($action) {
      case ScribbleAction::Like:
        return $this->like($id, $username);
        break;
      case ScribbleAction::Dislike:
        return $this->dislike($id, $username);
        break;
      case ScribbleAction::Delete:
        return $this->delete($id);
        break;
      case ScribbleAction::Comment:
        return $this->comment($id, $data);
        break;
    }
    return "Action not found";
  }
}
