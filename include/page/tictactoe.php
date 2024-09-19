<div class="tictactoe-container">
  Tic Tac Toe
  <div class="tictactoe-board">
    <div class="tictactoe-row">
      <img class="player-icon clickable" id="position-0"/>
      <img class="player-icon clickable" id="position-1"/>
      <img class="player-icon clickable" id="position-2"/>
    </div>
    <div class="tictactoe-row">
      <img class="player-icon clickable" id="position-3"/>
      <img class="player-icon clickable" id="position-4"/>
      <img class="player-icon clickable" id="position-5"/>
    </div>
    <div class="tictactoe-row">
      <img class="player-icon clickable" id="position-6"/>
      <img class="player-icon clickable" id="position-7"/>
      <img class="player-icon clickable" id="position-8"/>
    </div>
  </div>
  <div class="tictactoe-controls">
    <button class="tictactoe-reset-board-button button">New Game</button>
  </div>
  <div class="players-info">
    <div class="player-info">
      <div class="player-info-icons">
        <img class="player-avatar" id="user-player-avatar" src="<?= $avatarSrc;?>" icon/>
        <img class="player-icon-tiny" src="icon/x.png" icon>
      </div>
      <?= $username; ?>
    </div>
    <div class="player-info">
      <div class="player-info-icons">
        <img class="player-avatar" id="fangbot-player-avatar" src="" icon/>
        <img class="player-icon-tiny" icon/>
      </div>
      FangBot
      <div class="difficulty-container">
        Difficulty:
        <div class="difficulty-word" style="color: #26a269;">easy</div>
        <input class="difficulty-slider" type="range" min="1" max="3" value="1"/>
      </div>
    </div>
  </div>
  <div class="tictactoe-message-box"></div>
  <div class="tictactoe-error-box"></div>
  <div class="choose-player-modal tictactoe-modal hidden">
    Tic Tac Toe
    <div class="player-icons">
      <img class="player-icon clickable" id="player-choose-x" src="icon/x.png" alt="tictactoe player X">
      <img class="player-icon clickable" id="player-choose-o" src="icon/o.png" alt="tictactoe player O">
    </div>
    Choose X or O. X plays first.
  </div>
</div>
