<div class="wrap">
  <h1><?= __('BoardPress Settings') ?></h1>
  <form method="post" action="options.php">
    <?php
    settings_fields( 'boardpress' );
    do_settings_sections( 'boardpress-admin' );
    submit_button();
    ?>
  </form>
</div>
