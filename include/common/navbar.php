<?php
  $search = "";
  if (isset($_GET["search"])) {
    $search = $_GET["search"];
  }
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
      </a>
    </div>
    <div class="leaderboard-nav">
      <a href="/leaderboard.php">
        <img src="cup.png" class="icon nav-entry" alt="leaderboard"/>
      </a>
    </div>
    <div class="searchbar">
      <form class="search">
        <input type="image" src="search.png" name="searchButton" class="icon nav-entry search-icon" onclick="return search()"/>
        <input type="text" name="searchText" class="search-text" placeholder="search titles, or try by:<username>" value="<?=$search;?>"/>
      </form>
    </div>
  </div>
  <div class="account nav-entry">
    <?php if (session_status() === PHP_SESSION_ACTIVE and isset($_SESSION['username'])) { ?>
      <a href="/user.php" class="account">
        <img class="avatar" icon/>
        <?= $_SESSION['username'];?>
      </a>
    <?php } else { ?>
      <a href="/login.php" class="account">
        <img class="avatar" icon/>
        Login
      </a>
    <?php } ?>
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
