<?php
/**
 * Plugin Name: TrelloPress
 * Description: A plugin integrating Trello with WordPress
 * Version: 1.0
 * Author: Łukasz Garczewski
 * Author URI: http://phareios.com/open-source/
 * Text Domain: trellopress
 * Domain Path: /languages
 */

# -- before release 1.1 --
# @todo activation flow with API activation
# @todo labels as links to a label view
# @todo card-based view in lists
# @todo fix footer

$class = 'TrelloPress';
require_once( 'TrelloPress.class.php' );
require_once( 'TrelloPressMetaBox.class.php' );
require_once( 'TrelloPressRelatedPosts.class.php' );
require_once( 'TrelloPressSettings.class.php' );
if( is_admin() ) new TrelloPressSettings();

// TrelloPress hooks
add_action('wp_enqueue_scripts', [$class, 'register_css']);
add_action('the_content', [$class,'add_post_module']);
add_action('libre_2_entry_footer', [$class,'add_entry_footer']);
add_shortcode('trellopress', [$class, 'shortcode']);

// TrelloPressMetaBox hooks
add_action('add_meta_boxes', [$class.'MetaBox', 'add_box']);
add_action('save_post', [$class.'MetaBox', 'save_postdata']);

// TrelloPressRelatedPosts hooks
add_action('pre_get_posts', [$class.'RelatedPosts','filter_related_posts']);
add_filter('query_vars', [$class.'RelatedPosts','add_query_vars_filter']);

// register_activation_hook( )
// add_action();
function trellopress_activate() {
}

add_action('plugins_loaded', 'trellopress_init');
function trellopress_init() {
  // localization
  $plugin_rel_path = basename( dirname( __FILE__ ) ) . '/languages';
  load_plugin_textdomain( 'trellopress', false, $plugin_rel_path );
}

add_filter('plugin_action_links', 'trellopress_action_links', 10, 2);
function trellopress_action_links( $links, $plugin_file ) {
  if ( $plugin_file == plugin_basename(__FILE__) ) {
    $settings_url = admin_url(
      'options-general.php?' . http_build_query(
        ['page' => TrelloPressSettings::PAGE_NAME]
      )
    );
    $links[] = "<a href='{$settings_url}'>" . __( 'Settings' ) . "</a>";
  }
  return $links;
}
