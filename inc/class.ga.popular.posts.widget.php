<?php

/**
 * @package        class.ga.popular.posts.widget.php
 * @subpackage     asrzen
 * @author         Anirudh Sethi
 * @created        7/20/15 - 3:20 PM
 * @license        Creative Commons 3.0 Attribution
 * @licenseurl    https://creativecommons.org/licenses/by/3.0/us/
 * @desc           Google Analytics based Popular posts. Data is gathered based on no. of Pageviews
 * @link           http://www.anirudhsethireport.com
 */

/**
 * Filter Example:
 * pagePath=~ \\?p=* && pagePath!~ \\?page=*
 */


if ( ! defined( 'WP_RAVEN_WIDGETS_VERSION' ) ) {
	exit( 'No direct script access allowed' );
}

require_once( WP_RAVEN_WIDGETS_PLUGIN_DIR . '/lib/gapi.class.php' );

class WP_Raven_Widget_GAPopularPosts extends WP_Widget {

	var $widgetdomain;

//	const passphrase = "WeBsTEr90346simPEr92329COrnEreD8507pASsEs69586InVOiCInG";

	public function __construct() {
		$this->widgetdomain = load_theme_textdomain( 'wpraven', WP_RAVEN_WIDGETS_PLUGIN_LANG_DIR . '/languages' );
		$widget_ops         = array( 'classname' => 'wpraven-popular-posts', 'description' => __( 'WP Raven Popular posts based on Google Analytics data' ) );
		$control_ops        = array( 'width' => 800, 'height' => 350 );
		parent::__construct( 'wpraven_ga_popular_posts', __( 'WP Raven GA Popular Posts' ), $widget_ops, $control_ops );
	}

	public function widget( $args, $instance ) {

		// Pass vars to views
		extract( $args, EXTR_SKIP );

		echo ( isset( $args['before_widget'] ) && ! empty( $args['before_widget'] ) ) ? $args['before_widget'] : "";

		if ( ! empty( $instance['title'] ) ) {
			echo $args['before_title'] . $instance['title'] . $args['after_title'];
		}

		echo ( isset( $args['before_content'] ) && ! empty( $args['before_content'] ) ) ? $args['before_content'] : "";

		// Get GAPI report results
		$instance['results_cache'] = self::__asrzen_get_ga_report( $instance );

		include_once( WP_RAVEN_WIDGETS_PLUGIN_VIEWS_DIR . '/view.ga.popular.posts.php' );

		echo ( isset( $args['after_content'] ) && ! empty( $args['after_content'] ) ) ? $args['after_content'] : "";

		echo ( isset( $args['after_widget'] ) && ! empty( $args['after_widget'] ) ) ? $args['after_widget'] : "";

	}

	public function update( $new_instance, $old_instance ) {
		$instance = $old_instance;

		$instance['title']       = $new_instance['title'];
		$instance['ga_username'] = $new_instance['ga_username'];
//		$instance['ga_pwd'] = $cipher->encrypt($new_instance['ga_pwd']);
		$instance['ga_profile_id']      = $new_instance['ga_profile_id'];
		$instance['client_secret_path'] = $new_instance['client_secret_path'];
		$instance['query_post_count']   = $new_instance['query_post_count'];
		$instance['show_post_count']    = $new_instance['show_post_count'];
		$instance['trim_title']         = $new_instance['trim_title'];
		$instance['data_months']        = $new_instance['data_months'];
		$instance['show_teaser']        = $new_instance['show_teaser'];
		$instance['teaser_trim']        = $new_instance['teaser_trim'];
		$instance['ga_filter']          = base64_encode( $new_instance['ga_filter'] );
		$instance['cache_lifetime']     = $new_instance['cache_lifetime'];
		$instance['debugmode']          = $new_instance['debugmode'];
		//$instance['clear_cache'] = $new_instance['clear_cache'];
		if ( $instance['clear_cache'] === true ) {
			delete_transient( '__wpraven_ga_results_cache' );
		}

		return $instance;
	}

	public function form( $instance ) {

		// Defaults
		$instance = wp_parse_args( (array) $instance, array(
			'title'            => '',
			'ga_username'      => '',
			'ga_pwd'           => '',
			'ga_profile_id'    => '',
			'query_post_count' => 100,
			'show_post_count'  => 10,
			'trim_title'       => 50,
			'show_teaser'      => false,
			'teaser_trim'      => false,
			'data_months'      => 30,
			'ga_filter'        => '',
			'cache_lifetime'   => 1440,
			'debugmode'        => true,
			'clear_cache'      => false,
		) );

		$wc = new Zen_Theme_Controls();

		$wc->eztc(
			array(
				'#control'      => 'text',
				'#name'         => $this->get_field_name( 'title' ),
				'#id'           => $this->get_field_id( 'title' ),
				'#title'        => __( 'Title', 'wpraven' ),
				'#class'        => 'widefat',
				'#prefix_title' => '<p>',
				'#suffix'       => '</p>',
				'#value'        => isset( $instance['title'] ) ? $instance['title'] : '',
			)
		);

		echo '<div class="row">';

		echo '<div class="azcol span_3">';
		$wc->eztc(
			array(
				'#control'      => 'text',
				'#name'         => $this->get_field_name( 'ga_username' ),
				'#id'           => $this->get_field_id( 'ga_username' ),
				'#title'        => __( 'Google Account Username', 'wpraven' ),
				'#class'        => 'widefat',
				'#prefix_title' => '<p>',
				'#suffix'       => '<div class="tip">Username should be typically <strong>585222212-68ddrbc6qis0u@developer.gserviceaccount.com</strong> This is usually linked with your client secret file.</div></p>',
				'#value'        => isset( $instance['ga_username'] ) ? $instance['ga_username'] : '',
			)
		);

		$wc->eztc(
			array(
				'#control'      => 'text',
				'#name'         => $this->get_field_name( 'ga_profile_id' ),
				'#id'           => $this->get_field_id( 'ga_profile_id' ),
				'#title'        => __( 'Google Account Profile ID', 'wpraven' ),
				'#class'        => 'widefat',
				'#prefix_title' => '<p>',
				'#suffix'       => '</p>',
				'#value'        => isset( $instance['ga_profile_id'] ) ? $instance['ga_profile_id'] : '',
			)
		);

		$wc->eztc(
			array(
				'#control'      => 'text',
				'#name'         => $this->get_field_name( 'client_secret_path' ),
				'#id'           => $this->get_field_id( 'client_secret_path' ),
				'#title'        => __( 'Google Client Secret File Path', 'wpraven' ),
				'#class'        => 'widefat',
				'#prefix_title' => '<p>',
				'#suffix'       => '<div class="tip">Generate a client secret file from Google API Console -> Service Accounts -> Create Key and upload it to a secure location and specify fully qualified path here. The generated file name is typically <strong>client_secret.p12</strong>. Path should be like <em>/home/example/public_html/wp-content/client_secrets.p12</em></div></p>',
				'#value'        => isset( $instance['client_secret_path'] ) ? $instance['client_secret_path'] : '',
			)
		);

		echo '</div><!--//span_3-->';

		echo '<div class="azcol span_3">';
		$wc->eztc(
			array(
				'#control'      => 'text',
				'#name'         => $this->get_field_name( 'query_post_count' ),
				'#id'           => $this->get_field_id( 'query_post_count' ),
				'#title'        => __( 'Query Post Count', 'wpraven' ),
				'#class'        => 'widefat',
				'#prefix_title' => '<p>',
				'#suffix'       => '</p>',
				'#value'        => isset( $instance['query_post_count'] ) ? $instance['query_post_count'] : '',
			)
		);

		$wc->eztc(
			array(
				'#control'      => 'text',
				'#name'         => $this->get_field_name( 'show_post_count' ),
				'#id'           => $this->get_field_id( 'show_post_count' ),
				'#title'        => __( 'Show Post Count', 'wpraven' ),
				'#class'        => 'widefat',
				'#prefix_title' => '<p>',
				'#suffix'       => '</p>',
				'#value'        => isset( $instance['show_post_count'] ) ? $instance['show_post_count'] : '',
			)
		);

		$wc->eztc(
			array(
				'#control'      => 'text',
				'#name'         => $this->get_field_name( 'trim_title' ),
				'#id'           => $this->get_field_id( 'trim_title' ),
				'#title'        => __( 'Trim Title', 'wpraven' ),
				'#class'        => 'widefat',
				'#prefix_title' => '<p>',
				'#suffix'       => '</p>',
				'#value'        => isset( $instance['trim_title'] ) ? $instance['trim_title'] : '',
			)
		);
		echo '</div><!--//span_3-->';

		echo '</div><!--//row-->';

		echo "<hr />";

		echo '<div class="row">';

		echo '<div class="azcol span_3">';
		$wc->eztc(
			array(
				'#control'      => 'checkbox',
				'#name'         => $this->get_field_name( 'show_teaser' ),
				'#id'           => $this->get_field_id( 'show_teaser' ),
				'#title'        => __( 'Show Teaser', 'wpraven' ),
				'#prefix'       => '<p>',
				'#suffix_title' => '</p>',
				'#checked'      => isset( $instance['show_teaser'] ) ? $instance['show_teaser'] : 0,
			)
		);

		$wc->eztc(
			array(
				'#control'      => 'text',
				'#name'         => $this->get_field_name( 'teaser_trim' ),
				'#id'           => $this->get_field_id( 'teaser_trim' ),
				'#title'        => __( 'Teaser Trim', 'wpraven' ),
				'#class'        => 'widefat',
				'#prefix_title' => '<p>',
				'#suffix'       => '</p>',
				'#value'        => isset( $instance['teaser_trim'] ) ? $instance['teaser_trim'] : '',
			)
		);

		$wc->eztc(
			array(
				'#control'      => 'text',
				'#name'         => $this->get_field_name( 'data_months' ),
				'#id'           => $this->get_field_id( 'data_months' ),
				'#title'        => __( 'Retreive Data Since', 'wpraven' ),
				'#class'        => 'widefat',
				'#prefix_title' => '<p>',
				'#suffix'       => '<div class="tip">Specify in days</div></p>',
				'#value'        => isset( $instance['data_months'] ) ? $instance['data_months'] : '',
			)
		);
		echo '</div><!--//span_3-->';

		echo '<div class="azcol span_3">';
		$wc->eztc(
			array(
				'#control'      => 'text',
				'#name'         => $this->get_field_name( 'ga_filter' ),
				'#id'           => $this->get_field_id( 'ga_filter' ),
				'#title'        => __( 'Query Filter', 'wpraven' ),
				'#class'        => 'widefat',
				'#prefix_title' => '<p>',
				'#suffix'       => '</p>',
				'#base64encode' => true,
				'#value'        => isset( $instance['ga_filter'] ) ? base64_decode( $instance['ga_filter'] ) : '',
			)
		);

		$wc->eztc(
			array(
				'#control'      => 'text',
				'#name'         => $this->get_field_name( 'cache_lifetime' ),
				'#id'           => $this->get_field_id( 'cache_lifetime' ),
				'#title'        => __( 'Cache Lifetime', 'wpraven' ),
				'#class'        => 'widefat',
				'#prefix_title' => '<p>',
				'#suffix'       => '<div class="tip">Specify in minutes. <strong>Caching</strong> must be enabled and set to atleast 6 hrs.</div></p>',
				'#value'        => isset( $instance['cache_lifetime'] ) ? $instance['cache_lifetime'] : '',
			)
		);

		$wc->eztc(
			array(
				'#control'      => 'checkbox',
				'#name'         => $this->get_field_name( 'debugmode' ),
				'#id'           => $this->get_field_id( 'debugmode' ),
				'#title'        => __( 'Debug Mode', 'wpraven' ),
				'#prefix'       => '<p>',
				'#suffix_title' => '<div class="tip">Activate <strong>Debug</strong> mode when testing in <strong>localhost</strong> environment.</div></p>',
				'#checked'      => isset( $instance['debugmode'] ) ? $instance['debugmode'] : 0,
			)
		);
		echo '</div><!--//span_3-->';

		echo '</div><!--//row-->';

		$wc->eztc(
			array(
				'#control'      => 'checkbox',
				'#name'         => $this->get_field_name( 'clear_cache' ),
				'#id'           => $this->get_field_id( 'clear_cache' ),
				'#title'        => __( 'Clear Caches', 'wpraven' ),
				'#prefix'       => '<p>',
				'#suffix_title' => '<div class="tip">Use with caution!. Clears any cached query data and fetches new data.</div></p>',
				'#checked'      => isset( $instance['clear_cache'] ) ? $instance['clear_cache'] : 0,
			)
		);

	}

	/*
	 * Function to retrieve data from Google Analytics. It also checks for cache expiry.
	 * Retrieved data is stored in WP transient cache which is refreshed upon cache expiry.
	 */
	function __asrzen_get_ga_report( $args = array() ) {

		$ga_username = $ga_days_from = $ga_days_till = $ga_dimensions
			= $asrzen_results_cache = $ga_pwd = $ga_pagepaths
			= $ga_profile_id = $data_months = $ga_filter
			= $cache_lifetime = $query_post_count = $debugmode = null;

		if ( ! is_array( $args ) ) {
			return false;
		}

		//$cipher = new ASR_Zen_Cipher( self::passphrase );

		$ga_username = $args['ga_username'];
		//$ga_pwd           = $cipher->decrypt( $args['ga_pwd'] );
		$ga_profile_id      = $args['ga_profile_id'];
		$data_months        = $args['data_months'];
		$ga_filter          = html_entity_decode( base64_decode( $args['ga_filter'] ) );
		$cache_lifetime     = $args['cache_lifetime'];
		$query_post_count   = $args['query_post_count'];
		$client_secret_file = $args['client_secret_path'];
		//$debugmode        = $args['debugmode'];
		$ga_dimensions = array( 'hostname', 'pagePath' );

		if ( is_numeric( $data_months ) ):
			$ga_days_from = mktime( 0, 0, 0, date( "m" ), date( "d" ) - $data_months, date( "y" ) );
			$ga_days_from = date( 'Y-m-d', $ga_days_from );
		else:
			$ga_days_from = date( 'Y-m-d' );
		endif;

		$ga_days_till = date( 'Y-m-d' );

		if ( empty( $ga_username ) || empty( $ga_profile_id ) ) {
			throw new Exception( 'Google account data is missing or parameters are not set correctly.' );
		}

		// Check if we have data stored in WP transient cache, if TRUE then return those results
		if ( false === ( $asrzen_results_cache = get_transient( '__wpraven_ga_results_cache' ) ) ):

			// Use the developers console and replace the values with your
			// service account email, and relative location of your key file.
			// Key pwd: notasecret
			$key_file_location = $client_secret_file;

			try {
				$ga = new gapi( $ga_username, $key_file_location );

				//$ga->getToken();

				$ga->requestReportData(
					$ga_profile_id, // Profile ID
					$ga_dimensions, // Dimensions
					array( 'pageviews' ), // Metrics
					array( '-pageviews' ), // Sorting metric
					$ga_filter, // Filter expression
					$ga_days_from, // Date from
					$ga_days_till, // Date till
					1,
					$query_post_count
				);

				foreach ( $ga->getResults() as $result ) :
					$ga_pagepaths[] = $result->getPagepath();
				endforeach;

				if ( isset( $ga_pagepaths ) && ! empty( $ga_pagepaths ) && is_array( $ga_pagepaths ) ) {
					$asrzen_results_cache = serialize( $ga_pagepaths );
					set_transient( '__wpraven_ga_results_cache', $asrzen_results_cache, (int) $cache_lifetime * MINUTE_IN_SECONDS );
				} else {
					$asrzen_results_cache = false;
				}
			} catch ( Exception $e ) {
				return false;
			}


		endif;

		return $asrzen_results_cache; // Return cached or new data or false if something went wrong

	}

}

//-- Register widget
add_action( 'widgets_init', create_function( '', 'register_widget("WP_Raven_Widget_GAPopularPosts");' ) );