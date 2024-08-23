<?php

class ScribbleController extends Scribble {
  private function error($msg) {
    $msg = "Scribble error. " . $msg;
    echo json_encode(array("error" => $msg));
    http_response_code(400);
    exit();
  }

  public function handlePost() {
    if ($_SERVER["REQUEST_METHOD"] !== 'POST') {
      $this->error("Only accept POST on this route");
    }

    // Checks
    /////////////////////////////////////////////////////////////////////
    // check for behavior when a form was too large to accept
    if (empty($_POST)) {
      $length = $_SERVER['CONTENT_LENGTH'];

      $umf = return_bytes(ini_get('upload_max_filesize'));
      $pms = return_bytes(ini_get('post_max_size'));
      $less = ($umf > $pms) ? $pms : $umf;
      if ($length >= $less) {
        $this->error("Exceeded upload byte size limit of " . format_bytes($less));
      }
    }

    if (empty($_POST['scribble'])) {
      $this->error("Required json not present");
    }

    session_start();

    /*
    // enforce logged in
    if (!isset($_SESSION['username'])) {
      header("location: index.php");
    }
    */

    $username = "anonymous";
    if (isset($_SESSION['username'])) {
      $username = $_SESSION['username'];
    }

    $json = $_POST['scribble'];
    if(!json_validate($json)) {
      $this->error("Invalid json");
    }

    $scribble = json_decode($json);
    if (!isset($scribble->data_url) || !isset($scribble->title)) {
      $this->error("Missing json fields");
    }

    if (!is_string($scribble->data_url)) {
      $this->error("Improperly formatted json fields.");
    }

    $error = $this->createScribble($username, $scribble->data_url);
    if (!empty($error)) {
      $this->error("Creation failed. " . $error);
    }

    if (!isset($this->id)) {
      $this->error("Can't find scribble id.");
    }

    http_response_code(201);
    echo json_encode(array("success" => $this->id));
  }

  public function handleAvatarGet($username) {
    if ($_SERVER["REQUEST_METHOD"] !== 'GET') {
      $this->error("Only accept GET on this route.");
    }

    $error = $this->readScribbleAvatar($username);
    if (!empty($error)) {
      $this->error("Can't read scribble avatar. " . $error);
    }

    echo json_encode(array("success" => $this->data_url));
  }
}
