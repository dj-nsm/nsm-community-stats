<?php
/*
Plugin Name: NSM Community Statistics
Plugin URI:  https://nailsoupmedia.com
Description: Fetch community information by saved search
Version:     1.0
Author:      Nail Soup Media
Author URI:  https://nailsoupmedia.com
License:     GPL2
License URI: https://www.gnu.org/licenses/old-licenses/gpl-2.0.html
*/

/* Copyright 2021 Nail Soup Media (email : support@nailsoupmedia.com)
(NSM Community Statistics) is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 2 of the License, or
any later version.
 
(NSM Community Statistics) is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.
 
You should have received a copy of the GNU General Public License
along with (NSM Community Statistics). If not, see (https://www.gnu.org/licenses/old-licenses/gpl-2.0.html).
*/

function nsm_community_styles() {
	wp_register_style ( 'nsm_community_styles', plugins_url( 'shortcode-style.css', __FILE__ ) );
	wp_enqueue_style( 'nsm_community_styles' );
}
add_action( 'wp_enqueue_scripts', 'nsm_community_styles');

function nsm_community_add_settings_page() {
    add_options_page( 'NSM Community Statistics page', 'NSM Community Statistics', 'manage_options', 'nsm_community', 'nsm_community_render_plugin_settings_page' );
}
add_action( 'admin_menu', 'nsm_community_add_settings_page' );

function nsm_community_render_plugin_settings_page() {
    ?>
    <h2>NSM Community Statistics Settings</h2>
    <form action="options.php" method="post">
        <?php 
        settings_fields( 'nsm_community_options' );
        do_settings_sections( 'nsm_community' ); ?>
        <input name="submit" class="button button-primary" type="submit" value="<?php esc_attr_e( 'Save' ); ?>" />
    </form>
    <?php
}

function nsm_community_register_settings() {
    register_setting( 'nsm_community_options', 'nsm_community_options', 'nsm_community_options_validate' );
    add_settings_section( 'api_settings', 'API Settings', 'nsm_community_plugin_section_text', 'nsm_community' );

    add_settings_field( 'nsm_community_plugin_setting_api_key', 'API Key', 'nsm_community_plugin_setting_api_key', 'nsm_community', 'api_settings' );
    add_settings_field( 'nsm_community_plugin_setting_dev_key', 'Developer Key', 'nsm_community_plugin_setting_dev_key', 'nsm_community', 'api_settings' );
	add_settings_field( 'nsm_community_plugin_setting_details_url', 'Developer Key', 'nsm_community_plugin_setting_details_url', 'nsm_community', 'api_settings' );
}
add_action( 'admin_init', 'nsm_community_register_settings' );

function nsm_community_options_validate( $input ) {
    $newinput['api_key'] = trim( $input['api_key'] );
	$newinput['dev_key'] = trim( $input['dev_key'] );
	$newinput['details_url'] = trim( $input['details_url'] );
    return $newinput;
}

function nsm_community_plugin_section_text() {
    echo '<p>Here you can set all the options for using the API</p>';
}

function nsm_community_plugin_setting_api_key() {
    $options = get_option( 'nsm_community_options' );
    echo "<input id='nsm_community_plugin_setting_api_key' name='nsm_community_options[api_key]' type='text' value='" . esc_attr( $options['api_key'] )  . "' />";
}

function nsm_community_plugin_setting_dev_key() {
    $options = get_option( 'nsm_community_options' );
    echo "<input id='nsm_community_plugin_setting_dev_key' name='nsm_community_options[dev_key]' type='text' value='" . esc_attr( $options['dev_key'] )  . "' />";
}

function nsm_community_plugin_setting_details_url() {
    $options = get_option( 'nsm_community_options' );
    echo "<input id='nsm_community_plugin_setting_dev_key' name='nsm_community_options[details_url]' type='text' value='" . esc_attr( $options['details_url'] )  . "' />";
}

function comstat_shortcode( $atts = array() ) {
	// set up default parameters
	extract( shortcode_atts( array(
		'slid' => '8215'
	), $atts));
	$options = get_option( 'nsm_community_options' );
	$access_key = esc_attr( $options['api_key'] );
	$dev_key = esc_attr( $options['dev_key'] );
	$SL_ID = $atts['slid'];
	$request = 'https://api.idxbroker.com/clients/savedlinks/' . $SL_ID . '/results';
	$results = IDX_API($access_key, $dev_key, $request, "GET");
	$numProps = count($results);
	$prices = array();
	foreach ($results as $result) {
		$price = $result['listingPrice'];
		$price = str_replace("$","",$price);
		$price = str_replace(",","",$price);
		$prices[] = intval($price);
	}
	$highest = max($prices);
	$highest = number_format($highest,0,".",",");
	$lowest = min($prices);
	$lowest = number_format($lowest,0,".",",");
	$average = array_sum($prices)/count($prices);
	$average = number_format($average,0,".",",");
	$html = '<div class="nsm-community-stats-container">';
	$html .= '<ul id="static-filed">';
	$html .= '<li>Homes for Sale</li>';
	$html .= '<li id="hsale">' . $numProps . '</li>';
	$html .= '</ul>';
	$html .= '<ul id="static-filed">';
	$html .= '<li>Average Home Price</li>';
	$html .= '<li id="aprice">' . $average . '</li>';
	$html .= '</ul>';
	$html .= '<ul id="static-filed">';
	$html .= '<li>Highest Home Price</li>';
	$html .= '<li id="hprice">' . $highest . '</li>';
	$html .= '</ul>';
	$html .= '<ul id="static-filed">';
	$html .= '<li>Lowest Home Price</li>';
	$html .= '<li id="lprice">' . $lowest . '</li>';
	$html .= '</ul>';
	$html .= '</div>';
	echo $html;
}
add_shortcode('comstat', 'comstat_shortcode');

function hotsheet_shortcode( $atts = array() ) {
	// set up default parameters
	extract( shortcode_atts( array(
		'slid' => '8215'
	), $atts));
	$options = get_option( 'nsm_community_options' );
	$access_key = esc_attr( $options['api_key'] );
	$dev_key = esc_attr( $options['dev_key'] );
	$SL_ID = $atts['slid'];
	$request = 'https://api.idxbroker.com/clients/savedlinks/' . $SL_ID . '/results';
	$results = IDX_API($access_key, $dev_key, $request, "GET");
	$html = '<div class="nsm-community-stats-container">';
	$html .= '<table>';
	$html .= '<tr>';
	$html .= '<th>Status</th><th>View Link</th><th>Price</th><th>Address</th><th>Full Bathrooms</th><th>Partial Bathrooms</th><th>SqFt</th>';
	$html .= '</tr>';
	foreach( $results as $result ) {
		$status = $result['propStatus'];
		$listingID = $results['listingID'];
		$address = $result['address'];
		$city = $result['cityName'];
		$state = $result['state'];
		$zip = $result['zipcode'];
		$bedrooms = $result['bedrooms'];
		$fullbaths = $result['fullBaths'];
		$partialbaths = $result['partialBaths'];
		$price = $result['listingPrice'];
		$detailURL = $result['detailsURL'];
		$sqft = $result['sqFt'];
		$link = esc_attr( $options['details_url'] );
		$link .= $detailURL;
		$html .= '<tr>';
		$html .= '<td>' . $status . '</td>';
		$html .= '<td><a href="' . $link . '">View</a></td>';
		$html .= '<td>' . $price . '</td>';
		$html .= '<td>' . $address . '</td>';
		$html .= '<td>' . $fullbaths . '</td>';
		$html .= '<td>' . $partialbaths . '</td>';
		$html .= '<td>' . $sqft . '</td>';
		$html .= '</tr>';
	}
	$html .= '</table>';
	$html .= '</div>';
	echo $html;
}
add_shortcode('hotsheet', 'hotsheet_shortcode');

function IDX_API( $access_key = null, $dev_key = null, $request = null, $method = null ) {
	$args = array(
		'headers' => array(
			'Content-Type' => 'application/x-www-form-urlencoded',
			'accesskey' => $access_key,
			'ancillarykey' => $dev_key,
			'outputtype' => 'json',
		)
	);
	$response = wp_remote_retrieve_body( wp_remote_get( $request, $args ) );
	$response = json_decode( $response, true );
	return $response;
}

?>