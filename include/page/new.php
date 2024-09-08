<div class="new-layout">
  <div class="pad-container">
    <canvas class="pad"></canvas>
  </div>
  <div class="button-container">
    <input type="button" class="button clear-canvas-button" value="Reset"/>
    <input type="button" class="button submit-button" value="Submit"/>
      <?php if ($perms->hasBeta()) { ?>
        <form class="import-base-form">
          <input type="button" class="button import-button" value="Import"/>
          <input type="text" class="input-import-id new-text-input" placeholder="id"/>
        </form>
        <div class="import-error hidden"></div>
      <?php } ?>
    <div class="spinner hidden"></div>
    <div class="error"></div>
  </div>
  <div class="title-modal center hidden">
    <img class="thumb">
    <div class="title-label">What do you want to call it?</div>
    <input type="text" class="new-text-input input-title" maxlength="30"/>
    <input type="button" class="button title-button" value="Post it!"/>
    <input type="button" class="button close-title-modal" value="Go back!"/>
  </div>
</div>
