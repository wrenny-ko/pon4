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
  private $maxRows;
  protected $numTotalEntries;
  protected $numFilteredEntries;
  protected $board;

  public function __construct() {
    $err = $this->connect();
    if (!!$err) {
      return "Database connect error. " . $err;
    }

    $this->readMaxRows();
    $this->readNumTotalEntries();
  }

  public function getBoard() {
    return $this->board;
  }

  public function getMaxRows() {
    return $this->maxRows;
  }

  protected function readMaxRows() {
    $sql = "SELECT max_rows FROM leaderboard";

    $statement;
    try {
      $statement = $this->pdo->query($sql);
    } catch (PDOException $e) {
      return "Database query error.";
    }

    $this->maxRows = $statement->fetchColumn();

    $statement = null;
    return "";
  }

  protected function writeMaxRows($num) {
    $sql = "UPDATE leaderboard SET max_rows = ? WHERE id = 1";

    $statement;
    try {
      $statement = $this->pdo->prepare($sql);

      if ( !$statement->execute(array($num)) ) {
        return "Database update failed.";
      }
    } catch (PDOException $e) {
      return "Database execute error.";
    }

    $statement = null;
    return "";
  }

  protected function readNumTotalEntries() {
    $sql = "SELECT COUNT(*) FROM users";

    try {
      $statement = $this->pdo->query($sql);

      $this->numTotalEntries = $statement->fetchColumn();
    } catch (PDOException $e) {
      return "Database query error.";
    }

    $statement = null;
    return "";
  }

  public function populate(LeaderboardColumn $sortCol = LeaderboardColumn::TotalScribbles, LeaderboardSortDir $sortDir = LeaderboardSortDir::Down) {
    // select each user
    //   select all scribbles by them, calc totals
    //   sort by sortcol in sortdir, truncate by max numrows
    //   set class variable
    $sql = "SELECT * FROM users";

    $statement;
    try {
      $statement = $this->pdo->prepare($sql);
      if ( !$statement->execute(array()) ) {
        return "Database lookup failed.";
      }
    } catch (PDOException $e) {
      return "Database error.";
    }

    $users = array();
    while ($row = $statement->fetch()) {
      $users[] = array("username" => $row["username"]);
    }

    foreach ($users as &$stats) {
      // find how many scribbles the given user created
      $sql = "SELECT COUNT(*) FROM scribbles INNER JOIN users WHERE users.username = ? AND users.id = scribbles.user";
      try {
        $statement = $this->pdo->prepare($sql);
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
        $statement = $this->pdo->prepare($sql);
        if ( !$statement->execute(array($stats["username"])) ) {
          return "Database lookup failed.";
        }

        $stats["avatar_use"] = $statement->fetchColumn();

        // find how many total likes the given user has
        $sql = "SELECT SUM(likes) FROM scribbles INNER JOIN users WHERE users.username = ? AND users.id = scribbles.user";
        $statement = $this->pdo->prepare($sql);
        if ( !$statement->execute(array($stats["username"])) ) {
          return "Database lookup failed.";
        }

        $likes = $statement->fetchColumn();
        $stats["likes"] = is_string($likes) ? $likes : 0;

        // find how many total dislikes the given user has
        $sql = "SELECT SUM(dislikes) FROM scribbles INNER JOIN users WHERE users.username = ? AND users.id = scribbles.user";
        $statement = $this->pdo->prepare($sql);
        if ( !$statement->execute(array($stats["username"])) ) {
          return "Database lookup failed.";
        }

        $dislikes = $statement->fetchColumn();
        $stats["dislikes"] = is_string($dislikes) ? $dislikes : 0;
      } catch (PDOException $e) {
        return "Database error.";
      }

      // calculate the like/dislike ratio
      $stats["like_ratio"] = $stats["likes"] - $stats["dislikes"];

      //TODO add a likes to total scribbles ratio
    }

    // sort users by column and direction
    $dir = $sortDir->value; // have to break this out of the function because of a 'read-only' error below
    array_multisort(array_column($users, $sortCol->value), $dir, SORT_REGULAR, $users);

    // truncate for final leaderboard
    $leaderboard = array_slice($users, 0, $this->maxRows);

    $this->board = $leaderboard;
    $this->numFilteredEntries = $this->maxRows;

    $statement = null;
    return "";
  }
}
