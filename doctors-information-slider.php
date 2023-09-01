<?php
/**
 * Plugin Name: Doctors Information Slider
 * Plugin URI: https://github.com/nisarul-dev/doctors-information-slider
 * Description: A easy lite plugin to show Doctors Information in a slider in organized manner, Use the shortcode [doctors-slider] & Enjoy!
 * Version: 1.0.0
 * Author: Nisarul
 * Author URI: https://www.nisarul.com
 * License: GPLv3 or later
 * License URI: https://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain: doctors-information-slider
 */

/*
This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 2 of the License, or
any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with This program. If not, see https://www.gnu.org/licenses/gpl-3.0.html.
*/

defined( 'ABSPATH' ) || exit; // Prevent direct access

class Doctors_Information_Slider {
	/**
	 * Class constructor.
	 */
	public function __construct() {
		/**
		 * Including Libraries and Files
		 */
		if ( file_exists( __DIR__ . '/libs/cmb2/init.php' ) ) {
			require_once __DIR__ . '/libs/cmb2/init.php';
		}
		if ( file_exists( __DIR__ . '/libs/metabox-config.php' ) ) {
			require_once __DIR__ . '/libs/metabox-config.php';
		}

		/**
		 * Action Hooks
		 */
		add_action( 'wp_enqueue_scripts', array( $this, 'dis_theme_name_scripts' ) );
		add_action( 'init', array( $this, 'dis_shortcodes_init' ) );
		add_action( 'init', array( $this, 'dis_doctors_slider_post_type' ) );
		add_action( 'init', array( $this, 'dis_specializations_taxonomy' ) );
		add_action( 'pre_get_posts', array( $this, 'dis_custom_post_type_admin_sort' ) );

		/**
		 * Filter Hooks
		 */
		add_filter( 'enter_title_here', array( $this, 'dis_title_place_holder' ), 20, 2 );
	}

	/**
	 * Enqueue scripts and styles
	 */
	public function dis_theme_name_scripts() {
		$plugin_dir_url = plugin_dir_url( __FILE__ );
		// CSS
		wp_enqueue_style( 'dis-owl-carousel', $plugin_dir_url . 'assets/css/owl.carousel.min.css', array(), '1.0.0', 'all' );
		wp_enqueue_style( 'dis-owl-theme', $plugin_dir_url . 'assets/css/owl.theme.default.min.css', array( 'dis-owl-carousel' ), '1.0.0', 'all' );
		wp_enqueue_style( 'dis-main-style', $plugin_dir_url . 'assets/css/style.css', array( 'dis-owl-carousel', 'dis-owl-theme' ), '1.0.0', 'all' );

		// JS
		wp_enqueue_script( 'dis-owl-script', $plugin_dir_url . 'assets/js/owl.carousel.min.js', array( 'jquery' ), '1.0.0', true );
		wp_enqueue_script( 'dis-main-script', $plugin_dir_url . 'assets/js/main-script.js', array( 'jquery', 'dis-owl-script' ), '1.0.0', true );
	}

	/**
	 * The [doctors-slider] shortcode.
	 *
	 * Displays the current year.
	 *
	 * @param array  $atts    Shortcode attributes. Default empty.
	 * @param string $content Shortcode content. Default null.
	 * @param string $tag     Shortcode tag (name). Default empty.
	 * @return string Shortcode output.
	 */
	public function dis_doctors_slider_shortcode( $atts = [], $content = null, $tag = '' ) {
		$prefix = '_dis_';
		$query = new WP_Query( array(
			'post_type' => 'doctors_slider',
			'posts_per_page' => '-1',
			'order' => 'ASC',
			'orderby' => 'title',
		) );

		$output = '<div class="owl-carousel owl-theme">';

		if ( $query->have_posts() ) {
			while ( $query->have_posts() ) {
				$query->the_post();

				$output .= '<div class="item">';
				$output .= '<div class="circular-image">';
				$output .= ( get_the_post_thumbnail() != "" ) ? get_the_post_thumbnail() : '<img src="' . esc_attr( plugin_dir_url( __FILE__ ) ) . 'img/avatar.webp" alt="no image"/>';
				$output .= '</div>
							<table>
								<tr>
									<th>' . __( 'Name', 'doctors-information-slider' ) . '</th>
									<td class="colon">:</td>
									<td>' . esc_html( get_the_title() );
				$term_obj_list = get_the_terms( get_the_ID(), 'specialization' );
				$terms_string = join( ', ', wp_list_pluck( $term_obj_list, 'name' ) );
				$output .= '</td>
								</tr>
								<tr>
									<th>' . __( 'Specialty', 'doctors-information-slider' ) . '</th>
									<td class="colon">:</td>
									<td>' . esc_html( $terms_string );

				$output .= '</td>
								</tr>
								<tr>
									<th>' . __( 'Age', 'doctors-information-slider' ) . '</th>
									<td class="colon">:</td>
									<td>' . esc_html( get_post_meta( get_the_ID(), $prefix . 'date_of_birth', true ) );
				$output .= '</td>
								</tr>
								<tr>
									<th>' . __( 'Degree', 'doctors-information-slider' ) . '</th>
									<td class="colon">:</td>
									<td>' . esc_html( get_post_meta( get_the_ID(), $prefix . 'degree', true ) );
				$output .= '</td>
								</tr>
								<tr>
									<th>' . __( 'Chamber', 'doctors-information-slider' ) . '</th>
									<td class="colon">:</td>
									<td>' . esc_html( get_post_meta( get_the_ID(), $prefix . 'chamber', true ) );
				$output .= '</td></tr></table></div>';

			}
		}
		wp_reset_postdata();

		$output .= '</div>';

		return $output;
	}

	/**
	 * Central location to create all shortcodes.
	 */
	public function dis_shortcodes_init() {
		if ( ! shortcode_exists( 'year' ) ) {
			add_shortcode( 'doctors-slider', array( $this, 'dis_doctors_slider_shortcode' ) );
		}
	}

	/**
	 * Custom Post Type: Doctors Slider
	 */
	public function dis_doctors_slider_post_type() {
		$labels = array(
			'name' => _x( 'Doctors Slider', 'Post type general name', 'doctors-information-slider' ),
			'singular_name' => _x( 'Doctor', 'Post type singular name', 'doctors-information-slider' ),
			'menu_name' => _x( 'Doctors Slider', 'Admin Menu text', 'doctors-information-slider' ),
			'add_new' => __( 'Add New', 'doctors-information-slider' ),
			'add_new_item' => __( 'Add New Doctor', 'doctors-information-slider' ),
			'edit_item' => __( 'Edit Doctor', 'doctors-information-slider' ),
			'new_item' => __( 'New Doctor', 'doctors-information-slider' ),
			'view_item' => __( 'View Doctor', 'doctors-information-slider' ),
			'search_items' => __( 'Search Doctors', 'doctors-information-slider' ),
			'not_found' => __( 'No doctors found', 'doctors-information-slider' ),
			'not_found_in_trash' => __( 'No doctors found in Trash', 'doctors-information-slider' ),
		);

		$args = array(
			'labels' => $labels,
			'menu_icon' => 'dashicons-businessman',
			'public' => true,
			'publicly_queryable' => false,
			'query_var' => true,
			'rewrite' => array( 'slug' => 'doctors_slider' ),
			'capability_type' => 'post',
			'has_archive' => false,
			'hierarchical' => false,
			'menu_position' => 30,
			'supports' => array( 'title', 'thumbnail' ),
		);

		// Register the custom post type
		register_post_type( 'doctors_slider', $args );
	}

	/**
	 * Custom Taxonomy: Specializations (Doctors Slider)
	 */
	public function dis_specializations_taxonomy() {
		$labels = array(
			'name' => _x( 'Specializations', 'Taxonomy general name', 'doctors-information-slider' ),
			'singular_name' => _x( 'Specialization', 'Taxonomy singular name', 'doctors-information-slider' ),
			'menu_name' => _x( 'Specializations', 'Taxonomy Menu text', 'doctors-information-slider' ),
			'search_items' => __( 'Search Specializations', 'doctors-information-slider' ),
			'all_items' => __( 'All Specializations', 'doctors-information-slider' ),
			'parent_item' => __( 'Parent Specialization', 'doctors-information-slider' ),
			'parent_item_colon' => __( 'Parent Specialization:', 'doctors-information-slider' ),
			'edit_item' => __( 'Edit Specialization', 'doctors-information-slider' ),
			'update_item' => __( 'Update Specialization', 'doctors-information-slider' ),
			'add_new_item' => __( 'Add New Specialization', 'doctors-information-slider' ),
			'new_item_name' => __( 'New Specialization Name', 'doctors-information-slider' ),
		);

		$args = array(
			'labels' => $labels,
			'hierarchical' => true,
			'show_admin_column' => true,
			'query_var' => true,
			'rewrite' => array( 'slug' => 'specialization' ),
		);

		// Register the custom hierarchical taxonomy
		register_taxonomy( 'specialization', 'doctors_slider', $args );
	}

	/**
	 * Single Post Edit page title placeholder, "Add title" to "Doctor's Name"
	 */
	public function dis_title_place_holder( $title, $post ) {
		if ( $post->post_type == 'doctors_slider' ) {
			$my_title = esc_html_x( 'Doctor\'s Name', 'Single Post Edit page title placeholder', 'doctors-information-slider' );
			return $my_title;
		}

		return $title;
	}

	/**
	 * CPT admin edit page, order by 'title' ASC.
	 */
	public function dis_custom_post_type_admin_sort( $query ) {
		global $pagenow;

		// Check if we are in the edit screen of 'doctors_slider' custom post type and it's the main query
		if ( is_admin() && 'edit.php' === $pagenow && isset( $query->query['post_type'] ) && 'doctors_slider' === $query->query['post_type'] && $query->is_main_query() ) {
			// Set the orderby parameter to 'title'
			$query->set( 'orderby', 'title' );
			$query->set( 'order', 'ASC' ); // 'ASC' for ascending, 'DESC' for descending
		}
	}

}

// Instantiate the plugin
$doctors_information_slider = new Doctors_Information_Slider();