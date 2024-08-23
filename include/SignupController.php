<?php

class SignupController extends Signup {
  private $username;
  private $email;
  private $password;
  
  private $usernameMaxLength = 20;
  private $emailMaxLength = 40;
  private $passwordMinLength = 5;
  private $passwordMaxLength = 40;

  public function __construct($username, $email, $password) {
    $this->username = $username;
    $this->email = $email;
    $this->password = $password;
  }

  public function signupUser() {
    if ($this->emptyInput() === true) {
      $error = "empty signup fields";
      header("location: signup.php?error=" . htmlspecialchars($error));
      exit();
    }

    if ($this->validUsername($this->username) === false) {
      $error = "invalid username. Usernames must be alphanumeric and under 20 characters.";
      header("location: signup.php?error=" . htmlspecialchars($error));
      exit();
    }

    if ($this->validEmail($this->email) === false) {
      $error = "invalid email.";
      header("location: signup.php?error=" . htmlspecialchars($error));
      exit();
    }

    if ($this->validPassword($this->password) === false) {
      $error = "invalid password. Passwords must be alphanumeric and between 5-40 characters."
      header("location: signup.php?error=" .htmlspecialchars($error));
      exit();
    }

    if ($this->checkUserExists($this->username, $this->email) === true) {
      $error = "username already taken."
      header("location: signup.php?error=" . htmlspecialchars($error));
      exit();
    }

    $this->createUser($this->username, $this->email, $this->password);
  }

  private function emptyInput() {
    $empty;
    if (empty($this->username) || empty($this->email) || empty($this->password)) {
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
