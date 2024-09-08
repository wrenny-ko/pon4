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

    let avatarUsernameList = [];

    function pushAvatar(data) {
      avatarUsernameList.push(data);
      return data;
    }

    let cachedAvatars = {};

    async function cacheAvatars() {
      let fetchList = [];

      avatarUsernameList.forEach( name => {
        if (cachedAvatars.hasOwnProperty(name)) {
          return;
        }
        fetchList.push(name);
      });

      if (fetchList.length > 0) {
        console.log("caching avatars for " + fetchList);
        const response = await axios.get(
          'api/scribble.php?action=get_avatars',
          {
            params: {
              usernames: fetchList.join(',')
            }
          }
        )

        const avatars = response.data.avatars;
        for (const [name, av] of Object.entries(avatars)) {
          cachedAvatars[name] = av.data_url;
        }
      }
    }

    async function populateAvatars() {
      await cacheAvatars();

      $('.userfield').slice(1).each( (k, field) => {
        const name = field.innerText;
        const data_url = cachedAvatars[name];
        const a = document.createElement('a');
        a.classList.add('leaderboard-user')
        a.classList.add('selectable');
        a.href = 'user.php?username=' + name;

        const img = document.createElement('img');
        img.classList.add('leaderboard-avatar');
        img.src = data_url;

        const uname = document.createElement('div');
        uname.classList.add('leaderboard-username');
        uname.innerText = name;

        a.appendChild(img);
        a.appendChild(uname);

        field.innerText = "";
        field.appendChild(a);
      });
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
          "render": pushAvatar,
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

    table.on('draw', function () {
      //console.log('Redraw occurred at: ' + new Date().getTime());
      populateAvatars();
    });

    $.fn.dataTable.ext.errMode = 'none'; //prevents the alert() calls
  }
}
