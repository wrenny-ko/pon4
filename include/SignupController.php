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

  private function signupError($msg) {
    header("location: signup.php?error=" . htmlspecialchars($msg));
    exit();
  }

  public function signupUser() {
    $error = $this->checkEmptyInput();
    if (!empty($error)) {
      $error = "empty input. " . $error;
      $this->signupError($error);
    }

    $error = $this->validateUsername($this->username);
    if (!empty($error)) {
      $error = "invalid username. " . $error;
      $this->signupError($error);
    }

    $error = $this->validateEmail($this->email);
    if (!empty($error)) {
      $error = "invalid email. " . $error;
      $this->signupError($error);
    }

    $error = $this->validatePassword($this->password);
    if (!empty($error)) {
      $error = "invalid password. " . $error;
      $this->signupError($error);
    }

    $error = $this->checkUserExists($this->username, $this->email);
    if (!empty($error)) {
      $error = "Username check failed. " . $error;
      $this->signupError($error);
    }

    $error = $this->createUser($this->username, $this->email, $this->password);
    if (!empty($error)) {
      $error = "could not create user. " . $error;
      $this->signupError($error);
    }

    session_start();
    $_SESSION['username'] = $this->username;
  }

  private function checkEmptyInput() {
    if (empty($this->username)) {
      return "Username required";
    } else if (empty($this->email)) {
      return "Email required.";
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

  // checks if format of user-input email is valid
  private function validateEmail($email) {
    if (filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
      return "Improper format.";
    }

    if (strlen($email) > $this->emailMaxLength) {
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
