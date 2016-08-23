<?php
/**
 * @package        view.ga.popular.posts.php
 * @subpackage     asrzen
 * @author         Anirudh Sethi
 * @created        7/29/15 - 3:43 PM
 * @license        Creative Commons 3.0 Attribution
 * @licenseurl    https://creativecommons.org/licenses/by/3.0/us/
 * @desc           GAPI popular posts widget
 * @link           http://www.anirudhsethireport.com
 */

if ( ! defined( 'WP_RAVEN_WIDGETS_VERSION' ) ) {
	exit( 'No direct script access allowed' );
}

extract( $instance );

$post_url = $post_title = $post_time = null;

// If we found results and it wasn't a false then display results
if ( isset( $results_cache ) && ! empty( $results_cache ) && ( $results_cache != false ) ) {
	$results_cache = unserialize( $results_cache );
	$results_count = 0;

	if ( $debugmode ) {
		echo '<code>'. __( 'Debug Mode is ON. This data will not be displayed once you set Debug Mode off', 'wpraven' ) . '</code>';
		var_dump(array_slice($results_cache, 0, $show_post_count));
		return false;
	}

	// If we got an array of results go through each result and prepare links for display ;)
	if ( is_array( $results_cache ) ){
		echo '<ol class="posts-listing boxed-counter">';
		foreach ($results_cache as $result){
			$post = get_page_by_path( basename( untrailingslashit( $result ) ) , ARRAY_A, 'post' );

			if ( isset( $post ) && isset( $post['ID'] ) && $results_count < $show_post_count ){
				$post_url = get_permalink( $post['ID'] );
				$post_title = wp_trim_words( get_the_title( $post['ID'] ), $trim_title );
				$post_time = sprintf( _x( '%s ago', '%s = human-readable time difference', 'wpraven' ), human_time_diff( get_the_time( 'U', $post['ID'] ), current_time( 'timestamp' ) ) );
				echo <<<HTML
	<li>
		<h4><a href="$post_url">$post_title</a></h4>
		<time>$post_time</time>
	</li>
HTML;
				$results_count++;
			} else {
				continue;
			}

		}
		echo '</ol>';
	}

} elseif ( $results_cache === false ) {
	echo "<abbr class='signal'>" . __( "Couldn't find any Popular Posts! Duh!", 'wpraven' ) . "</abbr>";
}