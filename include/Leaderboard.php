<?php
require_once("DatabaseHandler.php");

enum LeaderboardColumn: string {
  case Username = "username";
  case TotalScribbles = "total_scribbles";
  case AvatarUse = "avatar_use";
  case Likes = "likes";
  case Dislikes = "dislikes";
  case LikeRatio = "like_ratio";
}

enum LeaderboardSortDir: int {
  case Up = SORT_ASC;
  case Down = SORT_DESC;
}

class Leaderboard extends DatabaseHandler {
  private $hidden;
  private $maxRows;
  private $sortCol;
  private $sortDir;
  private $board;

  public function __construct(LeaderboardColumn $sortCol = LeaderboardColumn::TotalScribbles, LeaderboardSortDir $sortDir = LeaderboardSortDir::Down) {
    $this->readMaxRows();
    $this->readHidden();
    $this->sortCol = $sortCol;
    $this->sortDir = $sortDir;
    $this->populate();
  }

  public function getBoard() {
    return $this->board;
  }

  public function getMaxRows() {
    return $this->maxRows;
  }

  protected function readMaxRows() {
    $sql = "SELECT max_rows FROM leaderboard";
    $pdo = $this->connect();
    $statement = $pdo->query($sql);

    $this->maxRows = $statement->fetchColumn();

    $statement = null;
    return "";
  }

  protected function writeMaxRows($num) {
    $sql = "UPDATE leaderboard SET max_rows = ? WHERE id = 1";
    $pdo = $this->connect();
    $statement = $pdo->prepare($sql);

    if ( !$statement->execute(array($num)) ) {
      return "Database update failed.";
    }

    $statement = null;
    return "";
  }

  protected function writeSort(LeaderboardColumn $col, $dir) {
    $sql = "UPDATE max_rows FROM leaderboard where id = 1";
    $pdo = $this->connect();
    $statement = $pdo->prepare($sql);

    if ( !$statement->execute(array($num)) ) {
      return "Database lookup failed.";
    }

    $statement = null;
    return "";
  }

  protected function readHidden() {
    $sql = "SELECT column_name FROM leaderboard_hidden_columns";
    $pdo = $this->connect();
    $this->hidden = $pdo->query($sql)->fetchAll(PDO::FETCH_COLUMN);
    return "";
  }

  public function columnHidden(LeaderboardColumn $col) {
    return in_array($col, $this->hidden);
  }

  public function populate() {
    // select each user
    //   select all scribbles by them, calc totals
    //   sort by sortcol in sortdir, truncate by numrows
    //   set class variable
    $sql = "SELECT * FROM users";
    $pdo = $this->connect();
    $statement = $pdo->prepare($sql);

    if ( !$statement->execute(array()) ) {
      return "Database lookup failed.";
    }

    $users = array();
    while ($row = $statement->fetch()) {
      $users[] = array("username" => $row["username"]);
    }

    foreach ($users as &$stats) {
      // find how many scribbles the given user created
      $sql = "SELECT COUNT(*) FROM scribbles INNER JOIN users WHERE users.username = ? AND users.id = scribbles.user";
      $statement = $pdo->prepare($sql);
      if ( !$statement->execute(array($stats["username"])) ) {
        return "Database lookup failed.";
      }

      $stats["total_scribbles"] = $statement->fetchColumn();

      // find how many users currently use an avatar created by given user
      $sql = <<<EOF
      SELECT COUNT(*) FROM scribbles
      JOIN users AS uploaders ON uploaders.username = ?
      JOIN users AS everyone
      WHERE scribbles.user = uploaders.id AND scribbles.id = everyone.avatar
EOF;
      $statement = $pdo->prepare($sql);
      if ( !$statement->execute(array($stats["username"])) ) {
        return "Database lookup failed.";
      }

      $stats["avatar_use"] = $statement->fetchColumn();

      // find how many total likes the given user has
      $sql = "SELECT SUM(likes) FROM scribbles INNER JOIN users WHERE users.username = ? AND users.id = scribbles.user";
      $statement = $pdo->prepare($sql);
      if ( !$statement->execute(array($stats["username"])) ) {
        return "Database lookup failed.";
      }

      $likes = $statement->fetchColumn();
      $stats["likes"] = is_string($likes) ? $likes : 0;

      // find how many total dislikes the given user has
      $sql = "SELECT SUM(dislikes) FROM scribbles INNER JOIN users WHERE users.username = ? AND users.id = scribbles.user";
      $statement = $pdo->prepare($sql);
      if ( !$statement->execute(array($stats["username"])) ) {
        return "Database lookup failed.";
      }

      $dislikes = $statement->fetchColumn();
      $stats["dislikes"] = is_string($dislikes) ? $dislikes : 0;

      // calculate the like/dislike ratio
      $stats["like_ratio"] = $stats["likes"] - $stats["dislikes"];

      //TODO add a likes to total scribbles ratio
    }

    // sort users by column and direction
    $dir = $this->sortDir->value;
    array_multisort(array_column($users, $this->sortCol->value), $dir, SORT_REGULAR, $users);

    // truncate for final leaderboard
    $leaderboard = array_slice($users, 0, $this->maxRows);

    $this->board = $leaderboard;

    $statement = null;
    return "";
  }
}
