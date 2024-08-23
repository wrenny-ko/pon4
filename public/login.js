let av = document.getElementsByClassName('avatar')[0];
const response = await fetch("localhost:80/avatar.php");
const res = await response.json();
av.src = res['data_url'];
