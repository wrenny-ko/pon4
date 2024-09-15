export class TicTacToe {
  static pageName = 'tictactoe';
  static title = 'Tic Tac Toe';

  constructor() {
    // set fangbot avatar
    axios.get('api/scribble.php?action=get&id=2')
      .then( res => {
        $('#fangbot-player-avatar')[0].src = res.data.scribble.data_url;
      })
      .catch( err => {
        console.log(err.response.data.error);
      });

    // get saved difficulty setting
    axios.get('api/tictactoe.php?action=get_difficulty')
      .then( res => {
        const wordContainer = $('.difficulty-word').first();
        wordContainer.text(res.data.difficulty);

        let val = 1;
        switch (res.data.difficulty) {
          case 'easy':
            val = 1;
            wordContainer.css('color', '#26a269');
            break;
          case 'medium':
            val = 2;
            wordContainer.css('color', '#e66100');
            break;
          case 'hard':
            val = 3;
            wordContainer.css('color', '#e80d0d');
            break;
        }

        $('.difficulty-slider')[0].value = val;
      })
      .catch( err => {
        // if no game previously started, then this get request will error
        // this sets a default value
        console.log(err.response.data.error);
        $('.difficulty-slider')[0].value = 1;
        const wordContainer = $('.difficulty-word').first();
        wordContainer.text('easy');
        wordContainer.css('color', '#26a269');
      });
  }

  async setup() {
    $('.difficulty-slider')[0].addEventListener('input', this.setDifficultyWord);
    for (let i = 0; i <= 8; i++) {
      const posEl = $('#position-' + i)[0];
      posEl.removeAttribute('src'); // clear any placed tile
      posEl.addEventListener('click', this.postMove);
      posEl.removeAttribute('disabled');
    }

    // display choose X or O modal
    $('.choose-player-modal')[0].classList.remove('hidden');
    $('#player-choose-x')[0].addEventListener('click', this.initGame);
    $('#player-choose-o')[0].addEventListener('click', this.initGame);

    $('.tictactoe-reset-board-button')[0].addEventListener('click', this.again);

    $('.tictactoe-message-box')[0].innerText = '';
    $('.tictactoe-error-box')[0].innerText = '';
  }

  async again() {
    for (let i = 0; i <= 8; i++) {
      $('#position-' + i)[0].removeAttribute('src');
    }

    // display choose X or O modal
    $('.choose-player-modal')[0].classList.remove('hidden');
    $('#player-choose-x')[0].addEventListener('click', this.initGame);
    $('#player-choose-o')[0].addEventListener('click', this.initGame);

    $('.tictactoe-message-box')[0].innerText = '';
    $('.tictactoe-error-box')[0].innerText = '';
  }

  teardown() {
    $('.difficulty-slider')[0].removeEventListener('input', this.setDifficultyWord);
    for (let i = 0; i <= 8; i++) {
      const posEl = $('#position-' + i)[0];
      posEl.removeAttribute('src'); // clear any placed tile
      posEl.removeEventListener('click', this.postMove);
      posEl.removeAttribute('disabled');
    }

    $('.tictactoe-reset-board-button')[0].removeEventListener('click', this.again);
  }

  async initGame(e) {
    e.preventDefault();

    const difficulty = $('.difficulty-word')[0];
    if (difficulty.classList.contains('updated')) {
      await axios.post('api/tictactoe.php?action=set_difficulty&difficulty=' + difficulty.innerText);
      difficulty.classList.remove('updated');
    }

    const ch = e.target.id.substring(14);

    await axios.post('/api/tictactoe.php?action=start_game&player_char=' + ch)
      .then( res => {
        $('.tictactoe-error-box')[0].innerText = '';

        const b = res.data.board;
        for (let i = 0; i <= 8; i++) {
          const ch = b.substring(i, i + 1);
          const imgEl = $('#position-' + i)[0];
          imgEl.src = 'icon/' + ch + '.png';
          if (ch === '-') {
            imgEl.removeAttribute('disabled');
          } else {
            imgEl.setAttribute('disabled', '');
          }
        }
      }).catch( err => {
        $('.tictactoe-error-box')[0].innerText = err.response.data.error;
      });

    $('#user-player-avatar').next()[0].src = 'icon/' + ch + '.png';

    let fangChar = 'o';
    if (ch === 'o') {
      fangChar = 'x';
    }
    $('#fangbot-player-avatar').next()[0].src = 'icon/' + fangChar + '.png';

    $('.choose-player-modal')[0].classList.add('hidden');
    $('#player-choose-x')[0].removeEventListener('click', this.initGame);
    $('#player-choose-o')[0].removeEventListener('click', this.initGame);

    return false;
  }

  setDifficultyWord(e) {
    const val = $('.difficulty-slider')[0].value;
    const wordContainer = $('.difficulty-word').first();
    switch (+val) {
      case 1:
        wordContainer.text('easy');
        wordContainer.css('color', '#26a269');
        break;
      case 2:
        wordContainer.text('medium');
        wordContainer.css('color', '#e66100');
        break;
      case 3:
        wordContainer.text('hard');
        wordContainer.css('color', '#e80d0d');
        break;
    }

    // checked during the next time a move is clicked
    $('.difficulty-word')[0].classList.add('updated');
  }

  async getBoard() {
    axios.post('/api/tictactoe.php?action=start_game&player_char=' + ch)
      .then( res => {
        $('.tictactoe-error-box')[0].innerText = '';

        if (res.data.hasOwnProperty('winner')) {
          $('.tictactoe-message-box')[0].innerText = res.data.winner.toUpperCase() + " wins!";
        }

        const b = res.data.board;
        for (let i = 0; i <= 8; i++) {
          const ch = b.substring(i, i + 1);
          const imgEl = $('#position-' + i)[0];
          imgEl.src = 'icon/' + ch + '.png';
          if (ch === '-') {
            imgEl.removeAttribute('disabled');
          } else {
            imgEl.setAttribute('disabled', '');
          }
        }
      }).catch( err => {
        $('.tictactoe-error-box')[0].innerText = err.response.data.error;
      });
  }

  async postStartGame(e) {
    e.preventDefault();

    const difficulty = $('.difficulty-word')[0];
    if (difficulty.classList.contains('updated')) {
      await axios.post('api/tictactoe.php?action=set_difficulty&difficulty=' + difficulty.innerText);
      difficulty.classList.remove('updated');
    }

    const ch = e.target.id.substring(14);

    axios.post('/api/tictactoe.php?action=start_game&player_char=' + ch)
      .then( res => {
        $('.tictactoe-error-box')[0].innerText = '';

        const b = res.data.board;
        for (let i = 0; i <= 8; i++) {
          const ch = b.substring(i, i + 1);
          const imgEl = $('#position-' + i)[0];
          imgEl.src = 'icon/' + ch + '.png';
          if (ch === '-') {
            imgEl.removeAttribute('disabled');
          } else {
            imgEl.setAttribute('disabled', '');
          }
        }
      }).catch( err => {
        $('.tictactoe-error-box')[0].innerText = err.response.data.error;
      });

    return false;
  }

  async postMove(e) {
    e.preventDefault();

    const difficulty = $('.difficulty-word')[0];
    if (difficulty.classList.contains('updated')) {
      await axios.post('api/tictactoe.php?action=set_difficulty&difficulty=' + difficulty.innerText);
      difficulty.classList.remove('updated');
    }

    const pos = e.target.id.substring(9);
    axios.post('/api/tictactoe.php?action=move&pos=' + pos)
      .then( res => {
        if (res.data.hasOwnProperty('winner')) {
          const winchar = res.data.winner;
          let msg = "";
          if (winchar === '-') {
            msg = "Cat";
          } else {
            msg = winchar.toUpperCase() + " wins!";
          }

          $('.tictactoe-message-box')[0].innerText = msg;

          // disable board if won
          for (let i = 0; i <= 8; i++) {
            $('#position-' + i)[0].setAttribute('disabled', '');
          }
        }

        const b = res.data.board;
        for (let i = 0; i <= 8; i++) {
          const ch = b.substring(i, i + 1);
          const imgEl = $('#position-' + i)[0];
          imgEl.src = 'icon/' + ch + '.png';
          if (ch === '-') {
            imgEl.removeAttribute('disabled');
          } else {
            imgEl.setAttribute('disabled', '');
          }
        }

        $('.tictactoe-error-box')[0].innerText = '';
      })
      .catch( err => {
        $('.tictactoe-error-box')[0].innerText = err.response.data.error;
      });

    return false;
  }
}
