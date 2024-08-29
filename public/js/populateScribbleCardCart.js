async function populateScribbleCards() {
  const sp = new URLSearchParams(window.location.search);
  const search_param = sp.get("search");
  const username_param = sp.get("username");

  let url = "getScribble.php";
  if (search_param) {
    url += "?search=" + search_param;
  } else if (username_param) {
    url += "?search=by%3A" + username_param;
  }

  const response = await fetch(url);
  if (response.status !== 200) {
    console.log(response.text);
  }

  const res = await response.json();
  if (res.hasOwnProperty("error")) {
    console.log(res["error"]);
    return;
  }

  document.getElementsByClassName("scribble-card-cart")[0].innerHTML = "";

  const scribbles = res["scribbles"];

  let scc = document.getElementsByClassName("scribble-card-cart")[0];
  for (let i = scribbles.length - 1; i >= 0; i--) {
    scribble = scribbles[i];

    const newCard = document.createElement("a");
    newCard.classList.add("scribble-card");
    newCard.id = scribble.id;
    newCard.href = "scribble.php?id=" + scribble.id;
    scc.appendChild(newCard);

    const newImg = document.createElement("img");
    newImg.classList.add("scribble-card-image");
    newImg.src = scribble.data_url;
    newCard.appendChild(newImg);

    let title = scribble.title;
    if (title.length > 15) {
      title = title.substr(0, 14) + "\n" + title.substr(15);
    }

    const newTitle = document.createElement("div");
    newTitle.classList.add("scribble-card-title");
    newTitle.innerText = title;
    newCard.appendChild(newTitle);

    //TODO add author name and avatar
  }
}

populateScribbleCards();
