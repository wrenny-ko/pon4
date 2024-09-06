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
      <a href="/index" id="nav-to-index" class="site-nav">
        <img src="favicon.ico" class="icon nav-entry" alt="Pon4 logo"/>
        <span class="tooltip-text">Pon4!!!</span>
      </a>
    </div>
    <div class="new-post">
      <a href="/new" id="nav-to-new" class="site-nav">
        <img src="newpost.png" class="icon nav-entry" alt="newpost"/>
        <span class="tooltip-text">Create a scribble</span>
      </a>
    </div>
    <div class="leaderboard-nav">
      <a href="/leaderboard" id="nav-to-leaderboard" class="site-nav">
        <img src="cup.png" class="icon nav-entry" alt="leaderboard"/>
        <span class="tooltip-text">Leaderboard</span>
      </a>
    </div>
    <?php if ($perms->hasTech() || $perms->hasAdmin()) { ?>
      <div class="log-nav">
        <a href="/log" id="nav-to-log" class="site-nav">
          <img src="scroll.png" class="icon nav-entry" alt="log"/>
          <span class="tooltip-text">API logs</span>
        </a>
      </div>
    <?php } ?>
    <div class="searchbar">
      <form class="search">
        <input type="image" src="search.png" name="searchButton" id="search-button" class="icon nav-entry search-icon"/>
        <input type="text" name="searchText" class="search-text" placeholder="search titles, or try by:<username>" value="<?=$search;?>"/>
      </form>
    </div>
  </div>
  <div class="nav-right">
    <?php require_once $_SERVER["DOCUMENT_ROOT"] . "/../include/common/holidays.php"; ?>
    <div class="account nav-entry">
      <a href="<?= ($username !== "anonymous") ? "/user?username=" . $username : "/login";?>" id="<?= ($username !== "anonymous") ? "nav-to-user" : "nav-to-login";?>" class="account site-nav">
        <img class="avatar" src="<?= $avatar["data_url"];?>" icon/>
        <?= ($username !== "anonymous") ? $username : "Login";?>
      </a>
    </div>
  </div>
</div>
