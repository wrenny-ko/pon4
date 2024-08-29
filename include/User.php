<?php
require_once("DatabaseHandler.php");

class User extends DatabaseHandler {
  protected function createUser($username, $email, $password) {
    $sql = "INSERT INTO users (username, email, password) VALUES (?, ?, ?)";
    $pdo = $this->connect();
    $statement = $pdo->prepare($sql);

    $escaped_username = htmlspecialchars($username);
    $hashed = $this->saltAndHash($password);

    $error = "";
    if(!$statement->execute(array($escaped_username, $email, $hashed))) {
      $error = "Database error.";
    }

    $statement = null;
    return $error;
  }

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

  protected function checkUserExistsEmail($username, $email) {
    $sql = "SELECT * FROM users WHERE username = ? OR email = ?";
    $pdo = $this->connect();
    $statement = $pdo->prepare($sql);

    $error = "";
    if ( !$statement->execute(array($username, $email)) ) {
      $error = "Database failure.";
    }

    if ($statement->rowCount() > 0) {
      $error = "Username/email taken.";
    }

    $statement = null;
    return $error;
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

  protected function updateAvatar($scribble_id, $username) {
    $sql = "UPDATE users SET avatar = ? WHERE username = ?";
    $pdo = $this->connect();
    $statement = $pdo->prepare($sql);

    if ( !$statement->execute(array($scribble_id, $username)) ) {
      return "Database update failed.";
    }

    $statement = null;
    return "";
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

  private function hash($password, $salt_hex) {
    $iterations = 600000;
    $salt = hex2bin($salt_hex);
    $hash = hash_pbkdf2("sha256", $password, $salt, $iterations, 16);

    $hash_hex = bin2hex($hash);
    return $hash_hex;
  }
}
