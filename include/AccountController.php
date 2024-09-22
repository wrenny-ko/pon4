<?php

enum AccountAction: string {
  case Login = "login";
  case Logout = "logout";
  case Signup = "signup";
  case SetAvatar = "set_avatar";
}

class AccountController extends User {
  const RouteMap = array(
    AccountAction::Login->value    => array(
      "method" => RequestMethod::POST,
      "login_required" => false,
      "auth_levels" => array()
    ),
    AccountAction::Logout->value    => array(
      "method" => RequestMethod::POST,
      "login_required" => true,
      "auth_levels" => array()
    ),
    AccountAction::Signup->value => array(
      "method" => RequestMethod::POST,
      "login_required" => false,
      "auth_levels" => array()
    ),
    AccountAction::SetAvatar->value => array(
      "method" => RequestMethod::PUT,
      "login_required" => true,
      "auth_levels" => array()
    )
  );

  private $rest;
  private $action;

  public function error($msg) {
    $this->rest->setPDO($this->pdo);
    $this->rest->error($msg);
    $this->rest->setPDO(null);
  }

  public function success() {
    $this->rest->setPDO($this->pdo);
    $this->rest->success($this->action->value);
    $this->rest->setPDO(null);
  }

  public function run() {
    $msg = $this->init();
    if (!!$msg) {
      return $msg;
    }

    $msg = $this->handle();
    if (!!$msg) {
      return $msg;
    }

    return "";
  }

  public function init() {
    // set up first for logging/error response if needed
    $this->rest = new Rest();
    $this->rest->setupLogging("api.log", "account");

    if (!isset($_SERVER["REQUEST_METHOD"])) {
      return "request method not set";
    }

    $method;
    try {
      $method = RequestMethod::from($_SERVER["REQUEST_METHOD"]);
    } catch (\Throwable $e) {
      return "request method not supported";
    }
    $this->rest->setMethod($method);
    $this->rest->updateLogRequestMethod();

    if (!isset($_GET["action"])) {
      return "requires an 'action' query string";
    }

    $action;
    $action = AccountAction::tryFrom($_GET["action"]);
    if (!$action) {
      return "action not supported";
    }
    $this->action = $action;

    $this->rest->setLoginRequired( self::RouteMap[$action->value]["login_required"] );
    $this->rest->setAuths( self::RouteMap[$action->value]["auth_levels"] );

    $msg = $this->rest->compareMethod(self::RouteMap[$action->value]["method"] );
    if (!!$msg) {
      return $msg;
    }

    $msg = $this->rest->auth();
    if (!!$msg) {
      return $msg;
    }
  }

  public function __destruct() {
    $this->rest = null;
    $this->pdo = null;
  }

  public function handle() {
    return $this->handleAction();
  }

  private function handleAction() {
    switch ($this->action) {
      case AccountAction::Login:
        return $this->login();
        break;
      case AccountAction::Logout:
        return $this->logout();
        break;
      case AccountAction::Signup:
        return $this->signup();
        break;
      case AccountAction::SetAvatar:
        $id = $this->rest->getRequiredQueryField("id");
        $username = $this->rest->getUsername();
        return $this->setAvatar($id, $username);
        break;
    }
    return "action not found";
  }

  private function setSessionPerms() {
    $perms = new Perms($this->username);
    $perms->setPDO($this->pdo);

    $msg = $perms->readPerms();
    $perms->setPDO(null);
    if (!!$msg) {
      return "Error reading user perms.";
    }

    $_SESSION["perms"] = $perms;
    return "";
  }

  private function login() {
    if (!isset($_POST["username"])) {
      return "Requires a username.";
    }

    if (!isset($_POST["password"])) {
      return "Requires a password.";
    }

    $this->username = $_POST["username"];
    $this->password = $_POST["password"];

    $err = $this->loginUser();
    if (!!$err) {
      return "Error logging in user. " . $err;
    }

    $_SESSION["username"] = $_POST["username"];

    $err = $this->setSessionPerms();
    if (!!$err) {
      return "Error setting user session perms. " . $err;
    }

    return "";
  }

  private function logout() {
    //unset $_SESSION['username'] and $_SESSION['perms']
    session_unset();
    session_destroy();
  }

  private function signup() {
    if (!isset($_POST["username"])) {
      return "Requires a username.";
    }

    if (!isset($_POST["email"])) {
      return "Requires an email.";
    }

    if (!isset($_POST["password"])) {
      return "Requires a password.";
    }

    $this->username = $_POST["username"];
    $this->email = $_POST["email"];
    $this->password = $_POST["password"];

    $err = $this->signupUser();
    if (!!$err) {
      return "Error signing up user. " . $err;
    }

    $_SESSION['username'] = $_POST["username"];

    $err = $this->setSessionPerms();
    if (!!$err) {
      return "Error setting user session perms. " . $err;
    }

    return "";
  }

  private function setAvatar($id, $username) {
    $err = $this->updateAvatar($id, $username);
    if (!!$err) {
      return "Error setting avatar. " . $err;
    }

    $scrib = new Scribble();
    $scrib->setPDO($this->pdo);

    $err = $scrib->readScribble($id);
    $scrib->setPDO(null);
    if (!!$err) {
      return "Error reading scribble. " . $err;
    }

    $this->rest->setResponseField('scribble', $scrib->getScribble());

    $scrib = null;
    return "";
  }
}
