<?php
  require_once "../include/common/includes.php";
  require_once "../include/Leaderboard.php";

  $leaderboard = new Leaderboard();
?>
<?php require_once("../include/common/header.php"); ?>
  <link rel="stylesheet" href="css/leaderboard.css" type="text/css">
  <script src="js/jquery-3.7.1.min.js"></script>
  <link href="https://cdn.datatables.net/v/dt/dt-2.1.5/datatables.min.css" rel="stylesheet">
  <script src="https://cdn.datatables.net/v/dt/dt-2.1.5/datatables.min.js"></script>
</head>
<body>
  <?php require_once("../include/common/navbar.php"); ?>
  <div class="bg">
    <div class="leaderboard-container">
      <div class="leaderboard-title">
        Leaderboard
      </div>
      <?php if ($perms->hasAdmin() || $perms->hasModerator()) { ?>
        <div class="leaderboard-tools">
          <?php if ($perms->hasAdmin()) {
            if (isset($_GET["showRowNumField"])) { ?>
              <form class="set-max-rows-form">
                Set Max Rows
                <input class="set-max-rows-input" type="text" name="max-rows" placeholder="<?= $leaderboard->getMaxRows();?>"/>
                <button class="set-max-rows-button" onclick="handleSetMaxRows();">Update</button>
              </form>
            <?php } else { ?>
              <a class="show-form href-button" href="<?= $_SERVER['REQUEST_URI'] . "?showRowNumField=true";?>">
                Set Max Rows
              </a>
            <?php } ?>
          <?php } ?>
        </div>
      <?php } ?>
      <div class="table-container">
        <table class="leaderboard-table" id="tablify-me">
          <thead>
            <tr>
              <th>Username</th>
              <th>Total Scribbles</th>
              <th>Avatar Use</th>
              <th>Likes</th>
              <th>Dislikes</th>
              <th>Like Ratio</th>
            </tr>
          </thead>
        </table>
      </div>
    </div>
  </div>
  <script src="js/axios.min.js"></script>
  <script src="js/leaderboard.js"></script>
</body>
