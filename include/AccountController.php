<?php

enum AccountAction: string {
  case Login = "login";
  case Logout = "logout";
  case Signup = "signup";
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
    )
  );

  private $rest;
  private $action;

  public function __construct() {
    // set up first for logging/error response if needed
    $this->rest = new Rest();
    $this->rest->setupLogging("api.log", "account");

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
    $action = AccountAction::tryFrom($_GET["action"]);
    if (!$action) {
      $this->rest->error("action not supported");
    }
    $this->action = $action;

    $this->rest->setLoginRequired( self::RouteMap[$action->value]["login_required"] );
    $this->rest->setAuths( self::RouteMap[$action->value]["auth_levels"] );

    $this->rest->compareMethod(self::RouteMap[$action->value]["method"] );
    $this->rest->auth();
  }

  public function handle() {
    $msg = $this->handleAction();
    if (!empty($msg)) {
      $this->rest->error($msg);
    }
    $this->rest->success($this->action->value);
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
    }
    return "action not found";
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
    if (!empty($err)) {
      return "Error logging in user. " . $err;
    }

    return "";
  }

  private function logout() {
    session_unset(); // unset $_SESSION['username']
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
    if (!empty($err)) {
      return "Error signing up user. " . $err;
    }

    return "";
  }
}
