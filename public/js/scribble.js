export class Scribble {
  static name = 'scribble';
  static title = 'Scribble';

  setup() {
    this.populateScribble();

    const lb = $('.like-button');
    if (lb.length > 0) {
      lb[0].addEventListener('click', this.like);
    }

    const db = $('.dislike-button');
    if (db.length > 0) {
      db[0].addEventListener('click', this.dislike);
    }

    const sp = new URLSearchParams(window.location.search);
    const id = sp.get('id');
    const delb = $('.delete-button');
    if (delb.length > 0) {
      if (+id === 1) {
        delb[0].setAttribute('disabled', '');
      } else {
        delb[0].removeAttribute('disabled');
        delb[0].addEventListener('click', this.deleteScribble);
      }
    }

    const setAv = $('.set-avatar-button');
    if (setAv.length > 0) {
      setAv[0].addEventListener('click', this.setAvatar);
    }
  }

  again() {
    this.teardown();
    this.setup();
  }

  teardown() {
    $('.scribble-image')[0].src = '';
    $('.scribble-author')[0].innerHTML = '';
    $('.scribble-title')[0].innerText = '';
    $('.likes')[0].innerText = '';
    $('.dislikes')[0].innerText = '';
    $('.ratio')[0].innerText = '';
    $('.scribble-data')[0].setAttribute('data', '');
  }

  renderScribble(scribble) {
    $('.scribble-image')[0].src = scribble.data_url;

    const sa = $('.scribble-author')[0];
    sa.innerHTML = scribble.username;
    sa.href = "user?username=" + scribble.username;

    // crude text wrap
    let title = scribble.title;
    if (title.length > 15) {
      title = title.substr(0, 14) + "\n" + title.substr(15);
    }
    $('.scribble-title')[0].innerText = title;

    const likes = $('.likes')[0];
    likes.innerText = scribble.likes + " Likes";
    if (scribble.user_data.liked) {
      likes.classList.add('liked');
    } else {
      likes.classList.remove('liked');
    }

    const dislikes = $('.dislikes')[0];
    dislikes.innerText = scribble.dislikes + " Dislikes";
    if (scribble.user_data.disliked) {
      dislikes.classList.add('disliked');
    } else {
      dislikes.classList.remove('disliked');
    }

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

  async populateScribble() {
    const dataString = $('.scribble-data')[0].getAttribute('data');
    if (dataString !== '') {
      const scribble = JSON.parse(dataString);
      this.renderScribble(scribble);
      Scribble.syncScribble(scribble);
    } else {
      const sp = new URLSearchParams(window.location.search);
      let id = sp.get('id');

      if (!id) {
        history.pushState( {} , 'Pon 4', '/scribble?id=1');
        id = 1;
      }

      const response = await axios.get('api/scribble.php?action=get&id=' + id)
        .catch( error => {
          console.log(error.response);
        });

      const scribble = response.data.scribble;
      this.renderScribble(scribble);
      Scribble.syncScribble(scribble);
    }
  }

  static syncScribble(metadata) {
    const likes = $('.likes')[0];
    likes.innerText = metadata.likes + " Likes";
    if (metadata.user_data.liked) {
      likes.classList.add('liked');
    } else {
      likes.classList.remove('liked');
    }

    const dislikes = $('.dislikes')[0];
    dislikes.innerText = metadata.dislikes + " Dislikes";
    if (metadata.user_data.disliked) {
      dislikes.classList.add('disliked');
    } else {
      dislikes.classList.remove('disliked');
    }

    const ratio = +metadata.likes - (+metadata.dislikes);
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

  async setAvatar() {
    const sp = new URLSearchParams(window.location.search);
    const id = sp.get('id');

    if (id === '') {
      return;
    }

    axios('api/account.php?action=set_avatar&id=' + id,
      {
        method: 'PUT'
      }).then( res => {
        $('.avatar')[0].src = res.data.scribble.data_url;
      })
      .catch( error => {
        console.log(error.response);
      });
  }

  async deleteScribble() {
    const sp = new URLSearchParams(window.location.search);
    const id = sp.get('id');

    if (id === '') {
      return;
    }

    axios.delete("api/scribble.php?action=delete&id=" + id)
      .then( reponse => {
        history.pushState( {} , 'Pon 4', '/index');
        let ev = document.createEvent("HTMLEvents");
        ev.initEvent("approute", true, true);
        ev.eventName = "approute";
        document.dispatchEvent(ev);
      })
      .catch( error => {
        console.log(error.response);
      }
    );
  }

  async like() {
    const sp = new URLSearchParams(window.location.search);
    const id = sp.get('id');

    if (id === '') {
      return;
    }

    const response = await axios.put("api/scribble.php?action=like&id=" + id)
      .catch( error => {
        console.log(error.response);
    });

    Scribble.syncScribble(response.data.metadata);
  }

  async dislike() {
    const sp = new URLSearchParams(window.location.search);
    const id = sp.get('id');

    if (id === '') {
      return;
    }

    const response = await axios.put("api/scribble.php?action=dislike&id=" + id)
      .catch( error => {
        console.log(error.response);
      }
    );

    Scribble.syncScribble(response.data.metadata);
  }
}
