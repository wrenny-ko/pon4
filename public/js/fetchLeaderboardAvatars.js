async function populateLeaderboardAvatars() {
  for (const av of $('.leaderboard-avatar')) {
    const username = av.id.substring(7);
    const response = await fetch('api/scribble.php?action=get_avatar&username=' + username);
    if (response.status !== 200) {
      console.log(res);
    } else {
      const res = await response.json();
      av.src = res.data.scribble.data_url;
    }
  }
}

populateLeaderboardAvatars();
