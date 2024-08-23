<?php

class Login extends DatabaseHandler {
  protected function checkUserExists($username) {
    $sql = "SELECT * FROM users WHERE username = ?";
    $pdo = $this->connect();
    $statement = $pdo->prepare($sql);

    if ( !$statement->execute(array($username)) ) {
      $statement = null;
      $error = "failed to check user";
      header("location: ../login.php?error=" . htmlspecialchars($error)); //TODO change
      exit();
    }

    $exists = false;
    if ($statement->rowCount() > 0) {
      $exists = true;
    }

    $statement = null;
    return $exists;
  }

  protected function comparePassword($username, $password) {
    $sql = "SELECT password FROM users WHERE username = ?";
    $pdo = $this->connect();
    $statement = $pdo->prepare($sql);

    if ( !$statement->execute(array($username)) ) {
      $statement = null;
      $error = "login failed";
      header("location: ../login.php?error=" . htmlspecialchars($error));
      exit();
    }

    $row = $statement->fetch();
    $combined = $row['password'];

    $storedPassword = substr($combined, 0, 32);
    $salt = substr($combined, 32, 32);

    $hashed = $this->hash($password, $salt);

    $matches = false;
    if ($hashed === $storedPassword) {
      $matches = true;
    }

    $statement = null;
    return $matches;
  }

  private function hash($password, $salt_hex) {
    $iterations = 600000;
    $salt = hex2bin($salt_hex);
    $hash = hash_pbkdf2("sha256", $password, $salt, $iterations, 16);

    $hash_hex = bin2hex($hash);
    return $hash_hex;
  }
}
