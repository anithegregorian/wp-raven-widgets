<?php
/**
 * @package        zen-theme-controls.php
 * @subpackage     wp-raven
 * @author         Anirudh K. Mahant
 * @created        8/22/16 - 3:20 PM
 * @license        GNU GPL 3.0
 * @licenseurl    https://www.gnu.org/licenses/gpl-3.0.en.html
 * @desc           Zen Theme Controls - A small library to render HTML form elements with labels
 * @link           http://www.ravendevelopers.com
 */

if ( ! defined( 'ZEN_CONTROLS_PREFIX' ) ) {
	define( 'ZEN_CONTROLS_PREFIX', '' );
}

if ( ! class_exists( 'Zen_Theme_Controls' ) ) {

	class Zen_Theme_Controls {

		function __construct(){
			// Nothing
		}

		/**
		 * Creates a theme control for use under Theme Settings.
		 *
		 * Based on $args theme control is rendered and returned upon function call. See below for list of options:
		 * #control            ->  An array type from one of the predefined control types. (array)
		 * #name and #id       ->  Specify name and id of the control (its important to name them uniquely). Theme settings uses this names to save and restore information. (string)
		 * #class              ->  Give a class name to control for theming. (string)
		 * #title              ->  Is used to create a label for accompanying control. (string)
		 * #value              ->  The value for control can be specified here (for text, radio and textarea only). Upon restoring the setting this function uses the same argument. (string)
		 * #rows and #cols     ->  Used to specify rows and cols for textarea. (string)
		 * #height and #width  ->  Used to specify height and width for control. (string)
		 * #checked            ->  Used to specify checked value for checkbox control. (string)
		 * #multiple           ->  Creates a multiple selection list box. Specify 'multiple' without quotes (string)
		 * #default_value      ->  If you do not specify #value then #default_value is used (string)
		 * #size               ->  For text input type (string)
		 * #options            ->  Not implemented yet (array)
		 * #selected           ->  For checkbox (int)
		 * #allowhtml          ->  For textbox and textarea, not fully implemented but should work (int)
		 *
		 *
		 * @var $args mixed Can be an array containing control type, control name, control id, control class, control title etc..
		 * @return Fully rendered theme control in HTML.
		 * @author Anirudh K. Mahant
		 * @todo Needs more work!
		 */
		public static function zen_theme_control( $args = array() ) {

			//-- Defaults
			$defaults = array(
				'#control'         => array(
					'text',
					'number',
					'password',
					'url',
					'select',
					'radio',
					'checkbox',
					'textarea',
					'submit',
					'wp_dropdown_posts',
					'wp_dropdown_post_types',
					'wp_dropdown_categories',
					'wp_dropdown_pages',
					'ngg_gallery_combo',
					'ngg_album_combo',
					'wpnavmenu'
				),
				'#name'            => '',
				'#id'              => '',
				'#prefix'          => '',
				'#suffix'          => '',
				'#prefix_title'    => '',
				'#suffix_title'    => '',
				'#class'           => 'xx_input',
				'#title'           => '',
				'#value'           => '',
				'#rows'            => '5',
				'#cols'            => '80',
				'#height'          => '',
				'#width'           => '',
				'#multiple'        => false,
				'#checked'         => '',
				'#default_value'   => '',
				'#taxonomy'        => 'category',
				'#size'            => '25',
				'#options'         => array(),
				'#selected'        => - 1,
				'#allowhtml'       => false,
				'#base64encode'       => false,
				'#tinymce'         => false,
				'#settings_prefix' => true,
				'#required' => false,
				'#shownone' => false,
				'#number' => array(
					'min' => -1,
					'max' => -1,
				)
			);

			$r = wp_parse_args( $args, $defaults );

			if ( $r['#settings_prefix'] ) {
				$settings_prefix = ZEN_CONTROLS_PREFIX;
			} else {
				$settings_prefix = '';
			}

			$tc            = $r['#control'];
			$tc_allow_html = $r['#allowhtml'];
			$base64encode = $r['#base64encode'];
			$shownone = $r['#shownone'];

			if ( $base64encode ):
				$tc_value = ( ! empty( $r['#value'] ) ) ? $r['#value'] : $r['#value'];
			elseif ( $tc_allow_html ):
				$tc_value = ( ! empty( $r['#value'] ) ) ? stripslashes( html_entity_decode( $r['#value'] ) ) : stripslashes( html_entity_decode( $r['#default_value'] ) );
			else:
				$tc_value = ( ! empty( $r['#value'] ) ) ? @stripslashes( wp_kses_normalize_entities( $r['#value'] ) ) : $r['#default_value'];
			endif;

			$tc_size = ( ! empty( $r['#size'] ) ) ? $r['#size'] : '';

			$tc_instance_class = 'xx-instance-' . mt_rand();

			$tc_name  = ( ! empty( $r['#name'] ) ) ? $settings_prefix . $r['#name'] : 'xx-control-' . mt_rand();
			$tc_class = ( ! empty( $r['#class'] ) ) ? 'xx-input xx-input-' . $r['#control'] . ' ' . $r['#class'] . ' ' . $tc_instance_class : $tc_name;
			$tc_id    = ( ! empty( $r['#id'] ) ) ? $settings_prefix . $r['#id'] : $tc_name;

			$tc_height = ( ! empty( $r['#height'] ) ) ? ' height: ' . $r['#height'] . ';' : '';
			$tc_width  = ( ! empty( $r['#width'] ) ) ? ' width: ' . $r['#width'] . ';' : '';
			$tc_styles = $tc_height . $tc_width;

			$tc_checked = ( ! empty( $r['#checked'] ) && $r['#checked'] == 'on' ) ? true : false;

			$tc_label = ( ! empty( $r['#title'] ) ) ? '<label class="xx-label xx-label-' . $tc . '" for="' . $tc_id . '">' . $r['#title'] . '</label>' : '';

			$tc_is_multiple = ( ! empty( $r['#multiple'] ) ) ? $r['#multiple'] : '';
			$tc_selected    = ( ! empty( $r['#selected'] ) ) ? $r['#selected'] : - 1;

			$tc_taxonomy = ( ! empty( $r['#taxonomy'] ) ) ? $r['#taxonomy'] : 'category';

			$tc_tinymce = $r['#tinymce'];
			$tc_rows    = ( ! empty( $r['#rows'] ) ) ? $r['#rows'] : '';
			$tc_cols    = ( ! empty( $r['#cols'] ) ) ? $r['#cols'] : '';

			$tc_prefix = ( ! empty( $r['#prefix'] ) ) ? $r['#prefix'] : '';
			$tc_suffix = ( ! empty( $r['#suffix'] ) ) ? $r['#suffix'] : '';

			$tc_prefix_title = ( ! empty( $r['#prefix_title'] ) ) ? $r['#prefix_title'] : '';
			$tc_suffix_title = ( ! empty( $r['#suffix_title'] ) ) ? $r['#suffix_title'] : '';

			$tc_required = ( ! empty( $r['#required'] ) ) ? $r['#required'] : '';

			$tc_js     = '';
			$tc_output = '';

			switch ( $tc ):

				case "text": /* Textbox */

					$tc_output = $tc_prefix_title . $tc_label . $tc_suffix_title . "\n";

					$tc_output .= sprintf(
						'%s<input type="text" name="%s" class="%s" id="%s" value="%s" style="%s" size="%s" />%s',
						$tc_prefix, $tc_name, $tc_class, $tc_id, esc_attr( $tc_value ), $tc_styles, $tc_size, $tc_suffix
					);

					break;

				case "number": /* Number */

					$tc_min = ( ! empty( $r['#number']['min'] ) ) ? $r['#number']['min'] : -1;
					$tc_max = ( ! empty( $r['#number']['max'] ) ) ? $r['#number']['max'] : -1;

					$tc_output = $tc_prefix_title . $tc_label . $tc_suffix_title . "\n";

					$tc_output .= sprintf(
						'%s<input type="number" name="%s" class="%s" id="%s" value="%s" style="%s" size="%s" min="%s" max="%s" />%s',
						$tc_prefix, $tc_name, $tc_class, $tc_id, esc_attr( $tc_value ), $tc_styles, $tc_size, $tc_min, $tc_max, $tc_suffix
					);

					break;

				case "password": /* Password */

					$tc_output = $tc_prefix_title . $tc_label . $tc_suffix_title . "\n";

					$tc_output .= sprintf(
						'%s<input type="password" name="%s" class="%s" id="%s" value="%s" style="%s" size="%s" />%s',
						$tc_prefix, $tc_name, $tc_class, $tc_id, esc_attr( $tc_value ), $tc_styles, $tc_size, $tc_suffix
					);

					break;

				case "url": /* URL */

					$tc_output = $tc_prefix_title . $tc_label . $tc_suffix_title . "\n";

					$tc_output .= sprintf(
						'%s<input type="url" name="%s" class="%s" id="%s" value="%s" style="%s" size="%s" />%s',
						$tc_prefix, $tc_name, $tc_class, $tc_id, esc_attr( $tc_value ), $tc_styles, $tc_size, $tc_suffix
					);

					break;

				case "radio": /* Radio button */

					$tc_output = $tc_prefix_title . $tc_label . $tc_suffix_title . "\n";

					$tc_output .= sprintf(
						'%s<input type="radio" name="%s" class="%s" id="%s" value="%s" style="%s" size="%s" />%s',
						$tc_prefix, $tc_name, $tc_class, $tc_id, esc_attr( $tc_value ), $tc_styles, $tc_size, $tc_suffix
					);

					break;

				case "checkbox": /* Checkbox */

					$tc_output = sprintf(
						'%s<input type="checkbox" name="%s" class="%s" id="%s" style="%s"' . checked( $tc_checked, true, false ) . ' />%s',
						$tc_prefix, $tc_name, $tc_class, $tc_id, $tc_styles, $tc_suffix
					);

					$tc_output = $tc_output . "\n" . $tc_prefix_title . $tc_label . $tc_suffix_title . "\n";

					break;

				case "submit": /* Submit Button */

					$tc_output = $tc_prefix_title . $tc_label . $tc_suffix_title . "\n";

					$tc_output .= sprintf(
						'%s<input type="submit" name="%s" class="%s" id="%s" value="%s" style="%s" />%s',
						$tc_prefix, $tc_name, $tc_class, $tc_id, esc_attr( $tc_value ), $tc_styles, $tc_suffix
					);

					break;

				case "wp_dropdown_categories": /* Wordpress Dropdown Categories */

					$tc_output = $tc_prefix_title . $tc_label . $tc_suffix_title . "\n";

					$tc_name = ( $tc_is_multiple ) ? $tc_name . '[]' : $tc_name;

					$wp_ddc_defaults = array(
						'show_option_all'  => '',
						'show_option_none' => 'Select Categorie(s)',
						'orderby'          => 'name',
						'order'            => 'ASC',
						'show_count'       => 0,
						'hide_empty'       => 0,
						'echo'             => 0,
						'selected'         => $tc_selected,
						'hierarchical'     => 1,
						'name'             => $tc_name,
						'id'               => $tc_id,
						'class'            => $tc_class,
						'depth'            => 0,
						'tab_index'        => 0,
						'taxonomy'         => $tc_taxonomy,
						'hide_if_empty'    => false
					);


					$tc_output .= $tc_prefix . wp_dropdown_categories( $wp_ddc_defaults ) . $tc_suffix;
					break;

				case "wp_dropdown_pages": /* Wordpress Dropdown Pages */

					$tc_output = $tc_prefix_title . $tc_label . $tc_suffix_title . "\n";

					$tc_name = ( $tc_is_multiple ) ? $tc_name . '[]' : $tc_name;

					$wp_ddp_defaults = array(
						'depth'                 => 0,
						'child_of'              => 0,
						'selected'              => $tc_selected,
						'echo'                  => 0,
						'name'                  => $tc_name,
						'show_option_none'      => 'Select Page(s)',
						'show_option_no_change' => '',
						'class'                 => $tc_class
					);

					$tc_output .=  $tc_prefix . wp_dropdown_pages( $wp_ddp_defaults ) . $tc_suffix;
					break;

				case "wp_dropdown_posts":

					global $post;

					$args = array();

					$args = array(
						'post_type'      => 'any',
						'posts_per_page' => - 1,
						'orderby'        => 'ID',
						'order'          => 'ASC',
					);

					$tc_output = $tc_prefix_title . $tc_label . $tc_prefix_title . "\n";

					if ( $tc_is_multiple ) {
						$tc_output .= sprintf(
							'%s<select name="%s[]" class="%s" id="%s" multiple="%s" style="%s">',
							$tc_prefix, $tc_name, $tc_class, $tc_id, $tc_is_multiple, $tc_styles );
					} else {
						$tc_output .= sprintf( '%s<select name="%s[]" class="%s" id="%s" style="%s">', $tc_prefix, $tc_name, $tc_class, $tc_id, $tc_styles );
					}

					$tc_output .= "\n" . '<option value="-1">' . __( 'Select Post(s)' ) . '</option>' . "\n";

					$tco_loop = new WP_Query( $args );

					while ( $tco_loop->have_posts() ) : $tco_loop->the_post();
						if ( in_array( $post->ID, $tc_selected ) ) {
							$selected = selected( $post->ID, $post->ID, false );
						} else {
							$selected = '';
						}
						$tc_output .= '<option value="' . $post->ID . '"' . $selected . '>' . esc_attr( $post->post_title ) . '</option>' . "\n";
					endwhile; // End the loop. Whew.
					wp_reset_query();

					$tc_output .= '</select>' . $tc_suffix;

					return $tc_output;

				case "wp_dropdown_post_types":

					$tc_output = $tc_prefix_title . $tc_label . $tc_prefix_title . "\n";

					if ( $tc_is_multiple ) {
						$tc_output .= sprintf(
							'%s<select name="%s[]" class="%s" id="%s" multiple="%s" style="%s">',
							$tc_prefix, $tc_name, $tc_class, $tc_id, $tc_is_multiple, $tc_styles );
					} else {
						$tc_output .= sprintf(
							'%s<select name="%s[]" class="%s" id="%s" style="%s">',
							$tc_prefix, $tc_name, $tc_class, $tc_id, $tc_styles );
					}

					$tc_output .= "\n" . '<option value="-1">' . __( 'Select Post Type(s)' ) . '</option>' . "\n";

					$post_types = get_post_types( '', 'objects' );

					foreach ( $post_types as $key => $post_type ):
						if ( in_array( (string) $key, $tc_selected ) ) {
							$selected = selected( (string) $key, (string) $key, false );
						} else {
							$selected = '';
						}
						$tc_output .= '<option value="' . (string) $key . '"' . $selected . '>' . esc_attr( $post_type->labels->name ) . '</option>' . "\n";
					endforeach;

					$tc_output .= '</select>' . $tc_suffix;

					break;

				case "select":

					$tc_output = $tc_prefix_title . $tc_label . $tc_suffix_title . "\n";

					if ( $tc_is_multiple ) {
						$tc_output .= sprintf(
							'%s<select name="%s[]" class="%s" id="%s" multiple="%s" style="%s">',
							$tc_prefix, $tc_name, $tc_class, $tc_id, $tc_is_multiple, $tc_styles );
					} else {
						$tc_output .= sprintf(
							'%s<select name="%s[]" class="%s" id="%s" style="%s">',
							$tc_prefix, $tc_name, $tc_class, $tc_id, $tc_styles );
					}

					if ( $shownone ) $tc_output .= "\n" . '<option value="-1">' . __( '- None -' ) . '</option>' . "\n";

					if ( is_array( $r['#options'] ) ):
						foreach ( $r['#options'] as $opt => $value ):
							$tc_output .= '<option value="' . $opt . '"' . selected( $opt, $tc_selected, false ) . '>' . esc_attr( $value ) . '</option>' . "\n";
						endforeach;
					endif;

					$tc_output .= '</select>' . $tc_suffix;

					break;

				case "wpnavmenu":

					$tc_output = $tc_prefix_title . $tc_label . $tc_suffix_title . "\n";

					if ( $tc_is_multiple ) {
						$tc_output .= sprintf(
							'%s<select name="%s[]" class="%s" id="%s" multiple="%s" style="%s">',
							$tc_prefix, $tc_name, $tc_class, $tc_id, $tc_is_multiple, $tc_styles );
					} else {
						$tc_output .= sprintf(
							'%s<select name="%s[]" class="%s" id="%s" style="%s">',
							$tc_prefix, $tc_name, $tc_class, $tc_id, $tc_styles );
					}

					if ( $shownone ) $tc_output .= "\n" . '<option value="-1">' . __( '- None -' ) . '</option>' . "\n";

					foreach ( wp_get_nav_menus() as $menu ):
						$tc_output .= sprintf(
							'<option value="%1$s" %2$s>%3$s</option>',
							esc_attr( $menu->term_id ), selected( $tc_selected, $menu->term_id, false ), esc_html( $menu->name )
						);
					endforeach;

					$tc_output .= '</select>' . $tc_suffix;

					break;

				case 'ngg_gallery_combo':
					if ( class_exists( 'nggdb' ) ):
						$ngg        = new nggdb();
						$tc_output  = $tc_prefix_title . $tc_label . $tc_suffix_title . $tc_prefix . '<select name="' . $tc_name . '[]" class="' . $tc_class . '" id="' . $tc_id . '"' . $tc_is_multiple . '"' . $tc_styles . '>';
						$saved_arry = _xgto( $r['#name'] );
						if ( ! empty( $saved_arry ) & ( is_array( $saved_arry ) ) ) {
							$saved_value = $saved_arry[0];
						}
						$tc_output .= '<option value="-1">Select a Gallery</option>';
						$thegallery = $ngg->find_all_galleries();
						foreach ( $thegallery as $xgallery ):
							$tc_selected = ( $xgallery->gid == $saved_value ) ? ' selected="selected"' : '';
							$tc_output .= '<option value="' . $xgallery->gid . '"' . $tc_selected . '>' . esc_attr( $xgallery->name ) . '</option>';
						endforeach;
						$tc_output .= '</select>' . $tc_suffix;
					endif;
					break;

				case "textarea":

					$tc_output = $tc_prefix_title . $tc_label . $tc_suffix_title . "\n";

					if ( $tc_tinymce ):
						$tc_class = $tc_class . ' ' . $tc_id . ' theEditor';
						// TinyMCE init settings
						$initArray = array(
							"editor_selector" => $tc_id
						);
						wp_editor( true, $initArray );
					endif;
					$tc_output .= sprintf(
						'%s<textarea name="%s" class="%s" id="%s" style="%s" rows="%s" cols="%s">%s</textarea>%s',
						$tc_prefix, $tc_name, $tc_class, $tc_id, $tc_styles, $tc_rows, $tc_cols, $tc_value, $tc_suffix
					);
					break;

				default:
					break;

			endswitch;

			if ( ! empty( $tc_js ) ):
				return $tc_output . '<script type="text/javascript">jQuery(document).ready(function(){' . $tc_js . '});</script>';
			else:
				return $tc_output;
			endif;
		}

		// Shortcut for zen_theme_control() with echo
		public static function eztc( $args = array() ){
			if ($args) echo (string)( self::zen_theme_control( $args ) );
		}
	}
}