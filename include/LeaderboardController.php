<?php

class LeaderboardController extends Leaderboard {
  public function error($msg) {
    echo json_encode(array("error" => "Leaderboard error. " . $msg));
    http_response_code(400);
    exit();
  }

  public function handlePutLeaderboard($maxRows) {
    $this->writeMaxRows($maxRows);
    $this->readMaxRows();
    echo json_encode(array("max_rows" => $this->getMaxRows()));
    exit();
  }
}
