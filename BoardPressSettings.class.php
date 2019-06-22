<?php
class BoardPressSettings {
  private $options;

  const PAGE_NAME = 'boardpress-admin';

  public function __construct() {
    add_action( 'admin_menu', array( $this, 'add_plugin_page' ) );
    add_action( 'admin_init', array( $this, 'page_init' ) );
  }

  public function add_plugin_page() {
    // This page will be under "Settings"
    add_options_page(
      'BoardPress Settings',
      'BoardPress',
      'manage_options',
      'boardpress-admin',
      array( $this, 'create_admin_page' )
    );
  }

  public function create_admin_page() {
    $this->options = get_option( 'boardpress' );

    $this->render( 'settings-page');
  }

  /**
  * Register and add settings
  */
  public function page_init() {
    register_setting(
      'boardpress', // Option group
      'boardpress', // Option name
      array( $this, 'sanitize' ) // Sanitize
    );

    add_settings_section(
      'boardpress_api_section', // ID
      'Trello API settings', // Title
      array( $this, 'api_help_text' ), // Callback
      'boardpress-admin' // Page
    );

    add_settings_field(
      'boardpress_api_key', // ID
      'Trello API key', // Title
      array( $this, 'api_key_callback' ), // Callback
      'boardpress-admin', // Page
      'boardpress_api_section' // Section
    );

    add_settings_field(
      'boardpress_api_token',
      'Trello API token',
      array( $this, 'api_token_callback' ),
      'boardpress-admin',
      'boardpress_api_section'
    );

    add_settings_section(
      'boardpress_board_section', // ID
      'Trello board settings', // Title
      '', // Callback
      'boardpress-admin' // Page
    );

    add_settings_field(
      'boardpress_board_id',
      'Board tied to this blog',
      array( $this, 'board_id_callback' ),
      'boardpress-admin',
      'boardpress_board_section'
    );
  }

  /**
  * Sanitize each setting field as needed
  *
  * @param array $input Contains all settings fields as array keys
  */
  public function sanitize( $input ) {
    $new_input = array();
    $options = array( 'api_key', 'api_token', 'board_id' );
    foreach ( $options as $opt ) {
      if ( isset( $input[$opt] ) ) {
        $new_input[$opt] = sanitize_text_field( $input[$opt] );
      }
    }

    return $new_input;
  }

  public function api_help_text() {
    _e( 'To use this extension yoo need to <a href="https://trello.com/app-key">generate a Trello API key and token</a>.' );
  }

  public function api_token_callback() {
    $this->build_input('api_token');
  }

  public function api_key_callback() {
    $this->build_input('api_key');
  }

  public function board_id_callback() {
    if ( !empty($this->options) && !empty($this->options['api_key']) ) {
      $tp = new BoardPress();
      $boards = $tp->get( 'members', 'me', ['boards' => 'all'] );
      $this->render('settings-board-picker', $boards);
    } else {
      echo "set API token &amp; key first to be able to pick a board";
    }
  }

  public function get_settings_url() {
    return admin_url(
      'options-general.php?' . http_build_query(
        ['page' => self::PAGE_NAME]
      )
    );
  }

  private function build_input( $id ) {
    printf(
      "<input type='text' pattern='[\w]+' id='boardpress[{$id}]' name='boardpress[{$id}]' value='%s' />",
      isset( $this->options[$id] ) ? esc_attr( $this->options[$id]) : ''
    );
  }

  private function render( $template, $data = array() ) {
    include( "templates/boardpress-{$template}.php");
  }
}
