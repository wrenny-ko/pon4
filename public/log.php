<?php
  require_once "../include/common/includes.php";

  // redirect if unauthorized
  if (!$perms->hasLevel(AuthLevel::Tech) && !$perms->hasLevel(AuthLevel::Admin)) {
    header("location: index.php");
  }

  $endpoint = "";
  if (isset($_GET["endpoint"])) {
    $endpoint = $_GET["endpoint"];
  }
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
        API logs - 
        <?= $endpoint ? $endpoint : "all endpoints" ?>
      </div>
      <div class="log-box">
      </div>
    </div>
  </div>
  <script src="js/log.js"></script>
</body>
