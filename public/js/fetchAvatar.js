async function populateAvatar() {
  let av = document.getElementsByClassName('avatar')[0];
  const response = await fetch("api/scribble.php?action=get_avatar");
  const res = await response.json();
  if (response.status !== 200) {
    console.log(res);
  } else {
    av.src = res['data']['scribble']['data_url'];
  }
}

populateAvatar();
