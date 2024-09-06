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

    document.addEventListener('metasync', e => {
      this.syncScribble();
    });
  }

  teardown() {
    //document.removeEventListener('metasync', metasyncHandler);
    $('.scribble-image')[0].src = '';
    $('.scribble-author')[0].innerHTML = '';
    $('.scribble-title')[0].innerText = '';
    $('.likes')[0].innerText = '';
    $('.dislikes')[0].innerText = '';
    $('.ratio')[0].innerText = '';
  }

  async populateScribble() {
    const sp = new URLSearchParams(window.location.search);
    const id = sp.get('id');

    if (id === '') {
      history.pushState( {} , 'Pon 4', '/scribble?id=1' + res.data.id);
      id = 1;
    }

    const response = await axios.get(
      'api/scribble.php?action=get&id=' + id
    ).catch(error => {
      console.log(error.response);  //TODO show error on page
    });

    const scribble = response.data.scribble;

    const si = $('.scribble-image')[0];
    si.src = scribble.data_url;

    const sa = $('.scribble-author')[0];
    sa.innerHTML = scribble.username;
    sa.href = "user?username=" + scribble.username;//TODO SPA this

    const st = $('.scribble-title')[0];

    // crude text wrap
    let title = scribble.title;
    if (title.length > 15) {
      title = title.substr(0, 14) + "\n" + title.substr(15);
    }
    st.innerText = title;

    this.syncScribble();
  }

  async syncScribble() {
    const sp = new URLSearchParams(window.location.search);
    const id = sp.get('id');

    if (id === '') {
      history.pushState( {} , 'Pon 4', '/scribble?id=1' + res.data.id);
      id = 1;
    }

    const response = await axios.get(
      'api/scribble.php?action=get_metadata&id=' + id
    ).catch(error => {
      console.log(error.response);  //TODO show error on page
    });

    const meta = response.data.metadata;

    const likes = $('.likes')[0];
    likes.innerText = meta.likes + " Likes";
    if (meta.user_data.liked) {
      likes.classList.add('liked');
    } else {
      likes.classList.remove('liked');
    }

    const dislikes = $('.dislikes')[0];
    dislikes.innerText = meta.dislikes + " Dislikes";
    if (meta.user_data.disliked) {
      dislikes.classList.add('disliked');
    } else {
      dislikes.classList.remove('disliked');
    }

    const ratio = +meta.likes - (+meta.dislikes);
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

    const response = await axios('api/account.php?action=set_avatar&id=' + id,
      {
        method: 'PUT'
      }).then( res => {
        $('.avatar')[0].src = res.data.scribble.data_url;
      })
      .catch( error => {
        console.log(error.response); //TODO show error on page
      });
  }

  async deleteScribble() {
    const sp = new URLSearchParams(window.location.search);
    const id = sp.get('id');

    if (id === '') {
      return;
    }

    const response = await axios.delete("api/scribble.php?action=delete&id=" + id)
      .catch( error => {
        console.log(error.response); //TODO show error on page
      }
    );

    history.pushState( {} , 'Pon 4', '/index');
    let ev = document.createEvent("HTMLEvents");
    ev.initEvent("approute", true, true);
    ev.eventName = "approute";
    document.dispatchEvent(ev);
  }

  async like() {
    const sp = new URLSearchParams(window.location.search);
    const id = sp.get('id');

    if (id === '') {
      return;
    }

    await axios.put("api/scribble.php?action=like&id=" + id)
      .catch( error => {
        console.log(error.response); //TODO show error on page
    });

    let ev = document.createEvent("HTMLEvents");
    ev.initEvent("metasync", true, true);
    ev.eventName = "metasync";
    document.dispatchEvent(ev);

  }

  async dislike() {
    const sp = new URLSearchParams(window.location.search);
    const id = sp.get('id');

    if (id === '') {
      return;
    }

    const response = await axios.put("api/scribble.php?action=dislike&id=" + id)
      .catch( error => {
        console.log(error.response); //TODO show error on page
      }
    );

    let ev = document.createEvent("HTMLEvents");
    ev.initEvent("metasync", true, true);
    ev.eventName = "metasync";
    document.dispatchEvent(ev);
  }
}
