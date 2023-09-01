<?php
/**
 * CMB2 Metabox Config File
 *
 * @since 1.0.0
 */

function dis_register_doctors_slider_metabox() {

	$prefix = '_dis_';

	$dis_metabox_section = new_cmb2_box( array(
		'id' => 'dis_metabox',
		'title' => esc_html__( 'Doctor\'s Information', 'doctors-information-slider' ),
		'object_types' => array( 'doctors_slider' ),
	) );

	$dis_metabox_section->add_field( array(
		'id' => $prefix . 'date_of_birth',
		'type' => 'text_date',
		'name' => esc_html__( 'Age', 'doctors-information-slider' ),
		'desc' => esc_html__( 'Ex: 12/31/1999', 'doctors-information-slider' ),
	) );

	$dis_metabox_section->add_field( array(
		'id' => $prefix . 'degree',
		'type' => 'text',
		'name' => esc_html__( 'Degree', 'doctors-information-slider' ),
		'desc' => esc_html__( 'Ex: MBBS', 'doctors-information-slider' ),
	) );

	$dis_metabox_section->add_field( array(
		'id' => $prefix . 'chamber',
		'type' => 'text',
		'name' => esc_html__( 'Chamber', 'doctors-information-slider' ),
		'desc' => esc_html__( 'Ex: Uttara, Sector 9', 'doctors-information-slider' ),
	) );
}

add_action( 'cmb2_admin_init', 'dis_register_doctors_slider_metabox' );