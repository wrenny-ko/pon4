function scribble() {
  document.querySelector(".submit-button").setAttribute("disabled", "");

  let c = document.getElementsByClassName("pad")[0];
  let ctx = c.getContext("2d");

  let position = {x: 0, y: 0};
  let offset = {x: 0, y: 0};

  let drawing = false;
  let strokes = [];

  // keeps canvas aligned
  function resize(e) {
    let rect = c.getBoundingClientRect();
    offset = {
      x: -1*rect.x,
      y: -1*rect.y,
    }
    ctx.canvas.offsetLeft = rect.x;
    ctx.canvas.offsetTop = rect.y;
    ctx.canvas.width = rect.width;
    ctx.canvas.height = rect.height;
  }

  resize();

  //TODO parameterize these later for added user fun
  ctx.lineWidth = 2;
  ctx.lineCap = 'round';
  ctx.strokeStyle = '#c0392b';

  // draw on canvas if drawing flag and if mouse button is still down
  function mouseMove(e) {
    if (e.button !== 0 || !drawing) {
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

    document.querySelector(".submit-button").removeAttribute("disabled");
  }

  // set position for drawing when cursor enters the canvas
  function mouseEnter(e) {
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
    ctx.clearRect(0, 0, ctx.canvas.width, ctx.canvas.height);
    document.querySelector(".submit-button").setAttribute("disabled", "");
  }

  document.addEventListener('resize', resize);
  document.addEventListener('mousedown', mouseDown);
  document.addEventListener('mouseup', stopDrawing);
  document.addEventListener('mousemove', mouseMove);
  document.addEventListener('mouseenter', mouseEnter);

  let reset = document.getElementsByClassName("clear-canvas-button")[0];
  reset.addEventListener('click', clearCanvas);

  // show the spinner and disable the main buttons
  function disable() {
    document.querySelector(".spinner").classList.remove('hidden');
    document.querySelector(".submit-button").setAttribute("disabled", "");
    document.querySelector(".clear-canvas-button").setAttribute("disabled", "");
  }

  // hide the spinner and allow the buttons to be pressed again
  function enable() {
    document.querySelector(".spinner").classList.add('hidden');
    document.querySelector(".submit-button").removeAttribute("disabled");
    document.querySelector(".clear-canvas-button").removeAttribute("disabled");
  }

  function setErrorMsg(msg) {
    document.querySelector(".error").classList.remove('hidden');
    document.querySelector(".error").innerHTML = msg;
  }

  function drawThumb() {
    const base64URL = ctx.canvas.toDataURL();
    const t = document.getElementsByClassName("thumb")[0];
    t.src = base64URL;
  }

  function isBlank() {
    let d = ctx.getImageData(0, 0, 200, 200).data;
    return !d.some(ch => ch !== 0);
  }

  function showTitleModal() {
    const t = document.getElementsByClassName("title-modal")[0];
    t.style.zIndex = 10;
  }

  function hideTitleModal() {
    const t = document.getElementsByClassName("title-modal")[0];
    t.style.zIndex = -1;
  }

  function disablePostButton() {
    const tb = document.getElementsByClassName("title-button")[0];
    tb.setAttribute("disabled", "true");
  }

  function enablePostButton() {
    const tb = document.getElementsByClassName("title-button")[0];
    tb.removeAttribute("disabled");
  }

  function close() {
    enable();
    hideTitleModal();
    if (isBlank()) {
      document.querySelector(".submit-button").setAttribute("disabled", "");
    }
  }

  // popup with the title input before posting
  async function submit(e) {
    disable();

    drawThumb();
    showTitleModal();
    disablePostButton();
    
    const it = document.getElementsByClassName("input-title")[0];
    it.addEventListener("input", (event) => {
      if (event.target.value) {
        enablePostButton();
      } else {
        disablePostButton();
      }
    });

    document.getElementsByClassName("input-title")[0].focus();
  }

  async function post(e) {
    const t = document.getElementsByClassName("input-title")[0];
    if (t.value === "") {
      return;
    }

    hideTitleModal();

    const formData = new FormData();
    const scribble = JSON.stringify({
      title: t.value,
      data_url: ctx.canvas.toDataURL(),
    })
    formData.append("scribble", scribble)

    const response = await fetch('http://localhost:80/postScribble.php', {
      method: 'POST',
      body: formData,
    });

    const res = await response.json();
    if (response.status !== 201) {
      // show error message
      setErrorMsg("Error: " + res.error);

      // hide the spinner and allow the buttons to be pressed again
      enable();
      return false;
    }

    id = res.success
    window.location.href = '/scribble.php?id=' + id;

    return false; // this is so the normal form handler doesn't resume after
  }

  let sb = document.getElementsByClassName("submit-button")[0];
  sb.addEventListener('click', submit);

  let tb = document.getElementsByClassName("title-button")[0];
  tb.addEventListener('click', post);

  let ctm = document.getElementsByClassName("close-title-modal")[0];
  ctm.addEventListener('click', close);
}

scribble();
