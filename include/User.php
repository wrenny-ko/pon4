<?php
require_once("DatabaseHandler.php");

class User extends DatabaseHandler {
  protected $username;
  protected $email;
  protected $password;

  private $UsernameMaxLength = 20;
  private $emailMaxLength = 40;
  private $PasswordMinLength = 5;
  private $PasswordMaxLength = 40;

  public function __construct() {
    $err = $this->connect();
    if (!!$err) {
      return "Database connect error. " . $err;
    }
  }

  public function __destruct() {
    $this->pdo = null;
  }

  protected function createUser($username, $email, $password) {
    $sql = "INSERT INTO users (username, email, password) VALUES (?, ?, ?)";
    $statement;
    try {
      $statement = $this->pdo->prepare($sql);

      $escaped_username = htmlspecialchars($username);
      $hashed = $this->saltAndHash($password);

      $error = "";
      if(!$statement->execute(array($escaped_username, $email, $hashed))) {
        $error = "Database error.";
      }
    } catch (PDOException $e) {
        return "Database execute error.";
    }

    $statement = null;
    return $error;
  }

  protected function checkUserExists($username) {
    $sql = "SELECT * FROM users WHERE username = ?";
    $statement;
    try {
      $statement = $this->pdo->prepare($sql);

      if ( !$statement->execute(array($username)) ) {
        return "Database check failed.";
      }

      if ($statement->rowCount() === 0) {
        return "Username does not exist.";
      }
    } catch (PDOException $e) {
        return "Database execute error.";
    }

    $statement = null;
    return "";
  }

  protected function checkUserExistsEmail($username, $email) {
    $sql = "SELECT * FROM users WHERE username = ? OR email = ?";
    $statement;
    try {
      $statement = $this->pdo->prepare($sql);

      $error = "";
      if ( !$statement->execute(array($username, $email)) ) {
        $error = "Database failure.";
      }

      if ($statement->rowCount() > 0) {
        $error = "Username/email taken.";
      }
    } catch (PDOException $e) {
        return "Database execute error.";
    }

    $statement = null;
    return $error;
  }

  protected function comparePassword($username, $password) {
    $sql = "SELECT password FROM users WHERE username = ?";
    $statement;
    try {
      $statement = $this->pdo->prepare($sql);

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
    } catch (PDOException $e) {
        return "Database execute error.";
    }

    $statement = null;
    return "";
  }

  protected function updateAvatar($scribble_id, $username) {
    $sql = "UPDATE users SET avatar = ? WHERE username = ?";
    $statement;
    try {
      $statement = $this->pdo->prepare($sql);

      if ( !$statement->execute(array($scribble_id, $username)) ) {
        return "Database update failed.";
      }
    } catch (PDOException $e) {
        return "Database execute error.";
    }

    $statement = null;
    return "";
  }

  protected function loginUser() {
    $error = $this->checkEmptyLoginInput();
    if (!!$error) {
      return "Empty login fields. " . $error;
    }

    $error = $this->validateUsername($this->username);
    if (!!$error) {
      return "Invalid username. " . $error;
    }

    $error = $this->validatePassword($this->password);
    if (!!$error) {
      return "Invalid password. " . $error;
    }

    $error = $this->checkUserExists($this->username);
    if (!!$error) {
      return "Username check failed. " . $error;
    }

    $error = $this->comparePassword($this->username, $this->password);
    if (!!$error) {
      return "Password check failed. " . $error;
    }

    session_start();
    $_SESSION['username'] = $this->username;
  }

  private function checkEmptyLoginInput() {
    if (!$this->username) {
      return "Username required.";
    } else if (!$this->password) {
      return "Password required.";
    }
    return "";
  }

  protected function signupUser() {
    $error = $this->checkEmptySignupInput();
    if (!!$error) {
      return "Empty input. " . $error;
    }

    $error = $this->validateUsername($this->username);
    if (!!$error) {
      return "Invalid username. " . $error;
    }

    $error = $this->validateEmail($this->email);
    if (!!$error) {
      return "Invalid email. " . $error;
    }

    $error = $this->validatePassword($this->password);
    if (!!$error) {
      return "Invalid password. " . $error;
    }

    $error = $this->checkUserExistsEmail($this->username, $this->email);
    if (!!$error) {
      return "Failed check. " . $error;
    }

    $error = $this->createUser($this->username, $this->email, $this->password);
    if (!!$error) {
      return "Could not create user. " . $error;
    }

    $_SESSION['username'] = $this->username;
  }

  private function checkEmptySignupInput() {
    if (!$this->username) {
      return "Username required";
    } else if (!$this->email) {
      return "Email required.";
    } else if (!$this->password) {
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

    if (strlen($username) > $this->UsernameMaxLength) {
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

    if (strlen($password) < $this->PasswordMinLength) {
      return "Too short; must be between " . 
      $this->passwordMinLength . "-" .
      $this->passwordMaxLength . " characters.";
    }

    if (strlen($password) > $this->PasswordMaxLength) {
      return "Too long; must be between " . 
      $this->passwordMinLength . "-" .
      $this->passwordMaxLength . " characters.";
    }

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
