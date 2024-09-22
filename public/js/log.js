export class Log {
  name = 'log';
  title = 'API Logs';
  once = false;
  table = null;

  setup() {
    this.runTable();
  }

  again() {
    return;
  }

  teardown() {
    return;
  }

  async runTable() {
    if (this.once) {
      this.table.draw();
      return;
    }

    this.once = true;

    function renderUsername(data) {
      return '<a href="user?username=' + data + '" class="leaderboard-user site-nav selectable">' + 
               '<div class="leaderboard-username">' + data + '</div>' +
             '</a>';
    }

    this.table = new DataTable('#log-table', {
      ajax: 'api/log.php?action=fetch_rows',
      processing: true,
      serverSide: true,
      columns: [
        { "data": "timestamp", "name": "timestamp", "title": "Timestamp",
          "orderSequence": ["asc", "desc"], "className": "dt-center",
        },
        { "data": "endpoint", "name": "endpoint", "title": "Endpoint",
          "orderSequence": ["asc", "desc"], "className": "dt-center"
        },
        { "data": "method", "name": "method", "title": "Method",
          "orderSequence": ["asc", "desc"], "className": "dt-center"
        },
        { "data": "username", "name": "username", "title": "Username",
          "orderSequence": ["asc", "desc"], "className": "dt-center",
          "render": renderUsername,
        },
        { "data": "success", "name": "success", "title": "Success",
          "orderSequence": ["asc", "desc"], "className": "dt-center"
        },
        { "data": "message", "name": "message", "title": "Message",
          "orderSequence": ["asc", "desc"], "className": "dt-center"
        }
      ],
      order: [[0, 'desc']],
      oLanguage: {
        sSearch: "Search Current Sort Column:"
      }
    });

    $.fn.dataTable.ext.errMode = 'none'; //prevents the alert() calls
  }

  async populateLogs() {
    const title = $('.endpoint-title')[0];
    title.innerText = 'API Logs';

    const sp = new URLSearchParams(window.location.search);
    const endpoint = sp.get('endpoint');

    let endpointQuery = '';
    if (endpoint) {
      endpointQuery = '&endpoint=' + endpoint;
      title.innerText += ' - ' + endpoint;
    }

    const response = await axios.get(
      'api/log.php?action=read' + endpointQuery,
    ).catch(err => {
      console.log(err.response);
      $('.log-box')[0].innerText = err.response.data.error;
    });

    const ll = $('.log-box')[0];
    ll.innerHTML = '';
    for (const [i, line] of Object.entries(response.data.lines)) {
      const lineEl = document.createElement('div');
      lineEl.classList.add('log-line');

      let arr = line.split(" | ");

      const pre = document.createElement('div');
      pre.classList.add('log-line-text');
      pre.innerText = arr[0] + " | ";

      const endpointName = document.createElement('a');
      endpointName.classList.add('log-line-endpoint');
      endpointName.classList.add('site-nav');
      name = arr[1];
      endpointName.innerText = name;
      endpointName.href = '/log?endpoint=' + name;
      if (endpoint === name) {
        endpointName.classList.add('selected');
        endpointName.href = 'log';
      }

      const post = document.createElement('div');
      post.classList.add('log-line-text');
      post.innerText = " | " + arr.slice(2).join(" | ");

      lineEl.appendChild(pre);
      lineEl.appendChild(endpointName);
      lineEl.appendChild(post);

      ll.appendChild(lineEl);
    }
  }
}
