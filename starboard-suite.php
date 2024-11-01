<?php
/**
 * @package Starboard Suite Reservation Calendars
 */
/*
Plugin Name: Starboard Suite Reservation Calendars
Description: Easily add Starboard Suite booking calendars to your WordPress site
Version: 3.0.0
Author: Starboard Suite
Author URI: https://www.starboardsuite.com
License: GPLv2 or later
Text Domain: starboard-suite
*/

/*
Starboard Suite WordPress Plugin
Copyright (C) 2018 Starboard Suite <info@starboardsuite.com>

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

defined('ABSPATH') or die( 'Perhaps your lost? <a href="/">Go home.</a>' );

class StarboardSuitePlugin
{
  public $plugin;

  private $menu_title = "Starboard Suite";
  private $settings_page_title = 'Starboard Suite Booking Lightbox';
  private $icon_link = '/assets/starboard-suite-icon.svg';
  private $lightbox_base = '.starboardsuite.com';
  private $embedded_base = '.starboardsuite.com/choose-date-embedded';

  private $plugin_id = 'starboard_suite_plugin';
  private $section_id = 'starboard_suite_settings';
  private $field_id = 'starboard_suite_subdomain';
  private $field_description = 'The subdomain associated with your Starboard Suite account (e.g., https://<b>wildwoodcharters</b>.starboardsuite.com)';


  function __construct() {
    $this->plugin = plugin_basename( __FILE__ );
  }

  function register() {
    add_action('wp_enqueue_scripts',array($this, 'enqueue'));
    add_action('admin_menu', array($this,'add_admin_pages'));
    add_action('admin_init',array($this,'admin_init'));
    add_filter("plugin_action_links_$this->plugin",array($this,'settings_link'));
    $this->addShortCodes();
  }

  public function starboard_suite_lightbox( $atts = [] , $content = null) {
    $field_id = $this->field_id;
    $lightbox_base = $this->lightbox_base;
    $url = 'https://'.esc_attr(get_option($field_id)).$lightbox_base;
    $dataTags = '';
    $qs = '';
    if(!empty($atts)) {
      foreach ($atts as $key => $value) {
        if($key=='gift-certificates' || $key=='gift-certificate') {
          $url .= '/gift-certificates';
        } elseif ($key=='tour_type_id' || $key=='departure_location_id' || $key=='vessel_id') {
          $qs .= $qs == '' ? "?" : "&";
          $qs .= $key."=".$value;
        } else {
          $dataTags .= 'data-'.$key.'="'.$value.'" ';
        }
      }
    }
    return '<a class="starboard-lightbox starboard-wordpress-lightbox" '.$dataTags.'href="'.$url.$qs.'">'.$content.'</a>';
  }

  public function starboard_suite_embedded( $atts = [] , $content = null) {
    $field_id = $this->field_id;
    $embedded_base = $this->embedded_base;
    $url = 'https://'.esc_attr(get_option($field_id)).$embedded_base;
    $style = 'style="width: 100%; height: 600px;';
    $qs = '';
    if(!empty($atts)) {
      foreach ($atts as $key => $value) {
        if($key=='tour_type_id' || $key=='departure_location_id' || $key=='vessel_id') {
          $qs .= $qs == '' ? "?" : "&";
          $qs .= $key."=".$value;
        }
        $style .= $key.": ".$value.";";
      }
    }
    
    $style .= '"';
 
    return '<iframe class="starboard-wordpress-embedded" src="'.$url.$qs.'"'.$style.'frameborder=0></iframe>';
  }

  private function addShortCodes() {
    add_shortcode( 'starboard-suite-lightbox', array($this,'starboard_suite_lightbox') );
    add_shortcode( 'starboard-suite-embedded', array($this,'starboard_suite_embedded') );
  }

  public function settings_link($links) {
    $settings_link = '<a href="admin.php?page='.$this->plugin_id.'">Settings</a>';
    array_push($links,$settings_link);
    return $links;
  }

  public function admin_init() {
    $section = $this->section_id;
    $page = $this->plugin_id;
    $field = $this->field_id;
    $field_description = $this->field_description;

    register_setting(
      $section,                           //ID of settings section
      $field                              //ID of field
    );

    add_settings_section(
      $section,                           //id of section
      '',                                 //title of section displayed on page
      array($this,'render_section'),      //callback to description of section
      $page                               //page on which to add this section of options
    );

    add_settings_field(
      $field,                             //ID of field
      'Subdomain',                        //Label to the left of field
      array($this,'render_field'),        //Callback function to render
      $page,                              //The page on which this option will be displayed
      $section,                           //The name of the section to which this field belongs
      array(
        'option_name' => $field,
        'description' => $field_description,
      )                            
    );
    
  }

  public function add_admin_pages() {
    add_menu_page(
      $this->settings_page_title,
      $this->menu_title,
      'manage_options',
      $this->plugin_id,
      array($this, 'admin_index'),
      plugins_url($this->icon_link,__FILE__),
      110
    );
  }

  public function render_section() {
    _e('<h2>About</h2><p>In order for this plugin to work, you must have a Starboard Suite account. 
    Starboard Suite provides effortless reservations and ticketing for passenger vessels and watersports and is customized for your brand and business. 
    For more information or to setup an account, visit <a href="https://www.starboardsuite.com" target="_blank">www.starboardsuite.com</a>.</p>
    <p>If you already have a Starboard Suite account, <a href="https://support.starboardsuite.com/integrating-with-your-website/advanced-using-our-wordpress-plugin" target="_blank">click here to view our knowledge base article</a> on how to use this plugin.</p>
    <h2>Settings</h2>');
  }

  public function render_field($args) {
    $value = esc_attr( get_option($this->field_id));
    echo 'https://<input type="text" class="regular-text" name="'.$this->field_id.'" value="'.$value.'" />.starboardsuite.com';
    if ( !empty( $args[ 'description' ] ) )
      echo "<p class=\"description\">{$args[ 'description' ]}</p>";
  }

  public function admin_index() {
    echo '<h1 style="line-height: 130%">'.$this->settings_page_title.'</h1>';
    settings_errors();
    echo '<form action="options.php" method="POST">';
    settings_fields($this->section_id);
    do_settings_sections($this->plugin_id);
    submit_button();
    echo '</form>';
  }

  function activate() {
    flush_rewrite_rules();
  }

  function deactivate() {
    flush_rewrite_rules();
  }

  function enqueue() {
    $subdomain = get_option($this->field_id);
    if($subdomain!='') {
      wp_enqueue_script('ss-booking-lightbox-js', "https://$subdomain.starboardsuite.com/assets/frontend/booking-lightbox.js");
    }
  }  
}

if ( class_exists( 'StarboardSuitePlugin' ) ) {
  $starboardSuitePlugin = new StarboardSuitePlugin();
  $starboardSuitePlugin->register();
}

//activation
register_activation_hook(__FILE__, array($starboardSuitePlugin,'activate'));

//deactivation
register_deactivation_hook(__FILE__, array($starboardSuitePlugin,'deactivate'));