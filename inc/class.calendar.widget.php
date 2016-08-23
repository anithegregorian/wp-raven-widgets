<?php

/**
 * @package        class.calendar.widget.php
 * @subpackage     wp-raven
 * @author         Anirudh K. Mahant
 * @created        8/22/16 - 3:20 PM
 * @license        GNU GPL 3.0
 * @licenseurl    https://www.gnu.org/licenses/gpl-3.0.en.html
 * @desc           AJAX Calendar widget
 * @link           http://www.ravendevelopers.com
 */

if ( ! defined( 'WP_RAVEN_WIDGETS_VERSION' ) ) {
	exit( 'No direct script access allowed' );
}

class WP_Raven_Widget_Calendar extends WP_Widget {

	var $widgetdomain;

	private $query_result, $limit_year, $limit_month, $limit_day;

	public function __construct() {
		global $wpdb;

		$this->widgetdomain = load_theme_textdomain( 'wpraven', WP_RAVEN_WIDGETS_PLUGIN_LANG_DIR . '/languages' );
		$widget_ops         = array( 'classname' => 'widget_calendar', 'description' => __( 'WP Raven AJAX Calendar widget to dispaly archives' ) );
		$control_ops        = array( 'width' => 300, 'height' => 350 );

		// Get last post month and day. This is useful in limiting the calendar within possible date/month and year ranges
		$this->query_result = $wpdb->get_row( "SELECT YEAR(wp_posts.post_date) AS LYEAR, MONTH(wp_posts.post_date) AS LMONTH, DAY(wp_posts.post_date) AS LDATE FROM $wpdb->posts WHERE 1=1 AND wp_posts.post_type = 'post' AND (wp_posts.post_status = 'publish' OR wp_posts.post_status = 'private') ORDER BY wp_posts.post_date DESC LIMIT 0, 1", ARRAY_A );

		$this->limit_year = intval( $this->query_result['LYEAR'] );
		$this->limit_month = intval( $this->query_result['LMONTH'] );
		$this->limit_day = intval( $this->query_result['LDATE'] );

		// AJAX permissions
		add_action( 'wp_ajax_nopriv___wpraven_fetch_archives_by_year_month', array( $this, '__wpraven_fetch_archives_by_year_month' ) );
		add_action( 'wp_ajax___wpraven_fetch_archives_by_year_month', array( $this, '__wpraven_fetch_archives_by_year_month' ) );

		if ( ! is_admin() ) {
			add_action( 'wp_enqueue_scripts', array( $this, 'wpraven_enqueue_styles' ) );
		}

		parent::__construct( 'wpravenwidget_calendar', __( 'WP Raven Calendar' ), $widget_ops, $control_ops );
	}

	public function widget( $args, $instance ) {

		global $monthnum, $year;

		// Pass vars to views
		extract($args, EXTR_SKIP);

		// Enqueue and buffer AJAX scripts
//		self::__enqueue_calendar_ajax_scripts();
		if ( ! is_admin() ) {
			add_action( 'wp_footer', array( $this, '__enqueue_calendar_ajax_scripts' ), 100 );
		}

		echo ( isset( $args['before_widget'] ) && !empty( $args['before_widget'] ) ) ? $args['before_widget'] : "";

		if ( ! empty( $instance['title'] ) ) {
			echo $args['before_title'] . $instance['title'] . $args['after_title'];
		}

		echo ( isset( $args['before_content'] ) && !empty( $args['before_content'] ) ) ? $args['before_content'] : "";

		if ( ( isset( $monthnum ) && $monthnum > 0 ) || ( isset( $year ) && $year > 0 ) ) {
			echo self::__ewpraven_calendar( $monthnum, $year, true, false, true ); // Echo calendar
		} else {
			echo self::__ewpraven_calendar( $this->limit_month, $this->limit_year, true, false, true ); // Echo calendar
		}

		echo ( isset( $args['after_content'] ) && !empty( $args['after_content'] ) ) ? $args['after_content'] : "";

		echo ( isset( $args['after_widget'] ) && !empty( $args['after_widget'] ) ) ? $args['after_widget'] : "";

	}

	function wpraven_enqueue_styles(){
		$path = pathinfo(__DIR__);
		wp_register_style( 'wpraven-calendar-theme', plugins_url( basename($path['dirname']) . '/scss/theme.css' ), 'all' );
		wp_enqueue_style( 'wpraven-calendar-theme' ); // Enqueue it!
	}

	public function update( $new_instance, $old_instance ) {
		$instance                     = $old_instance;
		$instance['title']     = $new_instance['title'];

		return $instance;
	}

	public function form( $instance ) {

		// Defaults
		$instance = wp_parse_args( (array) $instance, array(
			'title' => '',
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
	}

	public function __enqueue_calendar_ajax_scripts(){
		$ajax_url    = WPRAVEN_AJAX_HANDLER_URL;
		$var_nonce   = wp_create_nonce( '__wpraven_fetch_archives_by_year_month' );
		$var_referer = esc_attr( $_SERVER['REQUEST_URI'] );
		$calendar_scripts = <<<EOT
		<script type="text/javascript">

			(function( wpraven, undefined ){
				'use strict';
				wpraven.isJSON = function (str) {
					try {
						JSON.parse(str);
					} catch (e) {
						return false;
					}
					return true;
				};
			})( window.wpraven = window.wpraven || {});

		(function($){
		
			$(document).ready(function(){
				var valYear, valMonth;
				var selYear = jQuery('select#archive_years_$this->id');
				var selMonth = jQuery('select#archive_months_$this->id');
				var tblCalendar = jQuery('#$this->id table tbody');
		
				// When months change update calendar
				selMonth.change(function(){
					valYear = selYear.val();
					valMonth = selMonth.val();
		
					if (valMonth > 0){
						jQuery.ajax({
							type: "get",
							url: "$ajax_url",
							cache: true,
							data:{
								action: '__wpraven_fetch_archives_by_year_month',
								year: valYear,
								month: valMonth,
								limitMonths: 0,
								_ajax_nonce: '$var_nonce',
								_wp_http_referer: '$var_referer',
							},
							beforeSend: function(){
								tblCalendar.html('<tr><td colspan=\"7\" style=\"padding: 30% 20%;\"><div class=\"spinner\"><div class=\"double-bounce1\"></div><div class=\"double-bounce2\"></div></div><\/td><\/tr>');
							},
							complete: function(){
								//selMonth.parent('li').removeClass('ajaxaction');
							},
							success: function(data){
								if (wpraven.isJSON(data)){
									var jsonData = JSON.parse(data);
									tblCalendar.html(jsonData[0]);
								} else {
									tblCalendar.html(data);
								}
								jQuery('div.widget_calendar table tbody td:not(:has(a))').addClass('nolink');
							},
							error: function(jqXHR, textStatus, errorThrown) {
							  try{console.log(textStatus + " - " + errorThrown);} catch (e) {}
							}
						});
					}
		
				});
		
				// When years change update months
				selYear.change(function(){
					valYear = selYear.val();
					valMonth = selMonth.val();
		
					if (valYear > 0){
						jQuery.ajax({
							type: "get",
							url: "$ajax_url",
							cache: true,
							data:{
								action: '__wpraven_fetch_archives_by_year_month',
								year: valYear,
								month: valMonth,
								limitMonths: 1,
								_ajax_nonce: '$var_nonce',
								_wp_http_referer: '$var_referer',
							},
							beforeSend: function(){
								selMonth.parent('li').addClass('ajaxaction');
							},
							complete: function(){
								selMonth.parent('li').removeClass('ajaxaction');
							},
							success: function(data){
								if (wpraven.isJSON(data)){
									var jsonData = JSON.parse(data);
									selMonth.html(jsonData[0]);
									tblCalendar.html(jsonData[1]);
								} else {
									tblCalendar.html(data);
								}
								jQuery('div.widget_calendar table tbody td:not(:has(a))').addClass('nolink');
							},
							error: function(jqXHR, textStatus, errorThrown) {
							  try{console.log(textStatus + " - " + errorThrown);} catch (e) {}
							}
						});
					}
		
				});
			});
			
		})(jQuery);
</script>
EOT;
		echo $calendar_scripts;
	}

	private function __ewpraven_calendar( $month = 0, $year = 0, $initial = true, $echo = false, $full = true  ){
		return str_replace( array( "\r", "\n", "\t" ), '', self::wpraven_get_calendar( $month, $year, $initial, $echo, $full ) );
	}

	/**
	 * Display calendar with days that have posts as links.
	 *
	 * The calendar is cached, which will be retrieved, if it exists. If there are
	 * no posts for the month, then it will not be displayed.
	 *
	 * @since 1.0.0
	 *
	 * @param bool $initial Optional, default is true. Use initial calendar names.
	 * @param bool $echo Optional, default is true. Set to false for return.
	 *
	 * @return string|null String when retrieving, null when displaying.
	 */
	private function wpraven_get_calendar( $rmonth = 0, $ryear = 0, $initial = true, $echo = true, $full = true ) {
		global $wpdb, $m, $monthnum, $year, $wp_locale, $posts;

		// If we already have archive browsing mode then keep original values!
		$monthnum = ( $rmonth > 0 ) ? $rmonth : $monthnum;

		// If we already have archive browsing mode then keep original values!
		$year = ( $ryear > 0 ) ? $ryear : $year;

		$previous_output = $next_output = $select_years = $select_months = $select_days = $calendar_output = null;

		$key = md5( $m . $monthnum . $year );

		if ( isset( $_GET['w'] ) ) {
			$w = '' . intval( $_GET['w'] );
		}

		// week_begins = 0 stands for Sunday
		$week_begins = intval( get_option( 'start_of_week' ) );

		// Let's figure out when we are
		if ( ! empty( $monthnum ) && ! empty( $year ) ) {
			$thismonth = '' . zeroise( intval( $monthnum ), 2 );
			$thisyear  = '' . intval( $year );
		} elseif ( ! empty( $w ) ) {
			// We need to get the month from MySQL
			$thisyear  = '' . intval( substr( $m, 0, 4 ) );
			$d         = ( ( $w - 1 ) * 7 ) + 6; //it seems MySQL's weeks disagree with PHP's
			$thismonth = $wpdb->get_var( "SELECT DATE_FORMAT((DATE_ADD('{$thisyear}0101', INTERVAL $d DAY) ), '%m')" );
		} elseif ( ! empty( $m ) ) {
			$thisyear = '' . intval( substr( $m, 0, 4 ) );
			if ( strlen( $m ) < 6 ) {
				$thismonth = '01';
			} else {
				$thismonth = '' . zeroise( intval( substr( $m, 4, 2 ) ), 2 );
			}
		} else {
			$thisyear  = gmdate( 'Y', current_time( 'timestamp' ) );
			$thismonth = gmdate( 'm', current_time( 'timestamp' ) );
		}

		$unixmonth = mktime( 0, 0, 0, $thismonth, 1, $thisyear );
		$last_day  = date( 't', $unixmonth );

		// Get the next and previous month and year with at least one post
		$previous = $wpdb->get_row( "SELECT MONTH(post_date) AS month, YEAR(post_date) AS year
		FROM $wpdb->posts
		WHERE post_date < '$thisyear-$thismonth-01'
		AND post_type = 'post' AND post_status = 'publish'
			ORDER BY post_date DESC
			LIMIT 1" );
		$next     = $wpdb->get_row( "SELECT MONTH(post_date) AS month, YEAR(post_date) AS year
		FROM $wpdb->posts
		WHERE post_date > '$thisyear-$thismonth-{$last_day} 23:59:59'
		AND post_type = 'post' AND post_status = 'publish'
			ORDER BY post_date ASC
			LIMIT 1" );

		/* translators: Calendar caption: 1: month name, 2: 4-digit year */
		$calendar_caption = _x( '%1$s %2$s', 'calendar caption' );

		$current_month = sprintf( $calendar_caption, $wp_locale->get_month( $thismonth ), date( 'Y', $unixmonth ) );

		if ( $previous ) {
			$previous_output .= "\n\t\t" . '<li class="text-left cleft"><a href="' . get_month_link( $previous->year, $previous->month ) . '">&laquo; ' . $wp_locale->get_month_abbrev( $wp_locale->get_month( $previous->month ) ) . '</a></li>';
		} else {
			$previous_output .= "\n\t\t" . '<li class="text-left cleft">&nbsp;</li>';
		}

		if ( $next ) {
			$next_output .= "\n\t\t" . '<li class="text-right cright"><a href="' . get_month_link( $next->year, $next->month ) . '">' . $wp_locale->get_month_abbrev( $wp_locale->get_month( $next->month ) ) . ' &raquo;</a></li>';
		} else {
			$next_output .= "\n\t\t" . '<li class="text-right cright">&nbsp;</li>';
		}

		if ( $full ) {
			$select_years  = self::__asrzen_get_archive_years();
			$select_months = self::__asrzen_get_archive_months( $year );

			$calendar_output = <<<EOT
<table id="wp-calendar-$this->id" class="wp-raven-calendar">
	<caption>
		<ul class="cal-params">
			<li><select name="archive_years" id="archive_years_$this->id">$select_years</select></li>
			<li>
				<div class="doingajax"><i class="fa fa-cog fa-spin"></i></div>
				<select name="archive_months" id="archive_months_$this->id">$select_months</select>
			</li>
		</ul>
</caption>
	<thead>
	<tr>
EOT;
			$myweek          = array();

			for ( $wdcount = 0; $wdcount <= 6; $wdcount ++ ) {
				$myweek[] = $wp_locale->get_weekday( ( $wdcount + $week_begins ) % 7 );
			}

			foreach ( $myweek as $wd ) {
				$day_name = ( true == $initial ) ? $wp_locale->get_weekday_initial( $wd ) : $wp_locale->get_weekday_abbrev( $wd );
				$wd       = esc_attr( $wd );
				$calendar_output .= "\n\t\t<th scope=\"col\" title=\"$wd\">$day_name</th>";
			}

			$calendar_output .= '
	</tr>
	</thead>
	<tbody>';
		}

		$calendar_output .= '<tr>';

		$daywithpost = array();

		// Get days with posts
		$dayswithposts = $wpdb->get_results( "SELECT DISTINCT DAYOFMONTH(post_date)
		FROM $wpdb->posts WHERE post_date >= '{$thisyear}-{$thismonth}-01 00:00:00'
		AND post_type = 'post' AND post_status = 'publish'
		AND post_date <= '{$thisyear}-{$thismonth}-{$last_day} 23:59:59'", ARRAY_N );
		if ( $dayswithposts ) {
			foreach ( (array) $dayswithposts as $daywith ) {
				$daywithpost[] = $daywith[0];
			}
		}

		// See how much we should pad in the beginning
		$pad = calendar_week_mod( date( 'w', $unixmonth ) - $week_begins );
		if ( 0 != $pad ) {
			$calendar_output .= "\n\t\t" . '<td colspan="' . esc_attr( $pad ) . '" class="pad">&nbsp;</td>';
		}

		$daysinmonth = intval( date( 't', $unixmonth ) );
		for ( $day = 1; $day <= $daysinmonth; ++ $day ) {
			if ( isset( $newrow ) && $newrow ) {
				$calendar_output .= "\n\t</tr>\n\t<tr>\n\t\t";
			}
			$newrow = false;

			if ( $day == gmdate( 'j', current_time( 'timestamp' ) ) && $thismonth == gmdate( 'm', current_time( 'timestamp' ) ) && $thisyear == gmdate( 'Y', current_time( 'timestamp' ) ) ) {
				$calendar_output .= '<td id="today">';
			} else {
				$calendar_output .= '<td>';
			}

			if ( in_array( $day, $daywithpost ) ) // any posts today?
			{
				$calendar_output .= '<a href="' . get_day_link( $thisyear, $thismonth, $day ) . '">' . $day . "</a>";
			} else {
				$calendar_output .= $day;
			}
			$calendar_output .= '</td>';

			if ( 6 == calendar_week_mod( date( 'w', mktime( 0, 0, 0, $thismonth, $day, $thisyear ) ) - $week_begins ) ) {
				$newrow = true;
			}
		}

		$pad = 7 - calendar_week_mod( date( 'w', mktime( 0, 0, 0, $thismonth, $day, $thisyear ) ) - $week_begins );
		if ( $pad != 0 && $pad != 7 ) {
			$calendar_output .= "\n\t\t" . '<td class="pad" colspan="' . esc_attr( $pad ) . '">&nbsp;</td>';
		}

		$calendar_output .= "\n\t</tr>\n\t</tbody>\n\t</table>";

		if ( $echo ) {
			/**
			 * Filter the HTML calendar output.
			 *
			 * @since 3.0.0
			 *
			 * @param string $calendar_output HTML output of the calendar.
			 */
			echo apply_filters( 'wpraven_get_calendar', $calendar_output );
		}

		return apply_filters( 'wpraven_get_calendar', $calendar_output );
	}

	private function __asrzen_get_archive_years() {
		global $wpdb, $year;

		$ryears  = $output = null;

		$ryears  = $wpdb->get_col( "SELECT DISTINCT YEAR(post_date) FROM $wpdb->posts ORDER BY post_date" );
		$output = sprintf( '<option value="%1$s">%2$s</option>', '-1', _x( '- Select Year -', 'year calendar option none', 'asr' ) );
		foreach ( $ryears as $ryear ) {
			if ( is_year() || is_month() || is_day() ){
				$output .= sprintf( '<option %1$s value="%2$s">%3$s</option>', selected( $year, $ryear, false ), $ryear, $ryear );
			} else {
				$output .= sprintf( '<option %1$s value="%2$s">%3$s</option>', selected( $this->limit_year, $ryear, false ), $ryear, $ryear );
			}

		}

		return $output;
	}

	private function __asrzen_get_archive_months( $ryear = 0, $prev_selected_month = 0 ) {
		global $wpdb, $monthnum;
		$months = $selected_month = $output = null;
		$months = array(
			1  => "January",
			2  => "February",
			3  => "March",
			4  => "April",
			5  => "May",
			6  => "June",
			7  => "July",
			8  => "August",
			9  => "September",
			10 => "October",
			11 => "November",
			12 => "December"
		);

		$output = sprintf( '<option value="%1$s">%2$s</option>', '-1', _x( '- Select Month -', 'month calendar option none', 'asr' ) );

		foreach ( $months as $value => $month ) {

			if ( is_year() || is_month() || is_day() ) {
				$selected_month = selected( $monthnum, $value, false );
			} else if ( ! ( is_year() || is_month() || is_day() ) ) {
				if ( $prev_selected_month > 0 ) {
					$selected_month = selected( $prev_selected_month, $value, false );
				}
				if ( $value <= 1 ) {
					$selected_month = selected( $value, $this->limit_month, false );
				}
			}

			if ( isset( $selected_month ) && ! empty( $selected_month ) ) {
				$output .= sprintf( '<option %1$s value="%2$s">%3$s</option>', $selected_month, $value, $month );
			} else {
				$output .= sprintf( '<option value="%1$s">%2$s</option>', $value, $month );
			}

			if ( ( $value == $this->limit_month && $ryear == $this->limit_year ) ) {
				break;
			}

		}
		return $output;
	}

	public function __wpraven_fetch_archives_by_year_month(){
		check_ajax_referer( '__wpraven_fetch_archives_by_year_month' );

		if ( isset( $_REQUEST['_ajax_nonce'] ) && ! wp_verify_nonce( $_REQUEST['_ajax_nonce'], '__wpraven_fetch_archives_by_year_month' ) ) {
			return '';
		}

		// Sanity checks; Don't even bother to go further if these fail
		if ( empty( $_REQUEST['year'] ) && empty( $_REQUEST['month'] ) ) {
			die( __( 'Error: No valid year, month or day!', 'asr' ) );
		}

		// Check if we've got valid year and month
		if ( $_REQUEST['year'] == $this->limit_year && $_REQUEST['month'] > $this->limit_month  ){
			echo json_encode(
				array(
					self::__asrzen_get_archive_months( $_REQUEST['year'], $this->limit_month ),
					self::__ewpraven_calendar( $this->limit_month, intval($_REQUEST['year']), true, false, false )
				)
			);
			die();
		}

		if ( ( isset( $_REQUEST['limitMonths'] ) && (bool)$_REQUEST['limitMonths'] == true ) ) {
			echo json_encode(
				array(
					self::__asrzen_get_archive_months( $_REQUEST['year'], $_REQUEST['month'] ),
					self::__ewpraven_calendar( intval($_REQUEST['month']), intval($_REQUEST['year']), true, false, false )
				)
			);
		} else if ( ( isset( $_REQUEST['limitMonths'] ) && (bool)$_REQUEST['limitMonths'] == false ) ) {
			echo json_encode(
				array(
					self::__ewpraven_calendar( intval($_REQUEST['month']), intval($_REQUEST['year']), true, false, false )
				)
			);
		}
		die();
	}

}

//-- Register widget
add_action( 'widgets_init', create_function( '', 'register_widget("WP_Raven_Widget_Calendar");' ) );