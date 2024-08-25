async function populateScribble() {
  let sc = document.getElementsByClassName('scribble-container')[0];
  const response = await fetch("http://localhost:80/getScribble.php?id=" + sc.id);
  if (response.status !== 200) {
    //TODO show error on page
    console.log(response.text);
  }
  const res = await response.json();
  const scribble = res['scribble']
console.log(scribble)
  let si = document.getElementsByClassName('scribble-image')[0];
  si.src = scribble.data_url;

  let sa = document.getElementsByClassName('scribble-author')[0];
  sa.innerHTML = scribble.username;
  sa.href = "users.php?name=" + scribble.username;

  let st = document.getElementsByClassName('scribble-title')[0];
  st.innerHTML = scribble.title;
}

async function setAvatar(username) {
  let sc = document.getElementsByClassName('scribble-container')[0];

  const response = await fetch("http://localhost:80/putAvatar.php?id=" + sc.id, {
    method: 'PUT',
  });

  if (response.status !== 200) {
    //TODO show error on page
    res = await response.json();
    console.log(res);
  }

  // trigger refresh
  // TODO only refetch avatar instead
  location.href = "http://localhost:80/scribble.php?id=" + sc.id;
}

populateScribble();
