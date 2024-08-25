<?php
class LoginController extends User {
  private $username;
  private $password;

  private $usernameMaxLength = 20;
  private $passwordMinLength = 5;
  private $passwordMaxLength = 40;

  public function __construct($username, $password) {
    $this->username = $username;
    $this->password = $password;
  }

  private function error($msg) {
    header("location: login.php?error=" . htmlspecialchars($msg));
    exit();
  }

  public function loginUser() {
    $error = $this->checkEmptyInput();
    if (!empty($error)) {
      $error = "empty login fields. " . $error;
      $this->error($error);
    }

    $error = $this->validateUsername($this->username);
    if (!empty($error)) {
      $error = "invalid username. " . $error;
      $this->error($error);
    }

    $error = $this->validatePassword($this->password);
    if (!empty($error)) {
      $error = "invalid password. " . $error;
      $this->error($error);
    }

    $error = $this->checkUserExists($this->username);
    if (!empty($error)) {
      $error = "Username check failed. " . $error;
      $this->error($error);
    }

    $error = $this->comparePassword($this->username, $this->password);
    if (!empty($error)) {
      $error = "Password check failed. " . $error;
      $this->error($error);
    }

    session_start();
    $_SESSION['username'] = $this->username;
  }

  private function checkEmptyInput() {
    if (empty($this->username)) {
      return "Username required";
    } else if (empty($this->password)) {
      return "Password required.";
    }
    return "";
  }

  // checks if format of user-input username is valid
  private function validateUsername($username) {
    // enforce at least one char, alphanumeric
    if (preg_match("/^[a-zA-Z0-9]+$/", $username) !== 1) {
      return "Requires only alphanumeric.";
    }

    if (strlen($username) > $this->usernameMaxLength) {
      return "Too long; must be under " . 
      $this->usernameMaxLength . " characters.";
    }

    return "";
  }

  // checks if format of user-input password is valid
  // currently allows alphanumeric passwords only
  private function validatePassword($password) {
    // enforce at least one char, alphanumeric
    if (preg_match("/^[a-zA-Z0-9]+$/", $password) !== 1) {
      return "Requires only alphanumeric.";
    }

    if (strlen($password) < $this->passwordMinLength) {
      return "Too short; must be between " . 
      $this->passwordMinLength . "-" .
      $this->passwordMaxLength . " characters.";
    }

    if (strlen($password) > $this->passwordMaxLength) {
      return "Too long; must be between " . 
      $this->passwordMinLength . "-" .
      $this->passwordMaxLength . " characters.";
    }

    return "";
  }
}
