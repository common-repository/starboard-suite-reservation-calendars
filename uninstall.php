<?php

/**
 * Trigger this file on plugin uninstall
 * 
 * @package Starboard Suite WordPress Plugin
 */

if ( !defined( 'WP_UNINSTALL_PLUGIN' ) ) {
  die;
}

// clear database of Starboard Suite data
delete_option( 'starboard_suite_settings' );
delete_option( 'starboard_suite_subdomain' );