async function handleSetMaxRows() {
  const val = $('.set-max-rows-input')[0].value;

  const res = await fetch("http://localhost:80/putLeaderboard.php?maxRows=" + val, {
    method: 'PUT',
  });

  if (response.status !== 200) {
    //TODO show error on page
    res = await response.json();
    console.log(res);
  }

  const sp = new URLSearchParams(window.location.search);
  window.location.href = 'leaderboard.php?sortCol=' + sp.get('sortCol') + '&sortDir=' + sp.get('sortDir');
}
