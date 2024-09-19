export class Draw {
  static pageName = 'index';
  static title = "Search scribbles";
  once = false;

  async setup() {
    $('.submit-button')[0].setAttribute('disabled', '');

    $('.input-title')[0].addEventListener('input', Draw.titleInputEventHandler);
    $('.title-modal')[0].addEventListener('keyup', Draw.titleSubmitKeyHandler);

    // beta test, backend requires auth
    if ($('#role-beta').length) {
      $('.import-button')[0].setAttribute('disabled', '');
      $('.input-import-id')[0].value = '';

      $('.import-button')[0].addEventListener('click', this.importBase);
      $('.input-import-id')[0].addEventListener('keyup', this.validateID);

      const imer = $('.import-error')[0];
      imer.classList.add('hidden');
      imer.innerText = '';
    }

    this.scribble();
  }

  async again() {
    this.teardown();
    this.setup();
  }

  teardown() {
    $('.input-title')[0].removeEventListener('input', Draw.titleInputEventHandler);
    $('.title-modal')[0].removeEventListener('keyup', Draw.titleSubmitKeyHandler);

    // beta test, backend requires auth
    if ($('#role-beta').length) {
      $('.import-button')[0].removeEventListener('click', this.importBase);
      $('.input-import-id')[0].removeEventListener('keyup', this.validateID);
    }

    let ctm = $('.close-title-modal')[0];
    let ev = document.createEvent("HTMLEvents");
    ev.initEvent("click", true, true);
    ev.eventName = "click";
    ctm.dispatchEvent(ev);
    return;
  }

  static titleInputEventHandler(e) {
    if (e.target.value) {
      Draw.enablePostButton();
    } else {
      Draw.disablePostButton();
    }
  }

  static titleSubmitKeyHandler(e) {
    if (e.keyCode === 13 && !$('.title-button').disabled) {
      Draw.post(e);
    }
  }

  scribble() {
    const c = $('.pad')[0];
    const ctx = c.getContext('2d');

    let position = {x: 0, y: 0};
    let offset = {x: 0, y: 0};

    let drawing = false;
    let entered = false;
    let strokes = [];

    // keeps canvas aligned
    function resize(e) {
      let rect = c.getBoundingClientRect();
      offset = {
        x: -1*rect.x,
        y: -1*rect.y,
      }
      ctx.canvas.setAttribute('offsetLeft', rect.x);
      ctx.canvas.setAttribute('offsetTop', rect.y);
      ctx.canvas.setAttribute('width', rect.width);
      ctx.canvas.setAttribute('height', rect.height);
    }

    resize();

    //TODO parameterize these later for added user fun
    ctx.lineWidth = 2;
    ctx.lineCap = 'round';
    ctx.strokeStyle = '#c0392b';

    // draw on canvas if drawing flag and if mouse button is still down
    function mouseMove(e) {
      if (e.button !== 0 || !drawing || !entered) {
        return;
      }

      ctx.beginPath();
      ctx.moveTo(position.x, position.y);
      position = {
        x: e.clientX + offset.x,
        y: e.clientY + offset.y,
      }

      ctx.lineTo(position.x, position.y);
      ctx.stroke();

      $('.submit-button')[0].removeAttribute('disabled');
    }

    // set position for drawing when cursor enters the canvas
    function mouseEnter(e) {
      entered = true;
      position = {
        x: e.clientX + offset.x,
        y: e.clientY + offset.y,
      }
    }

    // set position for drawing when cursor enters the canvas
    function mouseLeave(e) {
      entered = false;
      position = {
        x: e.clientX + offset.x,
        y: e.clientY + offset.y,
      }
    }

    // set position for drawing and set the drawing flag
    function mouseDown(e) {
      drawing = true;
      position = {
        x: e.clientX + offset.x,
        y: e.clientY + offset.y,
      }
    }

    function stopDrawing(e) {
      drawing = false;
    }


  function clearCanvas(e) {
    const c = $('.pad')[0];
    const ctx = c.getContext('2d');
    ctx.clearRect(0, 0, ctx.canvas.width, ctx.canvas.height);
    $('.submit-button')[0].setAttribute('disabled', '');
  }

    document.addEventListener('resize', resize);
    document.addEventListener('mousedown', mouseDown);
    document.addEventListener('mouseup', stopDrawing);
    document.addEventListener('mousemove', mouseMove);
    c.addEventListener('mouseenter', mouseEnter);
    c.addEventListener('mouseleave', mouseLeave);

    let reset = $('.clear-canvas-button')[0];
    reset.addEventListener('click', clearCanvas);

    // show the spinner and disable the main buttons
    function disable() {
      $('.spinner')[0].classList.remove('hidden');
      $('.submit-button')[0].setAttribute('disabled', '');
      $('.clear-canvas-button')[0].setAttribute('disabled', '');
    }

    // hide the spinner and allow the buttons to be pressed again
    function enable() {
      $('.spinner')[0].classList.add('hidden');
      if (!isBlank()) {
        $('.submit-button')[0].removeAttribute('disabled');
      }
      $('.clear-canvas-button')[0].removeAttribute('disabled');
    }

    function setErrorMsg(msg) {
      const errorEl = $('.error')[0];
      errorEl.classList.remove('hidden');
      errorEl.innerHTML = msg;
    }

    function drawThumb() {
      const base64URL = ctx.canvas.toDataURL();
      const t = $('.thumb')[0];
      t.src = base64URL;
    }

    function isBlank() {
      let d = ctx.getImageData(0, 0, 200, 200).data;
      return !d.some(ch => ch !== 0);
    }

    function close() {
      enable();
      Draw.clearTitleInput();
      Draw.hideTitleModal();
      if (isBlank()) {
        $('.submit-button')[0].setAttribute('disabled', '');
      }
    }

    // popup with the title input before posting
    async function submit(e) {
      disable();

      drawThumb();
      Draw.showTitleModal();
      Draw.disablePostButton();
    }

    if (!this.once) {
      this.once = true;

      const sb = $('.submit-button')[0];
      sb.addEventListener('click', submit);

      const tb = $('.title-button')[0];
      tb.addEventListener('click', Draw.post);

      const ctm = $('.close-title-modal')[0];
      ctm.addEventListener('click', close);
    }
  }

  validateID() {
    const button = $('.import-button')[0];

    const id = $('.input-import-id')[0].value;
    // test if positive integer
    if ( /^[+]?\d+$/.test(id) ) {
      button.removeAttribute('disabled')
    } else {
      button.setAttribute('disabled', '');
    }
  }

  importBase(e) {
    e.preventDefault();

    const imer = $('.import-error')[0];
    imer.classList.add('hidden');
    imer.innerText = '';

    const id = $('.input-import-id')[0].value;
    axios.get('api/scribble.php?action=import&id=' + id)
      .then( res => {
        //const durl = 'url("' + res.data.data_url + '")';
        //$('.pad')[0].style.setProperty('background-image', durl);

        const ctx = $('.pad')[0].getContext('2d');
        const img = new Image;
        img.onload = function(){
          ctx.drawImage(img, 0, 0);
        };
        img.src = res.data.data_url;
      })
      .catch( err => {
        const imer = $('.import-error')[0];
        imer.classList.remove('hidden');
        imer.innerText = err.response.data.error;
      });

    return false;
  }

  static showTitleModal() {
    $('.title-modal')[0].classList.remove('hidden');
  }

  static hideTitleModal() {
    $('.title-modal')[0].classList.add('hidden');
  }

  static clearTitleInput() {
    const t = $('.input-title')[0];
    t.value = '';
  }

  static disablePostButton() {
    const tb = $('.title-button')[0];
    tb.setAttribute('disabled', 'true');
  }

  static enablePostButton() {
    const tb = $('.title-button')[0];
    tb.removeAttribute('disabled');
  }

  static async post(e) {
    e.preventDefault();

    const t = $('.input-title')[0];
    if (t.value === '') {
      return;
    }

    Draw.hideTitleModal();

    const c = $('.pad')[0];
    const ctx = c.getContext('2d');

    const formData = new FormData();
    const scribble = JSON.stringify({
      title: t.value,
      data_url: ctx.canvas.toDataURL(),
    })
    formData.append('scribble', scribble)

    await axios.post('api/scribble.php?action=upload', formData, {
      headers: {
        'content-type': 'multipart/form-data'
      }
    })
    .then( res => {
      $('.scribble-data')[0].setAttribute('data', JSON.stringify(res.data.scribble));
      // navigate to the new scribble's page
      history.pushState( {} , 'Pon 4', '/scribble?id=' + res.data.scribble.id);
      let ev = document.createEvent("HTMLEvents");
      ev.initEvent("approute", true, true);
      ev.eventName = "approute";
      e.srcElement.dispatchEvent(ev);
    }).catch( err => {
      setErrorMsg('Error: ' + err.response.data.error); // show error message
      enable(); // hide the spinner and allow the buttons to be pressed again
    });

    return false;
  }
}
