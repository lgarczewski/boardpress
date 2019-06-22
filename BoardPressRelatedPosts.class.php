<?php
// @todo add custom title to page
// @todo add intro to page

class BoardPressRelatedPosts {
  const QUERY_VAR = 'trello_related_posts';

  public static function get_link( $card_id ) {
    // @todo v2 make the related posts link pretty instead of query-based
    return add_query_arg( self::QUERY_VAR, $card_id, get_site_url() );
  }

  public static function filter_related_posts( $wp_query ) {
    $card_id = get_query_var( self::QUERY_VAR, false );
    if ( !$card_id ) {
      return;
    }

    $wp_query->set( 'meta_key', BoardPress::META_FIELD );
    $wp_query->set( 'meta_value', $card_id );
    $wp_query->set( 'meta_compare', '=' );
  }

  public static function add_query_vars_filter( $vars ) {
    $vars[] = self::QUERY_VAR;
    return $vars;
  }
}
