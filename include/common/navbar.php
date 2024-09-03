<?php
  $search = "";
  if (isset($_GET["search"])) {
    $search = $_GET["search"];
  }

  $sc = new Scribble();
  $sc->readScribbleAvatar($username); // $username defined in include/common/includes.php
  $avatar = $sc->getScribble();
?>
<div class="navbar">
  <div class="nav-left">
    <div class="logo">
      <a href="/">
        <img src="favicon.ico" class="icon nav-entry" alt="Pon4 logo"/>
        <span class="tooltip-text">Pon4!!!</span>
      </a>
    </div>
    <div class="new-post">
      <a href="/new.php">
        <img src="newpost.png" class="icon nav-entry" alt="newpost"/>
        <span class="tooltip-text">Create a scribble</span>
      </a>
    </div>
    <div class="leaderboard-nav">
      <a href="/leaderboard.php">
        <img src="cup.png" class="icon nav-entry" alt="leaderboard"/>
        <span class="tooltip-text">Leaderboard</span>
      </a>
    </div>
    <?php if ($perms->hasTech() || $perms->hasAdmin()) { ?>
      <div class="log-nav">
        <a href="/log.php">
          <img src="scroll.png" class="icon nav-entry" alt="log"/>
          <span class="tooltip-text">API logs</span>
        </a>
      </div>
    <?php } ?>
    <div class="searchbar">
      <form class="search">
        <input type="image" src="search.png" name="searchButton" class="icon nav-entry search-icon" onclick="return search()"/>
        <input type="text" name="searchText" class="search-text" placeholder="search titles, or try by:<username>" value="<?=$search;?>"/>
      </form>
    </div>
  </div>
  <div class="account nav-entry">
    <a href="<?= ($username !== "anonymous") ? "user.php" : "/login.php";?>" class="account">
      <img class="avatar" src="<?= $avatar["data_url"];?>" icon/>
      <?= ($username !== "anonymous") ? $username : "Login";?>
    </a>
  </div>
  <script>
    function search() {
      event.preventDefault();
      let st = document.getElementsByClassName('search-text')[0];
      let url = 'index.php?search=' + encodeURIComponent(st.value);
      window.location.href = url;
      false;
    }
  </script>
</div>
