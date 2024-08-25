async function populateAvatar() {
  let av = document.getElementsByClassName('avatar')[0];
  const response = await fetch("getAvatar.php");
  if (response.status !== 200) {
    console.log(response.text);
  }
  const res = await response.json();
  av.src = res['success'];
}

populateAvatar();
