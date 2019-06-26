<?php
class BoardPressMetaBox {

  public static function add_box() {
      add_meta_box(
        'trello_card_id',
        'Connected Trello Card',
        [ __CLASS__, 'box_html'],
        'post'
      );
    }

  function save_postdata($post_id) {
    if (array_key_exists('boardpress_card_id', $_POST)) {
      update_post_meta(
        $post_id,
        BoardPress::META_FIELD,
        $_POST['boardpress_card_id']
      );
    }
  }

  public static function box_html($post) {
    $tp = new BoardPress();
    $cards = $tp->get_all_data();
    $current_card_id = get_post_meta(
      $post->ID, BoardPress::META_FIELD, true
    );
    include( 'templates/boardpress-meta-box.php');
  }
}
