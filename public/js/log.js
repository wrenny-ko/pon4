export class Log {
  name = 'log';
  title = 'API Logs';

  setup() {
    this.populateLogs();
  }

  again() {
    this.populateLogs();
  }

  teardown() {
    return;
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
