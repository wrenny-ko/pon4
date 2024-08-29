<?php
// require a sortCol and sortDir
// if one not present, provide a default and redirect
if (!isset($_GET["sortCol"]) || !isset($_GET["sortDir"])) {
  $newQueryData = array();
  $newQueryData["sortCol"] = isset($_GET["sortCol"]) ? $_GET["sortCol"] : "total_scribbles";
  $newQueryData["sortDir"] = isset($_GET["sortDir"]) ? $_GET["sortDir"] : "up";

  $query = http_build_query($newQueryData);
  header("location: leaderboard.php?" . $query);
}

session_start();
$username = "anonymous";
if (isset($_SESSION['username'])) {
  $username = $_SESSION['username'];
}

require_once("../include/Perms.php");
require_once("../include/Leaderboard.php");

$perms = new Perms($username);


$sortCol = LeaderboardColumn::TotalScribbles;
try {
  $sortCol = LeaderboardColumn::from($_GET["sortCol"]);
} catch (Exception $e) {
}

$sortDir = LeaderboardSortDir::Down;
if ($_GET["sortDir"] === "up") {
  $sortDir = LeaderboardSortDir::Up;
}

$leaderboard = new Leaderboard($sortCol, $sortDir);
//$leaderboard = new Leaderboard(LeaderboardColumn::AvatarUse, LeaderboardSortDir::Up);
?>

<?php require_once("../include/common/header.php"); ?>
  <link rel="stylesheet" href="css/leaderboard.css" type="text/css">
  <script src="../js/jquery-3.7.1.min.js"></script>
</head>

<body>
  <?php require_once("../include/common/navbar.php"); ?>
  <div class="bg">
    <div class="leaderboard-container">
      <div class="leaderboard-title">
        Leaderboard
      </div>
      <?php if ($perms->hasAdmin() || $perms->hasMod()) { ?>
        <div class="leaderboard-tools">
          <?php if ($perms->hasAdmin()) {
            if (isset($_GET["showRowNumField"])) { ?>
              <form class="set-max-rows-form">
                <input class="set-max-rows-input" type="text" placeholder="<?= $leaderboard->getNumRows();?>"/>
                <button class="set-max-rows-button"></button>
              </form>
          <?php } 
            } else { ?>
              <a class="show-form href-button" href="<?= $_SERVER['REQUEST_URI'] . "&showRowNumField=true";?>">
                Set Max Rows
              </a>
            <?php } ?>
        </div>
      <?php } ?>
      <div class="leaderboard-table">
        <div class="leaderboard-header-row">
          <?php if (!$leaderboard->columnHidden(LeaderboardColumn::Username)) { ?>
            <div class="leaderboard-header-col">
              <a class="leaderboard-sort" id="sort-by-username" href="leaderboard.php?sortCol=username&sortDir=<?= $_GET["sortDir"]?>">
                Username
              </a>
            </div>
          <?php } ?>
          <?php if (!$leaderboard->columnHidden(LeaderboardColumn::TotalScribbles)) { ?>
            <div class="leaderboard-header-col">
              <a class="leaderboard-sort" id="sort-by-total-scribbles" href="leaderboard.php?sortCol=total_scribbles&sortDir=<?= $_GET["sortDir"]?>">
                Total Scribbles
              </a>
            </div>
          <?php } ?>
          <?php if (!$leaderboard->columnHidden(LeaderboardColumn::AvatarUse)) { ?>
            <div class="leaderboard-header-col">
              <a class="leaderboard-sort" id="sort-by-avatar-use" href="leaderboard.php?sortCol=avatar_use&sortDir=<?= $_GET["sortDir"]?>">
                Avatar Use
              </a>
            </div>
          <?php } ?>
          <?php if (!$leaderboard->columnHidden(LeaderboardColumn::Likes)) { ?>
            <div class="leaderboard-header-col">
              <a class="leaderboard-sort" id="sort-by-likes" href="leaderboard.php?sortCol=likes&sortDir=<?= $_GET["sortDir"]?>">
                Likes
              </a>
            </div>
          <?php } ?>
          <?php if (!$leaderboard->columnHidden(LeaderboardColumn::Dislikes)) { ?>
            <div class="leaderboard-header-col">
              <a class="leaderboard-sort" id="sort-by-dislikes" href="leaderboard.php?sortCol=dislikes&sortDir=<?= $_GET["sortDir"]?>">
                Dislikes
              </a>
            </div>
          <?php } ?>
          <?php if (!$leaderboard->columnHidden(LeaderboardColumn::LikeRatio)) { ?>
            <div class="leaderboard-header-col">
              <a class="leaderboard-sort" id="sort-by-like-ratio" href="leaderboard.php?sortCol=like_ratio&sortDir=<?= $_GET["sortDir"]?>">Like Ratio</a>
            
            </div>
          <?php } ?>
        </div>
        <?php foreach ($leaderboard->getBoard() as $row) { ?>
          <div class="leaderboard-row">
            <?php if (!$leaderboard->columnHidden(LeaderboardColumn::Username)) { ?>
              <div class="leaderboard-col">
                <a class="leaderboard-user" href="user.php?username=<?= $row["username"];?>">
                  <img class="leaderboard-avatar" id="avatar-<?= $row["username"];?>" icon/>
                  <?= $row["username"];?>
                </a>
              </div>
            <?php } ?>
            <?php if (!$leaderboard->columnHidden(LeaderboardColumn::TotalScribbles)) { ?>
              <div class="leaderboard-col">
                <?= $row["total_scribbles"];?>
              </div>
            <?php } ?>
            <?php if (!$leaderboard->columnHidden(LeaderboardColumn::AvatarUse)) { ?>
              <div class="leaderboard-col">
                <?= $row["avatar_use"];?>
              </div>
            <?php } ?>
            <?php if (!$leaderboard->columnHidden(LeaderboardColumn::Likes)) { ?>
              <div class="leaderboard-col">
                <?= $row["likes"];?>
              </div>
            <?php } ?>
            <?php if (!$leaderboard->columnHidden(LeaderboardColumn::Dislikes)) { ?>
              <div class="leaderboard-col">
                <?= $row["dislikes"];?>
              </div>
            <?php } ?>
            <?php if (!$leaderboard->columnHidden(LeaderboardColumn::LikeRatio)) { ?>
              <div class="leaderboard-col">
                <?= $row["like_ratio"];?>
              </div>
            <?php } ?>
          </div>
        <?php } ?>
      </div>
    </div>
  <script src="js/fetchAvatar.js"></script>
  <script src="js/fetchLeaderboardAvatars.js"></script>
  <script>
    const sp = new URLSearchParams(window.location.search);
    const toSelect = '#sort-by-' + sp.get('sortCol').replace('_', '-');
    const selected = $(toSelect)[0];
    selected.classList.add('selected');
    
    const dirButton = document.createElement('a');
    dirButton.classList.add('sort-dir');
    let dir = '';
    if (sp.get('sortDir') === 'down') {
      dir = 'up';
      dirButton.innerText = '^';
    } else {
      dir = 'down';
      dirButton.innerText = 'v';
    }
    dirButton.href = 'leaderboard.php?sortCol=' + sp.get('sortCol') + '&sortDir=' + dir;
    selected.appendChild(dirButton);
  </script>
</body>
