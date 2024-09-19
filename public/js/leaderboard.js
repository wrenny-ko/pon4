export class Leaderboard {
  static pageName = 'leaderboard';
  static title = "Leaderboard";
  once = false;

  setup() {
    if ($('.show-set-max-rows-form').length) {
      $('.set-max-rows-button')[0].addEventListener('click', this.handleSetMaxRows);
      $('.show-set-max-rows-form')[0].addEventListener('click', this.showSetMaxRows);
    }
    this.runTable();
  }

  again() {
    return;
  }

  teardown() {
    if ($('.show-set-max-rows-form').length) {
      $('.set-max-rows-button')[0].removeEventListener('click', this.handleSetMaxRows);
      $('.show-set-max-rows-form')[0].removeEventListener('click', this.showSetMaxRows);
      this.hideSetMaxRows();
    }
  }

  showSetMaxRows() {
    $('.set-max-rows-form')[0].classList.remove('hidden');
    $('.show-set-max-rows-form')[0].classList.add('hidden');
  }

  hideSetMaxRows() {
    $('.set-max-rows-form')[0].classList.add('hidden');
    $('.show-set-max-rows-form')[0].classList.remove('hidden');
  }

  async handleSetMaxRows() {
    const val = $('.set-max-rows-input')[0].value;
    const response = await axios.put(
      'api/leaderboard.php?action=set_max_rows&max_rows=' + val,
      {
        method: 'PUT',
      }
    ).catch(error => {
      console.log(error.response);
    });

    $('#tablify-me').DataTable().ajax.reload();
  }

  async runTable() {
    if (this.once) {
      return;
    }

    this.once = true;

    function renderAvatar(data) {
      return '<a href="user.php?username=' + data.username + '" class="leaderboard-user selectable">' + 
               '<img class="leaderboard-avatar" src=' + data.data_url + '></img>' +
               '<div class="leaderboard-username">' + data.username + '</div>' +
             '</a>';
    }

    let table = new DataTable('#tablify-me', {
      ajax: 'api/leaderboard.php?action=fetch_rows',
      processing: true,
      serverSide: true,
      paging: false,
      searching: false,
      columns: [
        { "data": "username", "name": "username", "title": "Username",
          "orderSequence": ["asc", "desc"], "className": "dt-center userfield",
          "render": renderAvatar,
        },
        { "data": "total_scribbles", "name": "total_scribbles", "title": "Total Scribbles",
          "orderSequence": ["desc", "asc"], "className": "dt-center"
        },
        { "data": "avatar_use", "name": "avatar_use", "title": "Avatar Use",
          "orderSequence": ["desc", "asc"], "className": "dt-center"
        },
        { "data": "likes", "name": "likes", "title": "Likes",
          "orderSequence": ["desc", "asc"], "className": "dt-center"
        },
        { "data": "dislikes", "name": "dislikes", "title": "Dislikes",
          "orderSequence": ["desc", "asc"], "className": "dt-center"
        },
        { "data": "like_ratio", "name": "like_ratio", "title": "Like Ratio",
          "orderSequence": ["desc", "asc"], "className": "dt-center"
        }
      ],
      order: [[1, 'desc']]
    });

    $.fn.dataTable.ext.errMode = 'none'; //prevents the alert() calls
  }
}
