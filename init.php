<?php
/*
Plugin Name: WP Raven Widgets
Plugin URI: http://www.ravendevelopers.com/blog/2016/08/wp-raven-widgets
Description: WP Raven Widgets is a collection of very useful and powerful widgets for Wordpress
Author: Anirudh K. Mahant, inquiry@ravendevelopers.com
Version: 1.0
Author URI: http://www.ravendevelopers.com
*/

//-- Constants
if ( ! defined( 'WP_RAVEN_WIDGETS_VERSION' ) ) {
	define( 'WP_RAVEN_WIDGETS_VERSION', 1.0 );
}

if ( ! defined( 'WP_RAVEN_WIDGETS_PLUGIN_DIR' ) ) {
	define( 'WP_RAVEN_WIDGETS_PLUGIN_DIR', WP_PLUGIN_DIR . '/wp-raven-widgets' );
}

if ( ! defined( 'WP_RAVEN_WIDGETS_PLUGIN_VIEWS_DIR' ) ) {
	define( 'WP_RAVEN_WIDGETS_PLUGIN_VIEWS_DIR', WP_RAVEN_WIDGETS_PLUGIN_DIR . '/inc/views' );
}

if ( ! defined( 'WP_RAVEN_WIDGETS_PLUGIN_LANG_DIR' ) ) {
	define( 'WP_RAVEN_WIDGETS_PLUGIN_LANG_DIR', WP_RAVEN_WIDGETS_PLUGIN_DIR . '/languages' );
}

if ( ! defined( 'WPRAVEN_AJAX_HANDLER_URL' ) ) {
	define( 'WPRAVEN_AJAX_HANDLER_URL', get_bloginfo( 'url' ) . '/wp-admin/admin-ajax.php' );
}

add_action( 'admin_enqueue_scripts', 'wpraven_widgets_admin_styles' );

function wpraven_widgets_admin_styles(){
	wp_register_style( 'wpraven_admin_css', plugins_url( '/scss/wpraven-admin.css', __FILE__ ), false, WP_RAVEN_WIDGETS_VERSION );
	wp_enqueue_style( 'wpraven_admin_css' );
}

require_once( WP_RAVEN_WIDGETS_PLUGIN_DIR . '/lib/zen-theme-controls.php' );
require_once( WP_RAVEN_WIDGETS_PLUGIN_DIR . '/inc/class.calendar.widget.php' );
require_once( WP_RAVEN_WIDGETS_PLUGIN_DIR . '/inc/class.ga.popular.posts.widget.php' );