<?php

class Login extends DatabaseHandler {
  protected function checkUserExists($username) {
    $sql = "SELECT * FROM users WHERE username = ?";
    $pdo = $this->connect();
    $statement = $pdo->prepare($sql);

    if ( !$statement->execute(array($username)) ) {
      return "Database check failed.";
    }

    if ($statement->rowCount() === 0) {
      return "Username does not exist.";
    }

    $statement = null;
    return "";
  }

  protected function comparePassword($username, $password) {
    $sql = "SELECT password FROM users WHERE username = ?";
    $pdo = $this->connect();
    $statement = $pdo->prepare($sql);

    if ( !$statement->execute(array($username)) ) {
      return "Database error.";
    }

    $row = $statement->fetch();
    $combined = $row['password'];

    $storedPassword = substr($combined, 0, 32);
    $salt = substr($combined, 32, 32);

    $hashed = $this->hash($password, $salt);

    if ($hashed !== $storedPassword) {
      return "Incorrect password.";
    }

    $statement = null;
    return "";
  }

  private function hash($password, $salt_hex) {
    $iterations = 600000;
    $salt = hex2bin($salt_hex);
    $hash = hash_pbkdf2("sha256", $password, $salt, $iterations, 16);

    $hash_hex = bin2hex($hash);
    return $hash_hex;
  }
}
