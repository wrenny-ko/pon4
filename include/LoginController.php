<?php

class LoginController extends Login {
  private $username;
  private $password;

  private $usernameMaxLength = 20;
  private $passwordMinLength = 5;
  private $passwordMaxLength = 40;

  public function __construct($username, $password) {
    $this->username = $username;
    $this->password = $password;
  }

  public function loginUser() {
    if ($this->emptyInput() === true) {
      $error = "empty login fields";
      header("location: login.php?error=" . htmlspecialchars($error));
      exit();
    }

    if ($this->validUsername($this->username) === false) {
      $error = "invalid username. Usernames are alphanumeric and under 20 characters.";
      header("location: login.php?error=" . htmlspecialchars($error));
      exit();
    }

    if ($this->validPassword($this->password) === false) {
      $error = "invalid password. Passwords are alphanumeric and between 5-40 characters.";
      header("location: login.php?error=" . htmlspecialchars($error));
      exit();
    }

    if ($this->checkUserExists($this->username) === false) {
      $error = "username not found";
      header("location: signup.php?error=" . htmlspecialchars($error));
      exit();
    }

    if ($this->comparePassword($this->username, $this->password) === true) {
      session_start();
      $_SESSION['username'] = $this->username;
    }
  }

  private function emptyInput() {
    $empty;
    if (empty($this->username) || empty($this->password)) {
      $empty = true;
    } else {
      $empty = false;
    }
    return $empty;
  }

  // checks if format of user-input username is valid
  private function validUsername($username) {
    $valid = true;

    // enforce at least one char, alphanumeric
    if (preg_match("/^[a-zA-Z0-9]+$/", $username) !== 1) {
      $valid = false;
    }

    if (strlen($username) > $this->usernameMaxLength) {
      $valid = false;
    }

    return $valid;
  }

  // checks if format of user-input email is valid
  private function validEmail($email) {
    $valid = true;
    if (filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
      $valid = false;
    }

    if (strlen($email) > $this->emailMaxLength) {
      $valid = false;
    }

    return $valid;
  }

  // checks if format of user-input password is valid
  // currently allows alphanumeric passwords only
  private function validPassword($password) {
    $valid = true;

    // enforce at least one char, alphanumeric
    if (preg_match("/^[a-zA-Z0-9]+$/", $password) !== 1) {
      $valid = false;
    }

    if (strlen($password) < $this->passwordMinLength) {
      $valid = false;
    }

    if (strlen($password) > $this->passwordMaxLength) {
      $valid = false;
    }

    return $valid;
  }
}
