<?php
/**
 * @package Pursuit_365
 * @version 1.0.0
 */
/*
Plugin Name: Pursuit 365 Custom Code
Description: All custom code for pursuit365.com
Author: Janine Paris
Version: 1.0.0
Author URI: https://criticalhit.dev
*/

function test_shorty() {
    $html = "Hello World";
    return $html;
}

add_shortcode( 'test-shorty', 'test_shorty' );