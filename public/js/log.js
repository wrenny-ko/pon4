async function populateLogs() {
  const sp = new URLSearchParams(window.location.search);
  const endpoint = sp.get('endpoint');

  let endpointQuery = '';
  if (endpoint) {
    endpointQuery = '&endpoint=' + endpoint;
  }

  const response = await fetch('api/log.php?action=read' + endpointQuery);

  if (response.status !== 200) {
    //TODO show error on page
    let txt = await response.text();
    console.log(txt);
    return;
  }

  const j = await response.json();

  const ll = $('.log-box')[0];
  for (const [i, line] of Object.entries(j.data.lines)) {
    const lineEl = document.createElement('div');
    lineEl.classList.add('log-line');

    arr = line.split(" | ");

    const pre = document.createElement('div');
    pre.classList.add('log-line-text');
    pre.innerText = arr[0] + " | ";

    const endpointName = document.createElement('a');
    endpointName.classList.add('log-line-endpoint');
    name = arr[1];
    endpointName.innerText = name;
    endpointName.href = 'log.php?endpoint=' + name;
    if (endpoint === name) {
      endpointName.classList.add('selected');
      endpointName.href = 'log.php';
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

populateLogs();
