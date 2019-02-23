<?php
    add_action( 'wp_enqueue_scripts', 'my_theme_enqueue_styles' );
    function my_theme_enqueue_styles() {
    
        $parent_style = 'parent-style';
    
        wp_enqueue_style( $parent_style, get_template_directory_uri() . '/style.css' );
        wp_enqueue_style( 'child-style',
            get_stylesheet_directory_uri() . '/style.css',
            array( $parent_style ),
            wp_get_theme()->get('Version')
        );
    }

    // Edit Login Redirect
    function WOO_login_redirect( $redirect, $user ) {

        $redirect_page_id = url_to_postid( $redirect );
        $checkout_page_id = wc_get_page_id( 'checkout' );
    
        if ($redirect_page_id == $checkout_page_id) {
        return $redirect;
        }
    
        return get_permalink(get_option('woocommerce_myaccount_page_id')) . 'edit-account/';
    
    }
    
    add_action('woocommerce_login_redirect', 'WOO_login_redirect', 10, 2);


    // Edit my account menu order
    function my_account_menu_order() {
        $menuOrder = array(
            'edit-account'    	=> __( 'Account Details', 'woocommerce' ),
            'orders'             => __( 'Orders', 'woocommerce' ),
            'customer-logout'    => __( 'Logout', 'woocommerce' ),
        );
        return $menuOrder;
    }
    add_filter ( 'woocommerce_account_menu_items', 'my_account_menu_order' );
    
    // Remove the dashboard menu item
    function WOO_account_menu_items($items) {
        unset($items['dashboard']);
        return $items;            
    }
    
    add_filter ('woocommerce_account_menu_items', 'WOO_account_menu_items');
  
?>