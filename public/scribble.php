<?php
  header("Access-Control-Allow-Methods: GET");

  require_once "../include/common/enableLogging.php"; //TODO remove

  // redirect if invalid request
  if ($_SERVER["REQUEST_METHOD"] !== 'GET') {
    header("location: index.php");
  }

  // require a scribble id to view this page
  if (!isset($_GET["id"])) {
      header("location: index.php");
  }
  $id = $_GET["id"];

  session_start();
  $username = "anonymous";
  if (isset($_SESSION['username'])) {
    $username = $_SESSION['username'];
  }

  require_once "../include/DatabaseHandler.php";
  require_once("../include/Perms.php");

  $perms = new Perms($username);
?>
<?php require_once("../include/common/header.php"); ?>
  <link rel="stylesheet" href="css/scribble.css" type="text/css">
</head>
<body>
  <?php require_once("../include/common/navbar.php"); ?>
  <div class="bg">
    <div class="scribble-center">
      <?php echo "<div class=\"scribble-container\" id=\"$id\">"; ?>
        <div class="scribble-content-top">
          <img class="scribble-image" src=""/>
          <span class="scribble-attribution">
            by
            <a href="" class="scribble-author"></a>
          </span>
        </div>
        <div class="scribble-title"></div>
      </div>
      <?php if (session_status() === PHP_SESSION_ACTIVE and isset($_SESSION['username'])) { ?>
        <input type="button" class="button set-avatar-button" value="Set avatar" onclick="setAvatar('<?= $_SESSION['username'];?>');"/>
        <?php if ($perms->hasMod() || $perms->hasAdmin()) { ?>
          <input type="button" class="button delete-button" value="Delete" onclick="deleteScribble('<?= $id;?>');"/>
        <?php } ?>
      <?php } ?>
    </div>
  </div>
  <script src="js/fetchAvatar.js"></script>
  <script src="js/scribble.js"></script>
</body>
