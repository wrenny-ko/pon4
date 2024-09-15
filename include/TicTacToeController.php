<?php

enum BoardPosition: int {
  case X    = 1;
  case O    = -1;
  case Empty   = 0;
}

enum TicTacToeAction: string {
  case GetBoard = "get_board";
  case StartGame = "start_game";
  case Move = "move";
  case SetDifficulty = "set_difficulty";
  case GetDifficulty = "get_difficulty";
}

class TicTacToeController {
  const RouteMap = array(
    TicTacToeAction::GetBoard->value => array(
      "method" => RequestMethod::GET,
      "login_required" => true,
      "auth_levels" => array(AuthLevel::Beta)
    ),
    TicTacToeAction::StartGame->value => array(
      "method" => RequestMethod::POST,
      "login_required" => true,
      "auth_levels" => array(AuthLevel::Beta)
    ),
    TicTacToeAction::Move->value => array(
      "method" => RequestMethod::POST,
      "login_required" => true,
      "auth_levels" => array(AuthLevel::Beta)
    ),
    TicTacToeAction::SetDifficulty->value => array(
      "method" => RequestMethod::POST,
      "login_required" => true,
      "auth_levels" => array(AuthLevel::Beta)
    ),
    TicTacToeAction::GetDifficulty->value => array(
      "method" => RequestMethod::GET,
      "login_required" => true,
      "auth_levels" => array(AuthLevel::Beta)
    )
  );

  private $rest;
  private $action;

  public function __construct() {
    // set up first for logging/error response if needed
    $this->rest = new Rest();
    $this->rest->setupLogging("api.log", "tictactoe");

    if (!isset($_SERVER["REQUEST_METHOD"])) {
      $this->rest->error("request method not set");
    }

    $method;
    try {
      $method = RequestMethod::from($_SERVER["REQUEST_METHOD"]);
    } catch (\Throwable $e) {
      $this->rest->error("request method not supported");
    }

    $this->rest->setMethod($method);

    if (!isset($_GET["action"])) {
      $this->rest->error("requires an 'action' query string");
    }

    $action;
    try {
      $action = TicTacToeAction::from($_GET["action"]);
    } catch (\Throwable $e) {
      $this->rest->error("action not supported ");
    }
    $this->action = $action;

    $this->rest->setLoginRequired( self::RouteMap[$action->value]["login_required"] );
    $this->rest->setAuths( self::RouteMap[$action->value]["auth_levels"] );

    $this->rest->compareMethod(self::RouteMap[$action->value]["method"] );
    $this->rest->auth();

    /*
    $err = $this->connect();
    if (!!$err) {
      return "Database connect error. " . $err;
    }
    */
  }

    public function __destruct() {
    $this->rest = null;
    //$this->pdo = null;
  }

  public function handle() {
    $msg = $this->handleAction();
    if (!!$msg) {
      $this->rest->error($msg);
    }
    $this->rest->success($this->action->value);
  }

  private function handleAction() {
    switch ($this->action) {
      case TicTacToeAction::GetBoard:
        return $this->handleGetBoard();
        break;
      case TicTacToeAction::StartGame:
        return $this->handleStartGame();
        break;
      case TicTacToeAction::Move:
        return $this->handleMove();
        break;
      case TicTacToeAction::SetDifficulty:
        return $this->handleSetDifficulty();
        break;
      case TicTacToeAction::GetDifficulty:
        return $this->handleGetDifficulty();
        break;
    }
    return "action not found";
  }

  private function handleGetBoard() {
    if ( !isset($_SESSION["tictactoe"]) ) {
      return "game session not started yet.";
    }

    $game = $_SESSION["tictactoe"];
    $this->rest->setResponseField("board", $game->getBoard());

    $won = $game->checkWin();
    if (!!$won) {
      $this->rest->setResponseField("winner", $won);
    }

    return "";
  }

  private function handleStartGame() {
    $ch = $this->rest->getRequiredQueryField("player_char");
    if ($ch !== "x" && $ch !== "o") {
      return "invalid player_char (expect only 'x' or 'o')";
    }

    // first game initialized to "easy", subsequent games pull from previous game's difficulty
    $difficulty = "easy";
    if ( isset($_SESSION["tictactoe"]) ) {
      $difficulty = $_SESSION["tictactoe"]->getDifficulty();
    }

    $game = new TicTacToeGame($ch, $difficulty);
    if ($ch === "o") {
      $game->playAI();
    }

    $_SESSION["tictactoe"] = $game;
    $this->rest->setResponseField("board", $game->getBoard());

    return "";
  }

  private function handleMove() {
    $pos = $this->rest->getRequiredQueryField("pos");

    if ( !isset($_SESSION["tictactoe"]) ) {
      return "game session not started yet.";
    }
    $game = $_SESSION["tictactoe"];

    $msg = $game->move($pos);
    if (!!$msg) {
      return "error playing move: " . $msg;
    }
    $_SESSION["tictactoe"] = $game; // sync

    $won = $game->checkWin();
    if (!!$won) {
      $this->rest->setResponseField("board", $game->getBoard());
      $this->rest->setResponseField("winner", $won);
      return "";
    }

    $game->playAI();
    $won = $game->checkWin();
    if (!!$won) {
      $this->rest->setResponseField("winner", $won);
    }
    $_SESSION["tictactoe"] = $game; // sync

    $this->rest->setResponseField("board", $game->getBoard());

    return "";
  }

  private function handleSetDifficulty() {
    if ( !isset($_SESSION["tictactoe"]) ) {
      return "game session not started yet.";
    }

    $difficulty = $this->rest->getRequiredQueryField("difficulty");
    if ( !in_array($difficulty, array("easy", "medium", "hard")) ) {
      return "undefined difficulty; expected 'easy', 'medium', or 'hard'";
    }

    $_SESSION["tictactoe"]->setDifficulty($difficulty);
    $this->rest->setResponseField("difficulty", $_SESSION["tictactoe"]->getDifficulty());

    return "";
  }

  private function handleGetDifficulty() {
    if ( !isset($_SESSION["tictactoe"]) ) {
      return "game session not started yet.";
    }

    $this->rest->setResponseField("difficulty", $_SESSION["tictactoe"]->getDifficulty());

    return "";
  }
}
