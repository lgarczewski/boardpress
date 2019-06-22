<?php
/**
 * Plugin Name: BoardPress
 * Description: A plugin integrating Trello with WordPress
 * Version: 1.0
 * Author: Łukasz Garczewski
 * Author URI: http://phareios.com/open-source/
 * Text Domain: boardpress
 * Domain Path: /languages
 */

$class = 'BoardPress';
require_once( 'BoardPress.class.php' );
require_once( 'BoardPressMetaBox.class.php' );
require_once( 'BoardPressRelatedPosts.class.php' );
require_once( 'BoardPressSettings.class.php' );
if( is_admin() ) new BoardPressSettings();

// BoardPress hooks
add_action('wp_enqueue_scripts', [$class, 'register_css']);
add_action('the_content', [$class,'add_post_module']);
add_action('libre_2_entry_footer', [$class,'add_entry_footer']);
add_shortcode('boardpress', [$class, 'shortcode']);

// BoardPressMetaBox hooks
add_action('add_meta_boxes', [$class.'MetaBox', 'add_box']);
add_action('save_post', [$class.'MetaBox', 'save_postdata']);

// BoardPressRelatedPosts hooks
add_action('pre_get_posts', [$class.'RelatedPosts','filter_related_posts']);
add_filter('query_vars', [$class.'RelatedPosts','add_query_vars_filter']);

register_activation_hook( __FILE__, 'boardpress_activate');
function boardpress_activate() {
  add_option('Activated_Plugin','boardpress');
}

add_action('admin_init','boardpress_activation_flow');
function boardpress_activation_flow() {
    if(is_admin() && get_option('Activated_Plugin')=='boardpress') {
      delete_option('Activated_Plugin');
      wp_redirect( BoardPressSettings::get_settings_url() );
    }
}

add_action('plugins_loaded', 'boardpress_init');
function boardpress_init() {
  // localization
  $plugin_rel_path = basename( dirname( __FILE__ ) ) . '/languages';
  load_plugin_textdomain( 'boardpress', false, $plugin_rel_path );
}

add_filter('plugin_action_links', 'boardpress_action_links', 10, 2);
function boardpress_action_links( $links, $plugin_file ) {
  if ( $plugin_file == plugin_basename(__FILE__) ) {
    $settings_url = BoardPressSettings::get_settings_url();
    $links[] = "<a href='{$settings_url}'>" . __( 'Settings' ) . "</a>";
  }
  return $links;
}
