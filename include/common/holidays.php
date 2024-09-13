<?php
  function initHoliday() {
    $nextHoliday = array("title" => "unknown", "date" => "unknown", "link" => "unknown");

    $start = date("Y-m-d");
    $end = date("Y-m-d", strtotime("+7 months"));

    $cacheFilename = "/var/www/tmp/holidays.json";
    $stat = @stat($cacheFilename);

    // cache file if file not cached OR if cached file is older than 1 day
    if (!$stat || $stat["mtime"] < strtotime('-1 day'))  {
      `curl https://www.hebcal.com/hebcal?cfg=json\&v=1\&maj=on\&yto=on\&start={$start}\&end={$end} > {$cacheFilename}`;
      //exec($cmd, $output, $retval);

      //file_put_contents($cacheFilename, $res);
    }

    $file = file_get_contents($cacheFilename);
    $json = json_decode($file);

    if(property_exists($json, 'items') && count($json->items) > 0) {
      $nextHoliday["title"] = $json->items[0]->title;
      $nextHoliday["date"] = $json->items[0]->date;
      $nextHoliday["link"] = $json->items[0]->link;
    }

    return $nextHoliday;
  }

  $nextHoliday = initHoliday();
?>
<div class="holidays-widget nav-entry">
  <a href="<?= $nextHoliday["link"]; ?>" class="feast">
    <img src="icon/shofar.png" class="icon" alt="shofar"/>
    <div class="holiday-info">
      <div class="holiday-title">
        <?= $nextHoliday["title"]; ?>
      </div>
      <div class="holiday-date">
        <?= $nextHoliday["date"]; ?>
      </div>
    </div>
  </a>
</div>
