class Point {
  x;
  y;

  constructor(x, y) {
    this.x = x;
    this.y = y;
  }
}

class LineData {
  points = [];
  linestrings = [];

  startRecording(pt) {
    this.points = [pt];
  }

  recordPoint(pt) {
    this.points.push(pt)
  }

  stopRecording() {
    this.linestrings.push(this.points);
    this.points = [];
  }
}

function scribble() {
  document.querySelector(".submitButton").setAttribute("disabled", "");

  let c = document.getElementsByClassName("pad")[0];
  let ctx = c.getContext("2d");

  let position = {x: 0, y: 0};
  let offset = {x: 0, y: 0};

  let drawing = false;
  let strokes = [];

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

  ctx.lineWidth = 2;
  ctx.lineCap = 'round';
  ctx.strokeStyle = '#c0392b';

  let ld = new LineData();

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

    pt = new Point(position.x, position.y);
    ld.recordPoint(pt);

    document.querySelector(".submitButton").removeAttribute("disabled");
  }

  function mouseEnter(e) {
    position = {
      x: e.clientX + offset.x,
      y: e.clientY + offset.y,
    }
  }

  function mouseDown(e) {
    drawing = true;
    position = {
      x: e.clientX + offset.x,
      y: e.clientY + offset.y,
    }
    let pt = new Point(position.x, position.y);
    ld.startRecording(pt);
  }

  function stopDrawing(e) {
    drawing = false;
    ld.stopRecording();
  }

  function clearCanvas(e) {
    ctx.clearRect(0, 0, ctx.canvas.width, ctx.canvas.height);
  }

  document.addEventListener('resize', resize);
  document.addEventListener('mousedown', mouseDown);
  document.addEventListener('mouseup', stopDrawing);
  document.addEventListener('mousemove', mouseMove);
  document.addEventListener('mouseenter', mouseEnter);

  let reset = document.getElementsByClassName("clearCanvasButton")[0];
  reset.addEventListener('click', clearCanvas);

  function disable() {
    document.querySelector(".spinner").classList.remove('hidden');
    document.querySelector(".submitButton").setAttribute("disabled", "");
    document.querySelector(".clearCanvasButton").setAttribute("disabled", "");
  }

  function enable() {
    document.querySelector(".spinner").classList.add('hidden');
    document.querySelector(".submitButton").removeAttribute("disabled");
    document.querySelector(".clearCanvasButton").removeAttribute("disabled");
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
    const t = document.getElementsByClassName("titleModal")[0];
    t.style.zIndex = 10;
  }

  function hideTitleModal() {
    const t = document.getElementsByClassName("titleModal")[0];
    t.style.zIndex = -1;
  }

  function disablePostButton() {
    const tb = document.getElementsByClassName("titleButton")[0];
    tb.setAttribute("disabled", "true");
  }

  function enablePostButton() {
    const tb = document.getElementsByClassName("titleButton")[0];
    tb.removeAttribute("disabled");
  }

  function close() {
    enable();
    hideTitleModal();
    if (isBlank()) {
      document.querySelector(".submitButton").setAttribute("disabled", "");
    }
  }

  // popup with the title input before posting
  async function submit(e) {
    disable();

    drawThumb();
    showTitleModal();
    disablePostButton();
    
    const it = document.getElementsByClassName("inputTitle")[0];
    it.addEventListener("input", (event) => {
      if (event.target.value) {
        enablePostButton();
      } else {
        disablePostButton();
      }
    });
  }

  async function post(e) {
    const t = document.getElementsByClassName("inputTitle")[0];
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
    window.location.href = '/index.php?scribble=' + id;

    return false; // this is so the normal form handler doesn't resume after
  }

  let sb = document.getElementsByClassName("submitButton")[0];
  sb.addEventListener('click', submit);

  let tb = document.getElementsByClassName("titleButton")[0];
  tb.addEventListener('click', post);

  let ctm = document.getElementsByClassName("closeTitleModal")[0];
  ctm.addEventListener('click', close);
}

scribble();
