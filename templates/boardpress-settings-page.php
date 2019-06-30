<div class="wrap">
  <h1><?= esc_html_e('BoardPress Settings', 'boardpress') ?></h1>
  <form method="post" action="options.php">
    <?php
    settings_fields( 'boardpress' );
    do_settings_sections( 'boardpress-admin' );
    submit_button();
    ?>
  </form>
</div>
