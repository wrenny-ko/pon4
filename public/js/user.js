export class User {
  static pageName = 'user';
  static title = 'User page';

  async setup() {
    this.displayUserSpecific();
    this.runSearch();
  }

  async again() {
    this.runSearch();
  }

  teardown() {
    return;
  }

  displayUserSpecific() {
    if ($('#nav-to-user').length) {
      $('.logout-form')[0].classList.remove('hidden');
      $('.create-prompt')[0].classList.remove('hidden');
    } else {
      $('.logout-form')[0].classList.add('hidden');
      $('.create-prompt')[0].classList.add('hidden');
    }
  }

  async runSearch() {
    const sp = new URLSearchParams(window.location.search);
    const username_param = sp.get('username');

    $('#user-scribbles-title')[0].innerText = "Scribbles by " + username_param;

    let url = 'api/scribble.php?action=search';
    if (username_param) {
      url += '&search=by%3A' + username_param;
    }

    const cart = $('#user-cart')[0];

    const response = await axios.get(url)
      .catch((error) => {
        console.log(error);
        cart.innerHTML = error.toJSON();
    });

    if (!response.data.hasOwnProperty('scribbles') || response.data.scribbles.length === 0) {
      cart.innerHTML = 'No scribbles found by that query...';
      return;
    }

    cart.innerHTML = '';
    const scribbles = response.data.scribbles;

    for (let i = scribbles.length - 1; i >= 0; i--) {
      let scribble = scribbles[i];

      const newCard = document.createElement('a');
      newCard.classList.add('scribble-card');
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

      //TODO add author name and avatar
    }
  }
}
