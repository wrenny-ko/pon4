<?php
  require_once "../include/common/enableLogging.php"; //TODO remove; for development debugging only
  require_once "../include/common/initSession.php";
  require_once "../include/DatabaseHandler.php";
  require_once("../include/Perms.php");
  require_once("../include/LogController.php");

  $username = "anonymous";
  if (isset($_SESSION['username'])) {
    $username = $_SESSION['username'];
  }

  $perms = new Perms($username);

  //TODO made hasLevels()

  // redirect if unauthorized
  if (!$perms->hasLevel(AuthLevel::Tech) && !$perms->hasLevel(AuthLevel::Admin)) {
    header("location: index.php");
  }

  $endpoint = "log";
  if (isset($_GET["endpoint"])) {
    $endpoint = $_GET["endpoint"];
  }

  $log = new LogController($endpoint);
?>

<?php require_once("../include/common/header.php"); ?>
  <link rel="stylesheet" href="css/log.css" type="text/css">
  <script src="js/jquery-3.7.1.min.js"></script>
</head>

<body>
  <?php require_once("../include/common/navbar.php"); ?>
  <div class="bg">
    <div class="log-container">
      <div class="endpoint-title">
        <?= $endpoint ?>
      </div>
      <div class="logs">
      
      </div>
    </div>
  </div>
  <script src="js/fetchAvatar.js"></script>
  <script>
    const line = $('.logs')[0].createElement('div');
    line.classList.add('log-line');
    
    await 
    
    line.innerText = 
  </script>
</body>
