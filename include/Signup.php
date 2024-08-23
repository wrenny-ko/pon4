<?php

class Signup extends DatabaseHandler {
  protected function createUser($username, $email, $password) {
    $sql = "INSERT INTO users (username, email, password) VALUES (?, ?, ?)";
    $pdo = $this->connect();
    $statement = $pdo->prepare($sql);

    $hashed = $this->saltAndHash($password);

    $error = "";
    if(!$statement->execute(array($username, $email, $hashed))) {
      $error = "Database error.";
    }

    $statement = null;
    return $error;
  }

  protected function checkUserExists($username, $email) {
    $sql = "SELECT * FROM users WHERE username = ? OR email = ?";
    $pdo = $this->connect();
    $statement = $pdo->prepare($sql);

    $error = "";
    if ( !$statement->execute(array($username, $email)) ) {
      $error = "Database failure.";
    }

    if ($statement->rowCount() > 0) {
      $error = "Already taken.";
    }

    $statement = null;
    return $error;
  }

  // generate 64 bytes hex string of hashed password with appended salt
  //   - 32 bytes hex string of hashed password
  //   - 32 bytes hex string of salt
  private function saltAndHash($password) {
    $iterations = 600000;
    $salt = random_bytes(16);
    $hash = hash_pbkdf2("sha256", $password, $salt, $iterations, 16);

    $salt_hex = bin2hex($salt);
    $hash_hex = bin2hex($hash);

    return $hash_hex . $salt_hex;
  }
}
