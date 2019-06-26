<?php
class BoardPress {
  public $lists = array();
  public $labels = array();
  public $data = array();
  public $checklists = array();
  public $output = array();

  public $shortcode_filters = array();

  const META_FIELD = '_trello_card_id';
  const URL_PATTERN = 'https://api.trello.com/1/%s/%s?%s';

  // config
  private $api_key = '';
  private $api_token = '';
  private $board_id = null;

  function __construct() {
    $options = get_option('boardpress', array() );
    foreach ( $options as $var => $opt ) {
      $this->$var = $opt;
    }

    if ( $this->board_id ) {
      $this->data = $this->get_all_data();
    }

    // needed for use of strftime() and other functions
    setlocale(LC_TIME, [get_locale() . '.utf8', get_locale()]);
  }

  public static function shortcode( $raw_atts = [] ) {
    // normalize attribute keys, lowercase
    $raw_atts = array_change_key_case((array)$raw_atts, CASE_LOWER);

    // override default attributes with user attributes
    $atts = shortcode_atts(['list' => ''], $raw_atts);

    $tp = new self();
    $tp->shortcode_filters = $atts;
    try {
      return $tp->render_visible_cards();
    } catch (Exception $e) {
      syslog( LOG_WARNING, $e->getMessage() );
      return '';
    }
  }

  function render_visible_cards() {
    $this->data['cards'] = array_reverse( $this->data['cards'] );

    $this->checklists = $this->data['checklists'];

    $this->process_lists();
    $this->process_labels();
    $this->process_data();

    return $this->render_template( 'all-cards' );
  }

  function get_all_data() {
    return $this->get(
      'boards',
      $this->board_id,
      [
        'cards' => 'visible',
        'labels' => 'all',
        'lists' => 'all',
        'checklists' => 'all',
        'card_attachments' => 'cover'
      ]
    );
  }

  function get_board_link() {
    return "<a href='{$this->data['url']}'>{$this->data['name']}</a>";
  }

  function build_query( $object = 'boards', $id, $query = array() ) {
    $default_query = [
      'key'     => $this->api_key,
      'token'   => $this->api_token
    ];

    $q = http_build_query( array_merge( $default_query, $query ) );

    return sprintf( self::URL_PATTERN, $object, $id, $q );
  }

  function get( $object, $id, $query ) {
    // Get any existing copy of our transient data
    $cache_key = "{$object}-{$id}-" . implode( '-', $query );

    if ( false === ( $body = get_transient( $cache_key ) ) ) {
      // It wasn't there, so regenerate the data and save the transient
      $url = $this->build_query( $object, $id, $query );

      $res = wp_remote_get( $url );

      if ( is_wp_error( $res ) ) {
        throw new Exception(
          'Problem connecting to Trello API: status code ' . 
          wp_remote_retrieve_response_code( $res )
        );
      }

      $body = wp_remote_retrieve_body( $res );

      set_transient( $cache_key, $body, HOUR_IN_SECONDS );
    }

    return json_decode( $body, true );
  }

  function process_lists() {
    foreach ( $this->data['lists'] as $list ) {
      $this->lists[$list['id']] = $list['name'];
    }
  }

  function process_labels() {
    foreach ($this->data['labels'] as $label) {
      $this->labels[$label['id']] = array(
        'name' => $label['name'],
        'color' => $label['color'],
      );
    }
  }

  function process_data() {
//    die( var_dump($this->shortcode_filters));
    foreach ($this->data['cards'] as $card) {
      if (
        !empty($this->shortcode_filters['list']) &&
        $this->shortcode_filters['list'] != $this->lists[$card['idList']]
      ) {
        continue;
      }

      if ( class_exists( 'Parsedown') ) {
        $Parsedown = new Parsedown();
        $desc = $Parsedown->text( $card['desc'] );
      } else {
        $desc = '<p>' . $card['desc'] . '</p>';
      }

      $labels = $this->get_labels( $card['idLabels'] );
      $checklists = $this->get_checklists( $card['id'] );

      $update_time = strtotime( $card['dateLastActivity'] );
      $last_update = array(
        'time' => date('Y-m-d', $update_time),
        // @todo use wordpress date format
        'text' => strftime('%e %B %Y', $update_time)
      );

      if ( $card['idAttachmentCover'] ) {
        $cover = $card['attachments'][0]['url'];
      } else {
        $cover = '';
      }

      $this->output[$card['id']] = array(
        'url'     => $card['url'],
        'name'    => $card['name'],
        'desc'    => $desc,
        'labels'  => $labels,
        'checklists' => $checklists,
        'last_update' => $last_update,
        'cover' => $cover,
        'status'  => $this->lists[$card['idList']],
        'related_posts' => $this->get_related_posts( $card['id'] ),
        'statslug'=> strtolower(
          str_replace( ' ', '-', $this->lists[$card['idList']])
          )
      );
    }
  }

  function get_labels( $label_ids ) {
    $out = array();
    foreach ( $label_ids as $label_id ) {
      $out[] =  $this->labels[$label_id];
    }

    return $out;
  }

  function get_checklists( $card_id ) {
    // @todo Optimize!
    // @todo do this once for whole data set!
    $out = array();
    foreach ( $this->checklists as $checklist ) {
      if ( $checklist['idCard'] == $card_id ) {
        $out[] = $checklist;
      }
    }

    return $out;
  }

  function get_related_posts( $card_id ) {
    $args = array(
    'meta_key' => self::META_FIELD,
    'meta_value' => $card_id,
    'post_type' => 'post',
    'post_status' => 'published',
    'posts_per_page' => -1
);

    return get_posts($args);
  }

  function render_template( $template, $data = array() ) {

    ob_start();
    require( "templates/boardpress-$template.php" );
    $out = ob_get_contents();
    ob_end_clean();

    return $out;
  }

  public static function add_post_module( $content ) {
    global $post, $card;
    if (!empty($post) && is_single($post)) {
      $card_id = get_post_meta($post->ID, BoardPress::META_FIELD, true);
      if (!empty($card_id)) {
        $tp = new self();
        try {
          $card = $tp->get( 'cards', $card_id, ['fields' => 'all'] );
        } catch (Exception $e) {
          syslog( LOG_WARNING, $e->getMessage() );
          return $content; // needed?
        }

        $content .= $tp->render_template('single-card', $card);
      }
    }

    return $content;
  }

  public static function add_entry_footer() {
    $tp = new self();
    echo sprintf(
      __('Data taken from %s, graciously hosted on
          <a href="https://trello.com/">Trello</a>.', 'boardpress'),
        $tp->get_board_link()
    );
  }

  public static function register_css() {
      $plugin_url = plugin_dir_url( __FILE__ );

      wp_enqueue_style( 'boardpress-style', $plugin_url . 'css/style.css' );

      $theme_name = get_template();
      $theme_stylesheet = "css/{$theme_name}-specific.css";
      if ( file_exists( __DIR__ . '/' . $theme_stylesheet ) ) {
        wp_enqueue_style(
          'boardpress-style-' . $theme_name,
          $plugin_url . $theme_stylesheet
        );

      }
  }
}
