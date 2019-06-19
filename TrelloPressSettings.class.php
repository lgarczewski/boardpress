<?php
class TrelloPressSettings {
  private $options;

  const PAGE_NAME = 'trellopress-admin';

  public function __construct() {
    add_action( 'admin_menu', array( $this, 'add_plugin_page' ) );
    add_action( 'admin_init', array( $this, 'page_init' ) );
  }

  public function add_plugin_page() {
    // This page will be under "Settings"
    add_options_page(
      'TrelloPress Settings',
      'TrelloPress',
      'manage_options',
      'trellopress-admin',
      array( $this, 'create_admin_page' )
    );
  }

  public function create_admin_page() {
    $this->options = get_option( 'trellopress' );

    $this->render( 'settings-page');
  }

  /**
  * Register and add settings
  */
  public function page_init() {
    register_setting(
      'trellopress', // Option group
      'trellopress', // Option name
      array( $this, 'sanitize' ) // Sanitize
    );

    add_settings_section(
      'trellopress_api_section', // ID
      'Trello API settings', // Title
      array( $this, 'api_help_text' ), // Callback
      'trellopress-admin' // Page
    );

    add_settings_field(
      'trellopress_api_key', // ID
      'Trello API key', // Title
      array( $this, 'api_key_callback' ), // Callback
      'trellopress-admin', // Page
      'trellopress_api_section' // Section
    );

    add_settings_field(
      'trellopress_api_token',
      'Trello API token',
      array( $this, 'api_token_callback' ),
      'trellopress-admin',
      'trellopress_api_section'
    );

    add_settings_section(
      'trellopress_board_section', // ID
      'Trello board settings', // Title
      '', // Callback
      'trellopress-admin' // Page
    );

    add_settings_field(
      'trellopress_board_id',
      'Board tied to this blog',
      array( $this, 'board_id_callback' ),
      'trellopress-admin',
      'trellopress_board_section'
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
      $tp = new TrelloPress();
      $boards = $tp->get( 'members', 'me', ['boards' => 'all'] );
      $this->render('settings-board-picker', $boards);
    } else {
      echo "set API token &amp; key first to be able to pick a board";
    }
  }

  private function build_input( $id ) {
    printf(
      "<input type='text' pattern='[\w]+' id='trellopress[{$id}]' name='trellopress[{$id}]' value='%s' />",
      isset( $this->options[$id] ) ? esc_attr( $this->options[$id]) : ''
    );
  }

  private function render( $template, $data = array() ) {
    include( "templates/trellopress-{$template}.php");
  }
}
