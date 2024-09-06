<?php require_once "../include/common/includes.php"; ?>
<head>
  <title>🐴 Pon4   🐎</title>
  <link rel="icon" href="favicon.ico" type="image/x-icon" />
  <link rel="shortcut icon" href="favicon.ico" type="image/x-icon" />
  <link rel="stylesheet" href="css/main.css" type="text/css">
  <link rel="stylesheet" href="css/scribble-card-cart.css" type="text/css">
  <link rel="stylesheet" href="css/scribble.css" type="text/css">
  <link rel="stylesheet" href="css/new.css" type="text/css">
  <link rel="stylesheet" href="css/leaderboard.css" type="text/css">
  <link rel="stylesheet" href="css/log.css" type="text/css">
  <link rel="stylesheet" href="css/user.css" type="text/css">
  <link rel="stylesheet" href="css/login.css" type="text/css">
  <link rel="stylesheet" href="css/signup.css" type="text/css">

  <script src="js/jquery-3.7.1.min.js"></script>
  <script src="js/axios.min.js"></script>
  <link href="https://cdn.datatables.net/v/dt/dt-2.1.5/datatables.min.css" rel="stylesheet">
  <script src="https://cdn.datatables.net/v/dt/dt-2.1.5/datatables.min.js"></script>
  <script src="js/index.js" type="module"></script>
</head>
<body>
  <?php require_once "../include/common/navbar.php";?>
  <div class="page" id="index-page">
    <div class="scribble-card-cart"></div>
  </div>
  <div class="page hidden" id="scribble-page">
    <?php require_once "../include/page/scribble.php";?>
  </div>
  <div class="page hidden" id="new-page">
    <?php require_once "../include/page/new.php";?>
  </div>
  <div class="page hidden" id="leaderboard-page">
    <?php require_once "../include/page/leaderboard.php";?>
  </div>
  <div class="page hidden" id="log-page">
    <?php require_once "../include/page/log.php";?>
  </div>
  <div class="page hidden" id="user-page">
    <?php require_once "../include/page/user.php";?>
  </div>
  <div class="page hidden" id="login-page">
    <?php require_once "../include/page/login.php";?>
  </div>
  <div class="page hidden" id="signup-page">
    <?php require_once "../include/page/signup.php";?>
  </div>
</body>
