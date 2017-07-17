<?php
/*
Plugin Name: Woocommerce Report Categories
Plugin URI: https://github.com/huuhienqt90/woocommerce-report-category
Description: Report Sale by categories
Version: 1.0.1
Author: Hien(Hamilton) H.HO
Author URI: https://github.com/huuhienqt90
Text Domain: woocommerce-report-category
*/
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}
if ( ! class_exists( 'WooReport' ) ) :
    class WooReport{

        /**
         * Construct Class
         */
        public function __construct(){
            $this->defines();
            add_action( 'init', array( $this, 'init') );
            add_filter( 'woocommerce_reports_charts', array( $this, 'add_report_tab') );
            //Change deposits
            add_filter('woocommerce_add_cart_item', array( $this, 'add_cart_item' ), 20, 2 );
        }

        /**
         * Set Init Function
         */
        public function init(){
            $this->register_taxonomy();
            wp_register_style( 'izw-report-style', plugins_url( 'assets/admin/style.css', __FILE__ ) );
            wp_register_script( 'izw-report-script', plugins_url( 'assets/admin/js/admin.js', __FILE__ ) );
        }

        /**
         * Define Constant
         */
        public function defines(){
            define( '__TEXTDOMAIN__', 'woocommerce-report-category' );
        }

        /**
         * Add Report Tab
         *
         * @param $reports
         * @return mixed
         */
        public function add_report_tab( $reports ){
            $reports['category'] = array(
                'title'  => __( 'Sale by Categories', 'woocommerce' ),
                'reports' => array(
                    "categories" => array(
                        'title'       => __( 'Sale Categories', 'woocommerce' ),
                        'description' => '',
                        'hide_title'  => true,
                        'callback'    => array( $this, 'get_report' )
                    ),
                )
            );
            return $reports;
        }

        /**
         * Load Report List Page
         */
        public function get_report(){
            $assets_path          = str_replace( array( 'http:', 'https:' ), '', WC()->plugin_url() ) . '/assets/';
            wp_enqueue_script( 'chosen' );
            wp_enqueue_script( 'wc-chosen' );
            wp_enqueue_script( 'izw-report-script' );
            wp_enqueue_style( 'woocommerce_chosen_styles', $assets_path . 'css/chosen.css' );
            wp_enqueue_style( 'izw-report-style' );
            include("includes/class-list-report-data.php");
            include("templates/html-admin-report.php");
        }

        /**
         * Register Taxonomy
         */
        public function register_taxonomy(){
            // Add new taxonomy, make it hierarchical (like categories)
            $labels = array(
                'name'              => _x( 'Locations', 'taxonomy general name' ),
                'singular_name'     => _x( 'Location', 'taxonomy singular name' ),
                'search_items'      => __( 'Search Locations' ),
                'all_items'         => __( 'All Locations' ),
                'parent_item'       => __( 'Parent Location' ),
                'parent_item_colon' => __( 'Parent Location:' ),
                'edit_item'         => __( 'Edit Location' ),
                'update_item'       => __( 'Update Location' ),
                'add_new_item'      => __( 'Add New Location' ),
                'new_item_name'     => __( 'New Location Name' ),
                'menu_name'         => __( 'Locations' ),
            );

            $args = array(
                'hierarchical'      => true,
                'labels'            => $labels,
                'show_ui'           => true,
                'show_admin_column' => true,
                'query_var'         => true,
                'rewrite'           => array( 'slug' => 'location' ),
            );

            register_taxonomy( 'location', array( 'product' ), $args );
        }

        /**
         * Update deposit meta
         */
        public function update_deposit_meta($product, $quantity, &$cart_item_data)
        {
            if ($product->wc_deposits_enable_deposit === 'yes' && isset($cart_item_data['deposit']) &&
                $cart_item_data['deposit']['enable'] === 'yes')
            {
                $deposit_amount = $product->wc_deposits_deposit_amount;
                $deposit = $deposit_amount;

                if ($product->is_type('booking')) {
                    $amount = $cart_item_data['booking']['_cost'];
                    if ($product->has_persons() && $product->wc_deposits_enable_per_person == 'yes')
                    {
                        $persons = array_sum($cart_item_data['booking']['_persons']);
                        if ($product->wc_deposits_amount_type === 'fixed') {
                            $deposit = $deposit_amount * $persons;
                        } else { // percent
                            $deposit = $deposit_amount / 100.0 * $amount;
                        }
                    } else {
                        if ($product->wc_deposits_amount_type === 'percent') {
                            $deposit = $deposit_amount / 100.0 * $amount;
                        }
                    }
                } else {
                    if ($product->is_type('variable')) {
                        $amount = $cart_item_data['line_subtotal'];
                    } else {
                        $amount = $product->get_price_excluding_tax($quantity);
                    }
                    if ($product->wc_deposits_amount_type === 'fixed') {
                        $deposit = $deposit * $quantity;
                    } else {
                        $deposit = $amount * ($deposit_amount / 100.0);
                    }
                }

                if ($deposit < $amount && $deposit > 0) {
                    $cart_item_data['deposit']['deposit'] = $deposit;
                    $cart_item_data['deposit']['remaining'] = $amount - $deposit;
                    $cart_item_data['deposit']['total'] = $amount;
                } else {
                    $cart_item_data['deposit']['enable'] = 'no';
                }
            }
        }

        /**
         * Add cart item
         */
        public function add_cart_item($cart_item, $cart_item_key)
        {
            $product = wc_get_product( $cart_item['product_id']);

            if ($product->wc_deposits_enable_deposit === 'yes' && !empty($cart_item['deposit']) && $cart_item['deposit']['enable'] === 'yes')
            {
                $this->update_deposit_meta($product, $cart_item['quantity'], $cart_item);
                echo $product->wc_deposits_enable_per_person;
                die();
            }

            return $cart_item;
        }
    }
    new WooReport();
endif;