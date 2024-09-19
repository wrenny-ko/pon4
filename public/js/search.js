export class Search {
  static pageName = 'index';
  static title = "Search scribbles";

  async setup() {
    this.runSearch();
  }

  async again() {
    this.runSearch();
  }

  teardown() {
    return;
  }

  async runSearch() {
    const sp = new URLSearchParams(window.location.search);
    const search_param = sp.get('search');
    const username_param = sp.get('username');

    let url = 'api/scribble.php?action=search';
    if (search_param) {
      url += '&search=' + search_param;
    } else if (username_param) {
      url += '&search=by%3A' + username_param;
    }

    const cart = $('.scribble-card-cart')[0];

    await axios.get(url)
      .then( response => {
        if (!response.data.hasOwnProperty('scribbles') || response.data.scribbles.length === 0) {
          cart.innerHTML = 'No scribbles found by that query...';
          return;
        }

        cart.innerHTML = '';
        let scribbles = Object.entries(response.data.scribbles);

        for (let i = scribbles.length - 1; i >= 0; i--) {
          const scribble = scribbles[i][1];

          const newCard = document.createElement('a');
          newCard.classList.add('scribble-card');
          newCard.classList.add('site-nav');
          newCard.id = scribble.id;
          newCard.href = 'scribble?id=' + scribble.id;
          cart.appendChild(newCard);

          const newImg = document.createElement('img');
          newImg.classList.add('scribble-card-image');
          newImg.src = scribble.data_url;
          newCard.appendChild(newImg);

          let title = scribble.title;
          if (title.length > 15) {
            title = title.substr(0, 14) + '\n' + title.substr(15);
          }

          const newTitle = document.createElement('div');
          newTitle.classList.add('scribble-card-title');
          newTitle.innerText = title;
          newCard.appendChild(newTitle);

          newCard.setAttribute('data', JSON.stringify(scribble));
          //TODO display author name and avatar, likes/dislikes
        }
      })
      .catch((error) => {
        cart.innerHTML = error.response.data.error;
        cart.style.color = 'red';
      });
  }
}
