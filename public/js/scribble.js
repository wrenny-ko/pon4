async function populateScribble() {
  const sc = $('.scribble-container')[0];
  const response = await fetch("api/scribble.php?action=get&id=" + sc.id);
  if (response.status !== 200) {
    //TODO show error on page
    const res = await response.text();
    console.log(res);
    return;
  }
  const res = await response.json();
  const scribble = res['data']['scribble'];

  const si = $('.scribble-image')[0];
  si.src = scribble.data_url;

  const sa = $('.scribble-author')[0];
  sa.innerHTML = scribble.username;
  sa.href = "user.php?username=" + scribble.username;

  const st = $('.scribble-title')[0];

  // crude text wrap
  const title = scribble.title;
  if (title.length > 15) {
    title = title.substr(0, 14) + "\n" + title.substr(15);
  }
  st.innerText = title;

  $('.likes')[0].innerText = scribble.likes + " Likes";
  $('.dislikes')[0].innerText = scribble.dislikes + " Dislikes";
  
  const ratio = +scribble.likes - (+scribble.dislikes);
  const r = $('.ratio').first();
  r.text("Ratio: " + ratio.toString());
  if (ratio > 0) {
    r.css('color', '#26a269');
  } else if (ratio < 0) {
    r.css('color', '#e80d0d');
  } else {
    
    r.css('color', '#e66100');
  }
}

async function setAvatar(username) {
  let sc = document.getElementsByClassName('scribble-container')[0];

  const response = await fetch("putAvatar.php?id=" + sc.id, {
    method: 'PUT',
  });

  if (response.status !== 200) {
    //TODO show error on page
    res = await response.json();
    console.log(res);
  }

  // trigger refresh
  // TODO only refetch avatar instead
  location.href = "scribble.php?id=" + sc.id;
}

async function deleteScribble(id) {
  const response = await fetch("api/scribble.php?action=delete&id=" + id, {
    method: 'DELETE',
  });

  if (response.status !== 200) {
    //TODO show error on page
    res = await response.json();
    console.log(res);
    return;
  }

  location.href = "index.php";
}

async function like(username) {
  const sc = $('.scribble-container')[0];
  const response = await fetch("api/scribble.php?action=like&id=" + sc.id, {
    method: 'PUT',
  });

  if (response.status !== 200) {
    //TODO show error on page
    res = await response.json();
    console.log(res);
  }

  populateScribble();
}

async function dislike(username) {
  const sc = $('.scribble-container')[0];
  const response = await fetch("api/scribble.php?action=dislike&id=" + sc.id, {
    method: 'PUT',
  });

  if (response.status !== 200) {
    //TODO show error on page
    res = await response.json();
    console.log(res);
  }

  populateScribble();
}

populateScribble();
