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
    <div class="searchbar">
      <form class="search">
        <input type="image" src="search.png" name="searchButton" class="icon nav-entry search-icon" onclick="return search()"/>
        <input type="text" name="searchText" class="search-text" placeholder="search titles, or try by:<username>"/>
      </form>
    </div>
  </div>
  <div class="account nav-entry">
    <?php
      if (session_status() === PHP_SESSION_ACTIVE and isset($_SESSION['username'])) {
        echo "";
        $username = $_SESSION['username'];
        echo "<a href=\"/user.php\" class=\"account\">";
        echo "<img class=\"avatar\" icon/>";
        echo "$username";
        echo "</a>";
      } else {
    ?>
      <a href="/login.php" class="account">
        <img class="avatar" icon/>
        Login
      </a>
    <?php
      }
    ?>
  </div>
  <script>
    function search() {
      event.preventDefault();
      let st = document.getElementsByClassName('search-text')[0];
      query = st.value;
      url = 'index.php?search=' + encodeURIComponent(query);
      window.location.href = url;
      false;
    }
  </script>
</div>
