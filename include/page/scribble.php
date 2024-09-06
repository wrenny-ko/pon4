<div class="scribble-center">
  <div class="scribble-container">
    <div class="scribble-content-top">
      <img class="scribble-image" src=""/>
      <span class="scribble-attribution">
        by
        <a href="" class="scribble-author site-nav"></a>
      </span>
    </div>
    <div class="scribble-title"></div>
    <div class="like-bar">
      <div class="likes">Likes: </div>
      <div class="ratio">Ratio: </div>
      <div class="dislikes">Dislikes: </div>
    </div>
  </div>
  <div class="controls">
    <?php if (session_status() === PHP_SESSION_ACTIVE and isset($_SESSION['username'])) { ?>
      <div class="like-controls">
        <input type="button" class="like-button" value="Like"/>
        <input type="button" class="dislike-button" value="Dislike"/>
      </div>
      <input type="button" class="set-avatar-button" value="Set avatar"/>
      <?php if ($perms->hasModerator() || $perms->hasAdmin()) { ?>
        <input type="button" class="delete-button" value="Delete"/>
      <?php } ?>
    <?php } ?>
  </div>
</div>
