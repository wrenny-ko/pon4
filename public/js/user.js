export class User {
  static pageName = 'user';
  static title = 'User page';

  username;

  async setup() {
    this.username = '';
    this.userSpecificSetup();
    this.runSearch();
  }

  async again() {
    this.userSpecificTeardown();
    this.username = '';
    this.userSpecificSetup();
    this.runSearch();
  }

  teardown() {
    this.userSpecificTeardown();
    this.username = '';
  }

  userSpecificSetup() {
    this.username = "anonymous";

    const sp = new URLSearchParams(window.location.search);
    const usernameParam = sp.get('username');

    const navto = $('#nav-to-user');

    if (usernameParam) {
      this.username = usernameParam;
    } else {
      if (navto.length) {
        this.username = navto[0].innerText;
      }
    }

    if (navto.length && navto[0].innerText === this.username) {
      $('.logout-form')[0].classList.remove('hidden');
      $('.create-prompt')[0].classList.remove('hidden');
      $('.logout-button')[0].addEventListener('click', this.postLogout);
    } else {
      $('.logout-form')[0].classList.add('hidden');
      $('.create-prompt')[0].classList.add('hidden'); // TODO add to 
    }
  }

  userSpecificTeardown() {
    const navto = $('#nav-to-user');
    if (navto.length && navto[0].innerText === this.username) {
      $('.logout-form')[0].classList.add('hidden');
      $('.create-prompt')[0].classList.add('hidden');
      $('.logout-button')[0].removeEventListener('click', this.postLogout);
    }
  }

  async postLogout() {
    axios.post('api/account.php?action=logout')
      .then( res => {
        window.location.href = '/index'; // force page reload
      })
      .catch( err => {
        console.log(err.response);
      });
  }

  async runSearch() {
    const cartAlt = $('.empty-cart-alt')[0];
    cartAlt.classList.remove('hidden');

    const cart = $('#user-cart')[0];
    cart.classList.add('hidden');

    $('#user-scribbles-title')[0].innerText = "Scribbles by " + this.username;

    let url = 'api/scribble.php?action=search&search=by%3A' + this.username;
    axios.get(url)
      .then( response => {
        if (!response.data.hasOwnProperty('scribbles') || response.data.scribbles.length === 0) {
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

          cartAlt.classList.add('hidden');
          cart.classList.remove('hidden');
        }
      })
      .catch((error) => {
        cart.innerHTML = error.response.data.error;
        cart.style.color = 'red';
        cart.classList.remove('hidden');
      });
  }
}
