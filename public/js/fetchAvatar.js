async function populateAvatar() {
  let av = document.getElementsByClassName('avatar')[0];
  const response = await fetch("getAvatar.php");
  const res = await response.json();
  if (response.status !== 200) {
    console.log(res);
  } else {
    av.src = res['success'];
  }
}

populateAvatar();
