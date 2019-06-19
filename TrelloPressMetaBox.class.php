<?php
class TrelloPressMetaBox {

  function add_box() {
      add_meta_box(
        'trello_card_id',
        'Connected Trello Card',
        [ __CLASS__, 'box_html'],
        'post'
      );
    }

  function save_postdata($post_id) {
    if (array_key_exists('trellopress_card_id', $_POST)) {
      update_post_meta(
        $post_id,
        TrelloPress::META_FIELD,
        $_POST['trellopress_card_id']
      );
    }
  }

  function box_html($post) {
    $tp = new TrelloPress();
    $cards = $tp->getVisibleCards();
    $current_card_id = get_post_meta(
      $post->ID, TrelloPress::META_FIELD, true
    );
    include( 'templates/trellopress-meta-box.php');
  }
}
