<div class="leaderboard-container">
  <div class="leaderboard-title">
    Leaderboard
  </div>
  <?php if ($perms->hasAdmin()) { ?>
    <div class="leaderboard-tools">
        <form class="set-max-rows-form hidden">
          Set Max Rows
          <div class="set-max-rows-form-inner">
            <input class="set-max-rows-input text-input" type="text" name="max-rows" placeholder="10"/>
            <button class="set-max-rows-button button" type="button">Update</button>
          </div>
        </form>
        <button class="show-set-max-rows-form button" type="button">Set Max Rows</button>
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
