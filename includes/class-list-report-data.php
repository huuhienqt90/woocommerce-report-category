<?php
/**
 * Created by PhpStorm.
 * User: HuuHien
 * Date: 1/22/2015
 * Time: 3:38 PM
 */
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

if ( ! class_exists( 'WP_List_Table' ) ) {
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}
class IZW_Report_Data extends WP_List_Table{

    var $view_type = array();
    var $beds_total = 0;
    var $received_total = 0;
    var $outstanding_total = 0;

    /** ************************************************************************
     * REQUIRED. Set up a constructor that references the parent constructor. We
     * use the parent reference to set some default configs.
     ***************************************************************************/
    function __construct(){
        global $status, $page;

        //Set parent defaults
        parent::__construct( array(
            'singular'  => 'report',     //singular name of the listed records
            'plural'    => 'reports',    //plural name of the listed records
            'ajax'      => false        //does this table support ajax?
        ) );

    }


    /** ************************************************************************
     * Recommended. This method is called when the parent class can't find a method
     * specifically build for a given column. Generally, it's recommended to include
     * one method for each column you want to render, keeping your package class
     * neat and organized. For example, if the class needs to process a column
     * named 'title', it would first see if a method named $this->column_title()
     * exists - if it does, that method will be used. If it doesn't, this one will
     * be used. Generally, you should try to use custom column methods as much as
     * possible.
     *
     * Since we have defined a column_title() method later on, this method doesn't
     * need to concern itself with any column with a name of 'title'. Instead, it
     * needs to handle everything else.
     *
     * For more detailed insight into how columns are handled, take a look at
     * WP_List_Table::single_row_columns()
     *
     * @param array $item A singular item (one full row's worth of data)
     * @param array $column_name The name/slug of the column to be processed
     * @return string Text or HTML to be placed inside the column <td>
     **************************************************************************/
    function column_default($item, $column_name){
        switch($column_name){
            case "location":
                return $item[$column_name];
            default:
                return print_r($item,true); //Show the whole array for troubleshooting purposes
        }
    }
    function column_ID($item){
        //Return the Booking ID contents
        return sprintf('<a href="%1$s"><strong>#%2$s</strong></a>',
            /*$1%s*/ add_query_arg( array( 'post' => $item['ID'], 'action' => 'edit'), admin_url( 'post.php') ),
            /*$2%s*/ $item['ID']
        );
    }
    function column_name($item){

        return sprintf('%1$s',
            /*$1%s*/ $item['name']
        );
    }
    function column_email($item){

        return sprintf('%1$s',
            /*$1%s*/ $item['email']
        );
    }
    function column_phone($item){

        return sprintf('%1$s',
            /*$1%s*/ $item['phone']
        );
    }
    function column_promoter($item){

        return sprintf('%1$s',
            /*$1%s*/ $item['promoter']
        );
    }
    function column_booking_type($item){

        return sprintf('<strong>%1$s</strong>',
            /*$1%s*/ $item['booking_type']
        );
    }
    function column_payments($item){

        return sprintf('%1$s',
            /*$1%s*/ wc_price( $item['payments'] )
        );
    }
    function column_remaining($item){

        return sprintf('%1$s',
            /*$1%s*/ wc_price( $item['remaining'] )
        );
    }
    function column_start_date($item){

        return sprintf('%1$s',
            /*$1%s*/ date( 'M jS Y', strtotime( $item['start_date'] ) )
        );
    }
    function column_end_date($item){

        return sprintf('%1$s',
            /*$1%s*/ date( 'M jS Y', strtotime( $item['end_date'] ) )
        );
    }

    function get_columns(){
        $columns = array(
            'ID'                => 'Booking ID', //Render a checkbox instead of text
            'name'              => 'Name',
            'email'             => 'Email',
            'phone'             => 'Phone Number',
            'promoter'          => 'Promoter (Product Category)',
            'booking_type'      => 'Booking Type (Product)',
            'payments'          => 'Payments',
            'remaining'         => 'Remaining',
            'start_date'        => 'Start Date',
            'end_date'          => 'End Date',
            'location'          => 'Locations'
        );
        return $columns;
    }

    function get_sortable_columns() {
        $sortable_columns = array(
            'name'     => array('name',false),     //true means it's already sorted
            'email'    => array('email',false),
            'ID'  => array('ID',false)
        );
        return $sortable_columns;
    }

    function get_bulk_actions() {
        $actions = array(

        );
        return $actions;
    }

    function process_bulk_action() {



    }

    function prepare_items() {
        global $wpdb; //This is used only if making any database queries

        /**
         * First, lets decide how many records per page to show
         */
        $per_page = 10;



        /**
         * REQUIRED. Now we need to define our column headers. This includes a complete
         * array of columns to be displayed (slugs & titles), a list of columns
         * to keep hidden, and a list of columns that are sortable. Each of these
         * can be defined in another method (as we've done here) before being
         * used to build the value for our _column_headers property.
         */
        $columns = $this->get_columns();
        $hidden = array();
        $sortable = $this->get_sortable_columns();


        /**
         * REQUIRED. Finally, we build an array to be used by the class for column
         * headers. The $this->_column_headers property takes an array which contains
         * 3 other arrays. One for all columns, one for hidden columns, and one
         * for sortable columns.
         */
        $this->_column_headers = array($columns, $hidden, $sortable);


        /**
         * Optional. You can handle your bulk actions however you see fit. In this
         * case, we'll handle them within our package just to keep things clean.
         */
        $this->process_bulk_action();


        /**
         * Instead of querying a database, we're going to fetch the example data
         * property we created for use in this plugin. This makes this example
         * package slightly different than one you might build on your own. In
         * this example, we'll be using array manipulation to sort and paginate
         * our data. In a real-world implementation, you will probably want to
         * use sort and pagination data to build a custom query instead, as you'll
         * be able to use your precisely-queried data immediately.
         */
        $data = array();
        $booking_args = array(
            'post_type' => 'wc_booking',
            'post_status' => 'paid',
            'posts_per_page' => -1,
        );
        if( isset( $_REQUEST['izw_search'])){
            $this->product_ids = array();
            /*if ( isset( $_REQUEST['izw_search_key'] ) && is_array( $_REQUEST['izw_search_key'] ) ) {
                $this->show_categories = array_map( 'absint', $_REQUEST['izw_search_key'] );
            } elseif ( isset( $_GET['izw_search_key'] ) ) {
                $this->show_categories = array( absint( $_GET['izw_search_key'] ) );
            }*/
            $checkPromoter = false;
            if( isset( $_REQUEST['izw_promoter'] ) && !empty( $_REQUEST['izw_promoter'] ) ){
                $checkPromoter = true;
                $this->view_type[] = 'Promoter';
                if( is_array( $_REQUEST['izw_promoter'] ) ){
                    $this->show_categories = array_map( 'absint', $_REQUEST['izw_promoter'] );
                }else{
                    $this->show_categories = array( absint( $_REQUEST['izw_promoter'] ) );
                }
                foreach( $this->show_categories as $cat ){
                    $category = get_term( $cat, 'product_cat');
                    $product_id = get_objects_in_term($category->term_id, 'product_cat');
                    if (!empty($product_id) && is_array($product_id) && sizeof($product_id)) {
                        foreach ($product_id as $id) {
                            $this->product_ids[] = $id;
                        }
                    }
                }
                $this->product_ids = array_unique($this->product_ids);
            }
            $productids = array();
            $checkLocation = false;
            if( isset( $_REQUEST['izw_location'] ) && $_REQUEST['izw_location'] != '0' ){
                $checkLocation = true;
                $this->view_type[] = 'Location';
                $location_term = get_term( $_REQUEST['izw_location'], 'location');
                $product_id = get_objects_in_term( $location_term->term_id, 'location');
                if (!empty($product_id) && is_array( $product_id ) && sizeof($product_id)) {
                    foreach ($product_id as $id) {
                        $productids[] = $id;
                    }
                }
            }
            if( sizeof( $this->product_ids ) ){
                if( $checkLocation ){
                    $this->product_ids = array_values( array_intersect( $productids, $this->product_ids ) );
                }
                if( $checkLocation && $checkPromoter && (sizeof( $productids ) <= 0 ) ){
                    $this->product_ids = array(0);
                }
            }elseif( sizeof( $this->product_ids ) <= 0 ){
                if( sizeof( $productids ) > 0 ){
                    $this->product_ids = $productids;
                }elseif( $checkLocation || $checkPromoter ){
                    $this->product_ids = array( 0 );
                }
            }
            $booking_args = array(
                'post_type' => 'wc_booking',
                'posts_per_page' => -1,
                'post_status' => 'paid',
                'meta_query' => array(
                    array(
                        'key' => '_booking_product_id',
                        'value' => $this->product_ids,
                        'compare' => 'IN'
                    )
                )
            );
        }
        if( sizeof( $this->view_type ) < 1 ){
            $this->view_type = array('All');
        }
        $booking_args = apply_filters( 'izw_report_booking_args', $booking_args, $this );

        $bookingdata = new WP_Query( $booking_args );
        if ($bookingdata->have_posts()) {
            while ($bookingdata->have_posts()) {
                $bookingdata->the_post();
                global $post;
                $WC_Booking = new WC_Booking( get_the_ID() );
                $user = get_user_by( 'id', $post->post_author );

                $promoter_string = $location_string = $booking_string = '';
                foreach( $WC_Booking->get_order()->get_items() as $item){

                    /**
                     * Calculator Bed Room
                     */
                    $roomLabel = get_post_meta( $item['product_id'], '_wc_booking_resouce_label', true );
                    $bed = explode(" ", $item[$roomLabel]);
                    $bed_size = absint( $bed[0] ) ? $bed[0] : 0;
                    $this->beds_total += (int)$bed_size;

                    /**
                     * List Promoter
                     */
                    $promoter = wp_get_post_terms( $item['product_id'], 'product_cat');
                    if (!is_wp_error($promoter)) {
                        $promoter_string = '';
                        foreach( $promoter as $term){
                            $promoter_string .= '<a href="'.add_query_arg( array('action' => 'edit', 'taxonomy'=> 'product_cat', 'tag_ID' => $term->term_id, 'post_type'=>'product' ),admin_url('edit-tags.php')).'"><strong>'.$term->name.'</strong></a><br />';
                        }
                    }

                    /**
                     * List Locations
                     */
                    $locations = wp_get_post_terms( $item['product_id'], 'location' );
                    if (!is_wp_error($locations)) {
                        foreach( $locations as $term){
                            $location_string .= '<strong>'.$term->name.'</strong><br />';
                        }
                    }

                    /**
                     * List Booking Types
                     */
                    $booking_string .= '<a href="'.add_query_arg( array('post' => $item['product_id'], 'action'=> 'edit' ),admin_url('post.php')).'">'. get_the_title( $item['product_id'] ). '</a><br />';
                }

                /**
                 * Filter to change Promoter and Location string
                 */
                $promoter_string = apply_filters( 'izw_report_booking_promoter_string', $promoter_string, $WC_Booking );
                $location_string = apply_filters( 'izw_report_booking_location_string', $location_string, $WC_Booking );

                /**
                 * Get Paid and Remaining Price
                 */
                $paid_price = get_post_meta( $WC_Booking->get_order()->id, '_deposit_paid', true );
                if( (float)$WC_Booking->get_order()->get_total() >= (float)$paid_price ){
                    $remaining_price = (float)$WC_Booking->get_order()->get_total() - (float)$paid_price;
                }else{
                    $remaining_price = $WC_Booking->get_order()->get_total();
                }
                $this->outstanding_total += (float)$remaining_price;
                $this->received_total += (float)$paid_price;

                /**
                 * Set Data for list table
                 */
                $data[] = array(
                    'ID' => get_the_ID(), //Render a checkbox instead of text
                    'name' => '<a href="'.add_query_arg( array( 'user_id' => $post->post_author ), admin_url('user-edit.php') ).'"><strong>'. $user->first_name . " " . $user->last_name. '</strong></a>',
                    'email' => $user->user_email,
                    'phone' => get_user_meta( $post->post_author, 'billing_phone', true),
                    'promoter' => $promoter_string,
                    'booking_type' => $booking_string,
                    'payments' => $paid_price,
                    'remaining' => $remaining_price,
                    'start_date' => get_post_meta( get_the_ID(), '_booking_start', true ),
                    'end_date' => get_post_meta( get_the_ID(), '_booking_end', true ),
                    'location' => $location_string
                );
            }
        }
        wp_reset_postdata();


        /**
         * This checks for sorting input and sorts the data in our array accordingly.
         *
         * In a real-world situation involving a database, you would probably want
         * to handle sorting by passing the 'orderby' and 'order' values directly
         * to a custom query. The returned data will be pre-sorted, and this array
         * sorting technique would be unnecessary.
         */
        function usort_reorder($a,$b){
            $orderby = (!empty($_REQUEST['orderby'])) ? $_REQUEST['orderby'] : 'ID'; //If no sort, default to title
            $order = (!empty($_REQUEST['order'])) ? $_REQUEST['order'] : 'DESC'; //If no order, default to asc
            $result = strcmp($a[$orderby], $b[$orderby]); //Determine sort order
            return ($order==='asc') ? $result : -$result; //Send final sort direction to usort
        }
        usort($data, 'usort_reorder');


        /***********************************************************************
         * ---------------------------------------------------------------------
         * vvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvv
         *
         * In a real-world situation, this is where you would place your query.
         *
         * For information on making queries in WordPress, see this Codex entry:
         * http://codex.wordpress.org/Class_Reference/wpdb
         *
         * ^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^
         * ---------------------------------------------------------------------
         **********************************************************************/


        /**
         * REQUIRED for pagination. Let's figure out what page the user is currently
         * looking at. We'll need this later, so you should always include it in
         * your own package classes.
         */
        $current_page = $this->get_pagenum();

        /**
         * REQUIRED for pagination. Let's check how many items are in our data array.
         * In real-world use, this would be the total number of items in your database,
         * without filtering. We'll need this later, so you should always include it
         * in your own package classes.
         */
        $total_items = count($data);


        /**
         * The WP_List_Table class does not handle pagination for us, so we need
         * to ensure that the data is trimmed to only the current page. We can use
         * array_slice() to
         */
        $data = array_slice($data,(($current_page-1)*$per_page),$per_page);



        /**
         * REQUIRED. Now we can add our *sorted* data to the items property, where
         * it can be used by the rest of the class.
         */
        $this->items = $data;


        /**
         * REQUIRED. We also have to register our pagination options & calculations.
         */
        $this->set_pagination_args( array(
            'total_items' => $total_items,                  //WE have to calculate the total number of items
            'per_page'    => $per_page,                     //WE have to determine how many items to show on a page
            'total_pages' => ceil($total_items/$per_page)   //WE have to calculate the total number of pages
        ) );
    }

}