<?php

enum ScribbleAction: string {
  case Like = "like";
  case Dislike = "dislike";
  case Delete = "delete";
  case Comment = "comment";
  case Get = "get";
  case GetAvatar = "get_avatar";
  case GetAvatars = "get_avatars";
  case Upload = "upload";
  case Search = "search";
  case UserGif = "user_gif";
  case GetMetadata = "get_metadata";
}

class ScribbleController extends Scribble {
  const RouteMap = array(
    ScribbleAction::Like->value    => array(
      "method" => RequestMethod::PUT,
      "login_required" => true,
      "auth_levels" => array()
    ),
    ScribbleAction::Dislike->value => array(
      "method" => RequestMethod::PUT,
      "login_required" => true,
      "auth_levels" => array()
    ),
    ScribbleAction::Delete->value  => array(
      "method" => RequestMethod::DELETE,
      "login_required" => true,
      "auth_levels" => array(AuthLevel::Moderator, AuthLevel::Admin)
    ),
    ScribbleAction::Comment->value => array(
      "method" => RequestMethod::POST,
      "login_required" => false,
      "auth_levels" => array()
    ),
    ScribbleAction::Get->value => array(
      "method" => RequestMethod::GET,
      "login_required" => false,
      "auth_levels" => array()
    ),
    ScribbleAction::GetAvatar->value => array(
      "method" => RequestMethod::GET,
      "login_required" => false,
      "auth_levels" => array()
    ),
    ScribbleAction::GetAvatars->value => array(
      "method" => RequestMethod::GET,
      "login_required" => false,
      "auth_levels" => array()
    ),
    ScribbleAction::Upload->value => array(
      "method" => RequestMethod::POST,
      "login_required" => false,
      "auth_levels" => array()
    ),
    ScribbleAction::Search->value => array(
      "method" => RequestMethod::GET,
      "login_required" => false,
      "auth_levels" => array()
    ),
    ScribbleAction::UserGif->value => array(
      "method" => RequestMethod::GET,
      "login_required" => false,
      "auth_levels" => array(AuthLevel::Beta)
    ),
    ScribbleAction::GetMetadata->value => array(
      "method" => RequestMethod::GET,
      "login_required" => false,
      "auth_levels" => array()
    )
  );

  private $rest;
  private $action;

  public function __construct() {
    // set up first for logging/error response if needed
    $this->rest = new Rest();
    $this->rest->setupLogging("api.log", "scribble");

    if (!isset($_SERVER["REQUEST_METHOD"])) {
      $this->rest->error("request method not set");
    }

    $method;
    try {
      $method = RequestMethod::from($_SERVER["REQUEST_METHOD"]);
    } catch (\Throwable $e) {
      $this->rest->error("request method not supported");
    }
    $this->rest->setMethod($method);

    if (!isset($_GET["action"])) {
      $this->rest->error("requires an 'action' query string");
    }

    $action;
    try {
      $action = ScribbleAction::from($_GET["action"]);
    } catch (\Throwable $e) {
      $this->rest->error("action not supported");
    }
    $this->action = $action;

    $this->rest->setLoginRequired( self::RouteMap[$action->value]["login_required"] );
    $this->rest->setAuths( self::RouteMap[$action->value]["auth_levels"] );

    $this->rest->compareMethod(self::RouteMap[$action->value]["method"] );
    $this->rest->auth();

    $err = $this->connect();
    if (!!$err) {
      return "Database connect error. " . $err;
    }
  }

  public function handle() {
    $msg = $this->handleAction();
    if (!!$msg) {
      $this->rest->error($msg);
    }
    $this->rest->success($this->action->value);
  }

  private function handleAction() {
    switch ($this->action) {
      case ScribbleAction::Like:
        $id = $this->rest->getRequiredQueryField("id");
        $username = $this->rest->getUsername();
        return $this->like($id, $username);
        break;
      case ScribbleAction::Dislike:
        $id = $this->rest->getRequiredQueryField("id");
        $username = $this->rest->getUsername();
        return $this->dislike($id, $username);
        break;
      case ScribbleAction::Delete:
        $id = $this->rest->getRequiredQueryField("id");
        return $this->deleteScribbleUpdateAvatars($id);
        break;
      case ScribbleAction::Comment:
        return "not implemented";
        break;
      case ScribbleAction::Get:
        $id = $this->rest->getRequiredQueryField("id");
        return $this->get($id);
        break;
      case ScribbleAction::GetAvatar:
        $username = $this->rest->getQueryField("username");
        if(!$username) {
          $username = $this->rest->getUsername();
        }
        return $this->getAvatar($username);
        break;
      case ScribbleAction::GetAvatars:
        return $this->getAvatars();
        break;
      case ScribbleAction::Upload:
        $this->rest->setSuccessCode(201);
        $username = $this->rest->getUsername();
        return $this->upload($username);
        break;
      case ScribbleAction::Search:
        $search = $this->rest->getQueryField("search");
        return $this->search($search);
        break;
      case ScribbleAction::UserGif:
        return $this->userGif();
        break;
      case ScribbleAction::GetMetadata:
        $id = $this->rest->getRequiredQueryField("id");
        $username = $this->rest->getUsername();
        return $this->getMeta($id, $username);
        break;
    }
    return "action not found";
  }

  private function upload($username) {
    // Checks
    /////////////////////////////////////////////////////////////////////
    // check for behavior when a form was too large to accept
    if (!$_POST) {
      $length = $_SERVER['CONTENT_LENGTH'];

      $umf = return_bytes(ini_get('upload_max_filesize'));
      $pms = return_bytes(ini_get('post_max_size'));
      $less = ($umf > $pms) ? $pms : $umf;
      if ($length >= $less) {
        return "Exceeded upload byte size limit of " . format_bytes($less);
      }
    }

    if (!$_POST['scribble']) {
      return "Required json not present";
    }

    $json = $_POST['scribble'];
    if(!json_validate($json)) {
      return "Invalid json";
    }

    $scribble = json_decode($json);
    if (!isset($scribble->data_url) || !isset($scribble->title)) {
      return "Missing json fields";
    }

    if (!is_string($scribble->data_url)) {
      return "Improperly formatted json fields.";
    }

    if (strlen($scribble->title) > 30) {
      return "Titles have max character limits of 30.";
    }

    $error = $this->createScribble($username, $scribble->title, $scribble->data_url);
    if (!!$error) {
      return "Creation failed. " . $error;
    }

    if (!isset($this->id)) {
      return "Can't find scribble id.";
    }

    $this->rest->setDataField("id", $this->id);
    return "";
  }

  private function getAvatar($username) {
    $error = $this->readScribbleAvatar($username);
    if (!!$error) {
      return "Can't read scribble avatar. " . $error;
    }

    $this->rest->setDataField("scribble", $this->getScribble());
    return "";
  }

  private function getAvatars() {
    $usernames = $this->rest->getRequiredQueryField("usernames");
    $usernames = explode(',', $usernames);

    $avatars = array();
    foreach ($usernames as $name) {
      $error = $this->readScribbleAvatar($name);
      if (!!$error) {
        return "Can't read scribble avatar. " . $error;
      }

      $avatars[$name] = $this->getScribble();
    }

    $this->rest->setResponseField("avatars", $avatars);
    return "";
  }

  private function get($id) {
    $msg = $this->readScribble($id);
    if (!!$msg) {
      return "Error reading scribble. " . $msg;
    }

    $this->rest->setResponseField("scribble", $this->getScribble());
  }

  private function getMeta($id, $username) {
    $msg = $this->readMetadata($id, $username);
    if (!!$msg) {
      return "Error reading metadata. " . $msg;
    }

    $this->rest->setResponseField("metadata", $this->getMetadata());
  }

  // search titles by a query string
  private function search($search) {
    if (!$search) {
      // search all scribbles
      $error = $this->getScribbleList();
      if (!!$error) {
        return "Error searching scribbles. " . $error;
      }

      $this->rest->setResponseField("scribbles", $this->scribbleList);
    } else {
      //check for "user:<username>" for separate search
      $query = htmlspecialchars_decode($_GET["search"]);
      $matches = array([]);
      $result = preg_match('/(by):(?P<username>[[:alnum:]]+)/i', $query, $matches);

      if ($result === 1) {
        $error = $this->getScribbleListByUsername($matches['username']);
        if (!!$error) {
          return "Error searching scribbles. " . $error;
        }
      } else {
        // default to search titles
        $error = $this->getScribbleSearchTitle($_GET["search"]);
        if (!!$error) {
          return "Error searching scribbles. " . $error;
        }
      }

      $this->rest->setResponseField("scribbles", $this->scribbleList);
    }
    return "";
  }

  // beta test of making a slideshow out of all a user's scribbles
  private function userGif() {
    return "not implemented";

    $this->rest->setDataField("url", $url);
    return "";
  }
}
