async function handleSetMaxRows() {
  const val = $('.set-max-rows-input')[0].value;

  const response = await fetch("api/leaderboard.php?action=set_max_rows&max_rows=" + val, {
    method: 'PUT',
  });

  if (response.status !== 200) {
    //TODO show error on page
    t = await response.text();
    console.log(t);
    return;
  }

  window.location.href = window.location.href;
}

async function insertSortDir() {
  const sp = new URLSearchParams(window.location.search);
  const toSelect = '#sort-by-' + sp.get('sortCol').replace('_', '-');
  const selected = $(toSelect)[0];
  selected.classList.add('selected');

  const dirButton = document.createElement('a');
  dirButton.classList.add('sort-dir');
  let dir = '';
  if (sp.get('sortDir') === 'down') {
    dir = 'up';
    dirButton.innerText = '^';
  } else {
    dir = 'down';
    dirButton.innerText = 'v';
  }
  dirButton.href = 'leaderboard.php?sortCol=' + sp.get('sortCol') + '&sortDir=' + dir;
  selected.appendChild(dirButton);
}

insertSortDir();
