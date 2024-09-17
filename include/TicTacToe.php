<?php

const WinConditions = array(
  0x1c0, // 111 000 000 top row
  0x038, // 000 111 000 middle row
  0x007, // 000 000 111 bottom row
  0x124, // 100 100 100 left column
  0x092, // 010 010 010 middle column
  0x049, // 001 001 001 right column
  0x111, // 100 010 001 upper left to lower right diagonal
  0x054, // 001 010 100 upper right to lower left diagonal
);

class TicTacToeBoard {
  private $board;
  private $x;
  private $o;

  public function __construct($b = null, $x = null, $o = null) {
    $this->board = $b ?? 0;
    $this->x = $x ?? 0;
    $this->o = $o ?? 0;
  }

  public function resetBoard() {
    $this->board = 0;
    $this->x = 0;
    $this->o = 0;
  }

  public function getBoard() {
    return $this->board;
  }

  public function getBoardString() {
    $str = "";
    for ($mask = 0x100; $mask >= 0x001; $mask >>= 1) {
      $str .= ($this->board & $mask) ? (($this->x & $mask) ? "x" : "o") : "-";
    }
    return $str;
  }

  public function move($pos, $char) {
    if ($pos < 0 || $pos > 8) {
      return "position out of bounds";
    }

    $mask = 0x1 << (8 - $pos);

    if ($this->board & $mask) {
      return "invalid move. position taken";
    }

    if ($char === "x") {
      $this->x |= $mask;
    } elseif ($char === "o") {
      $this->o |= $mask;
    } else {
      return "invalid character (expected x or o).";
    }

    $this->board |= $mask;
    return "";
  }

  public function moveMask($mask, $char) {
    if ($this->board & $mask) {
      return "invalid move. position taken.";
    }

    if ($char === "x") {
      $this->x |= $mask;
    } elseif ($char === "o") {
      $this->o |= $mask;
    } else {
      return "invalid character (expected x or o).";
    }

    $this->board |= $mask;
    return "";
  }

  public function moveMaskNewBoard($mask, $char) {
    if ($this->board & $mask) {
      return "invalid move. position taken.";
    }

    $board = new TicTacToeBoard($this->board, $this->x, $this->o);

    if ($char === "x") {
      $board->x |= $mask;
    } elseif ($char === "o") {
      $board->o |= $mask;
    } else {
      return "invalid character (expected x or o).";
    }

    $board->board |= $mask;
    return $board;
  }

  public function checkWin() {
    foreach (WinConditions as $condMask) {
      if (($this->x & $condMask) === $condMask) {
        return "x";
      }
      if (($this->o & $condMask) === $condMask) {
        return "o";
      }
    }

    // check if board full
    if (($this->board & 0x1ff) === 0x1ff) {
      return "-"; // draw
    }

    return "";
  }

  public function getAvailableMoveMasks() {
    $moveMasks = array();
    for ($mask = 0x1; $mask <= 0x100; $mask <<= 1) {
      if (!($mask & $this->board)) {
        $moveMasks[] = $mask;
      }
    }
    return $moveMasks;
  }

  public function getOppositePlayer($ch) {
    switch ($ch) {
      case "x":
        return "o";
        break;
      case "o":
        return "x";
        break;
    }
    return "-";
  }
}

class TicTacToeGame {
  private $playerChar;
  private $board;
  private $difficulty;

  public function __construct($ch, $difficulty) {
    $this->playerChar = $ch;
    $this->board = new TicTacToeBoard();
    $this->difficulty = $difficulty;
  }

  public function getBoard() {
    return $this->board->getBoardString();
  }

  public function resetBoard() {
    return $this->board->resetBoard();
  }

  // "easy", "medium", "hard"
  public function setDifficulty($difficulty) {
    $this->difficulty = $difficulty;
  }

  public function getDifficulty() {
    return $this->difficulty;
  }

  public function checkWin() {
    return $this->board->checkWin();
  }

  public function move($pos) {
    return $this->board->move($pos, $this->playerChar);
  }

  public function playAI() {
    $aiChar = $this->board->getOppositePlayer($this->playerChar);
    $tree = new TicTacToeTreeNode($this->board, $aiChar);
    $tree->populate();
    $this->board->moveMask( $tree->chooseNextMoveMask($this->difficulty), $aiChar );
  }
}

class TicTacToeTreeNode {
  public $board;
  public $children;
  public $currentPlayerChar;
  public $value;

  public function __construct($board, $player) {
    $this->children = array();
    $this->board = $board;
    $this->currentPlayerChar = $player;
    $this->value = 0;
  }

  public function pushMove($mask) {
    $newBoard = $this->board->moveMaskNewBoard($mask, $this->currentPlayerChar);
    $this->children[$mask] = new TicTacToeTreeNode(
      $newBoard,
      $newBoard->getOppositePlayer($this->currentPlayerChar)
    );
  }

  public function pushAllAvailableMoves() {
    $masks = $this->board->getAvailableMoveMasks();
    foreach ($masks as $mask) {
      $this->pushMove($mask);
    }
  }

  // recursively populate a tree of moves until a winner or draw condition is found
  // calculate a weighted minmax, node layers alternating scores between players
  public function populate() {
    $winner = $this->board->checkWin();
    if ($winner === $this->currentPlayerChar) {
      $this->value -= 10; // win
    } else if ($winner === $this->board->getOppositePlayer($this->currentPlayerChar)) {
      $this->value += 10; // lose
    } else if ($winner === "-") {
      $this->value -= 1; // draw
    } else {
      // no winner, populate another level
      $this->pushAllAvailableMoves();

      if ($this->board->getBoard() === 0) {
        // if this is the top level and no moves have been played,
        //   then skip populating and set value for the center
        $this->children[0x010]->value = 10;
      } else {
        // populate children
        $this->value = 0;
        foreach ($this->children as $mask => $child) {
          $child->populate();
          $this->value -= 0.2 * ($child->value); // weighted so top-level first-children wins are preferred
        }
      }
    }
  }

  public function chooseNextMoveMask($difficulty = "easy") {
    $pick = 0;
    switch ($difficulty) {
      case "easy":
        // easy difficulty: always choose random
        $pick = array_rand($this->children);
        break;

      case "medium":
        // medium difficulty: sometimes random, but usually choose optimal
        if ($difficulty === "medium" && rand(0, 100) >= 75) {
          $pick = array_rand($this->children);
          break;
        }
        // fall through to hard

      case "hard":
        // fall through

      default:
        // punish invalid $difficulty by defaulting to hardest
        // if board empty, pick center tile to save computation
        if ($this->board->getBoard() === 0) {
          $pick = 0x010;
          break;
        }

        $pickMask = array_key_first($this->children); // get first child movemask
        $pickValue = reset($this->children)->value; // get value of first child
        for ($child = next($this->children); !!$child; $child = next($this->children)) {
          $mask = key($this->children);
          if (
              $child->value > $pickValue ||                       // always select highest value
              ($child->value === $pickValue && rand(0, 100) > 50) // sometimes random select between equal values
             ) {
            $pickMask = $mask;
            $pickValue = $child->value;
          }
        }
        $pick = $pickMask;
        break;
    }
    return $pick;
  }
}
