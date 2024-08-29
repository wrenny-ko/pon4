async function populateLeaderboardAvatars() {
  for (const av of $('.leaderboard-avatar')) {
    const username = av.id.substring(7);
    const response = await fetch('getAvatar.php?username=' + username);
    if (response.status !== 200) {
      console.log(response.text);
    }
    const res = await response.json();
    av.src = res['success'];
  }
}

populateLeaderboardAvatars();
