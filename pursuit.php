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

add_action( 'plugins_loaded', 'get_user_info' );
function get_user_info(){
    global $current_user_meta_expiry;
    
    $current_user = wp_get_current_user();

    if ( !($current_user instanceof WP_User) )
        return;

    $current_date = strtotime( date('m-d-Y') );
    $current_user_meta_expiry = get_user_meta( $current_user->ID, 'p365_member_expiry', true );
    $current_user_expiry_date = strtotime( $current_user_meta_expiry );
    $is_expired = false;

    if (( !$current_user_expiry_date ) && ( $current_date <= $current_user_expiry_date )) {
        $is_expired = true;
    }
    
    if ( !$is_expired ) {

// Add new endpoint to use on the My Account page
        function p365_add_woo_endpoints() {
            add_rewrite_endpoint( 'directory-profile', EP_ROOT | EP_PAGES );
            add_rewrite_endpoint( 'community', EP_ROOT | EP_PAGES );
            add_rewrite_endpoint( 'digital-feature', EP_ROOT | EP_PAGES );
        }
        add_action( 'init', 'p365_add_woo_endpoints' );

// Add new query var
        function p365_woo_query_vars( $vars ) {
            $vars[] = 'directory-profile';
            $vars[] = 'community';
            $vars[] = 'digital-feature';
            return $vars;
        }
        add_filter( 'query_vars', 'p365_woo_query_vars', 0 );

// Insert the new endpoint into the My Account menu
        function p365_add_woo_tabs_my_account( $items ) {
            $items['directory-profile'] = 'Directory Profile';
            $items['community'] = 'Community';
            $items['digital-feature'] = 'Digital Feature';
            return $items;
        }
        add_filter( 'woocommerce_account_menu_items', 'p365_add_woo_tabs_my_account' );

// Add content to an endpoint
        function p365_directory_profile_content() {
            echo do_shortcode('[display-frm-data id=3762 filter=limited]');
        }
        add_action( 'woocommerce_account_directory-profile_endpoint', 'p365_directory_profile_content' );
// add_action must follow 'woocommerce_account_{your-endpoint-slug}_endpoint' format
        
// Add content to an endpoint
        function p365_community_content() {
            global $current_user_meta_expiry;
            
            echo do_shortcode('[community-link]');
            echo '<p><strong>Your access ends on: ' . $current_user_meta_expiry . '</strong></p>';
        }
        add_action( 'woocommerce_account_community_endpoint', 'p365_community_content' );
// add_action must follow 'woocommerce_account_{your-endpoint-slug}_endpoint' format

// Add content to an endpoint
        function p365_digital_feature_content() {
            echo do_shortcode('[elementor-template id="3787"]');
        }
        add_action( 'woocommerce_account_digital-feature_endpoint', 'p365_digital_feature_content' );
// add_action must follow 'woocommerce_account_{your-endpoint-slug}_endpoint' format
    }
}

/**
 * CUSTOM SHORTCODES
 */
function p365_circle_community_link(){
    $html = '';
    $html .= '<p>';
    $html .= 'Visit the pursuit:365 community for even more exclusive content and access &rarr; ';
    $html .= '<a href="https://membership.pursuit365.com/" target="_blank" class="button">Dive In</a>';
    $html .= '</p>';
    return $html;
}
add_shortcode( 'community-link', 'p365_circle_community_link' );

function p365_directory_profile_link() {
    $html = '';
    $html .= '<p>';
    $html .= 'View or edit your pursuit:365.com directory profile &rarr; ';
    $html .= '<a href="/account/?action=dir-profile" class="button">My Directory Profile</a>';
    $html .= '</p>';
    return $html;
}
add_shortcode( 'directory-profile-link', 'p365_directory_profile_link' );

function p365_show_user_avatar() {
    if( is_user_logged_in() ) {
        global $current_user;
        get_currentuserinfo();
        
        $img_src = get_avatar_url( $current_user -> ID );
        $name = $current_user -> first_name;
        $html = '';
        $html .= '<img src="' . $img_src . '" alt="' . $name . 's current profile image">';
        return $html;
    }
    else {
        // If not logged in, show default avatar
        $html = '';
        $html .= '<img src="/wp-content/plugins/pursuit/images/profile-icon.svg" alt="default image, pursuit:365 logo" title="No profile image available">';
        return $html;
    }
}
add_shortcode( 'current-profile-img', 'p365_show_user_avatar' );

/**
 * ADD ORDER AND USER META TO WOO ORDERS
 */

function p365_modify_order_meta( $order_id ) {
    
    // Get some info about the order
    $order = new WC_Order( $order_id );
    $user_id = $order->get_user_id();
    $order_items = $order->get_items();
    $order_date = $order->order_date;
    
    // Initialize a counter
    $count = 0;

    // Check for the membership products in order items
    foreach( $order_items as $item_id => $item ){
        $product_id = $item->get_product_id();
        
        // If a membership product is in the order, increase the count
        if (( $product_id == '3774') || ( $product_id == '3775' ) ) {
            $count++;
        }
        
        // If the count is greater than 0, then add custom meta
        if ( $count > 0 ) {
            // Set a future expiration date
            $expiry_date = date('m-d-Y', strtotime('+1 year', strtotime($order_date)));
            
            // Add the expiration date to user meta
            update_user_meta( $user_id, 'p365_member_expiry', $expiry_date );
            //add_meta_data( $order_id, 'p365_member_expiry', $expiry_date );
        }
    }

};
add_action( 'woocommerce_thankyou', 'p365_modify_order_meta' );