<div class="izw-report-categories">
    <h2><?php _e( "Report by Categories", __TEXTDOMAIN__ ); ?></h2>
    <?php
        $listdata = new IZW_Report_Data();
        $listdata->prepare_items();
        $prompter_selected = isset( $_REQUEST['izw_promoter']) ? $_REQUEST['izw_promoter'] : '';
        $location_selected = isset( $_REQUEST['izw_location']) ? $_REQUEST['izw_location'] : '';
    ?>
    <form name="izw-search-box" action="" method="get">
        <input type="hidden" name="page" value="<?php echo $_REQUEST['page']; ?>"/>
        <input type="hidden" name="tab" value="<?php echo $_REQUEST['tab']; ?>"/>
    <div class="izw-row" id="izw-row">
        <div class="izw-column">
            <div class="izw-information">
                <div class="izw-counter">
                    <p>Viewing: <?php echo implode( ", ", $listdata->view_type ); ?></p>
                    <p>Total Nr of Beds: <?php echo $listdata->beds_total; ?></p>
                </div>
                <div class="izw-money">
                    <h2>Money received Total: <?php echo wc_price( $listdata->received_total); ?></h2>
                    <h3>Money outstanding Total: <?php echo wc_price( $listdata->outstanding_total ); ?></h3>
                </div>
            </div>
        </div><!-- END .izw-column -->
        <div class="izw-column">
            <div class="izw-dropdown">
                <div class="izw-promoter-box" style="display: inline;">
                    <select name="izw_promoter[]" multiple id="izw-promoter" data-placeholder="Select Promoter" style="width: 49%;">
                        <?php
                        $args = array(
                            'orderby' => 'name',
                            'order' => 'ASC',
                            'hide_empty' => false,
                            'exclude' => array(),
                            'exclude_tree' => array(),
                            'include' => array(),
                            'number' => '',
                            'fields' => 'all',
                            'slug' => '',
                            'name' => '',
                            'parent' => '',
                            'hierarchical' => true,
                            'child_of' => 0,
                            'get' => '',
                            'name__like' => '',
                            'description__like' => '',
                            'pad_counts' => false,
                            'offset' => '',
                            'search' => '',
                            'cache_domain' => 'core'
                        );

                        $terms = get_terms('product_cat', $args);
                        if (!empty($terms) && !is_wp_error($terms)) {
                            foreach ($terms as $term) {
                                ?>
                                <option <?php selected( $term->term_id, $prompter_selected ); ?> value="<?php echo $term->term_id; ?>"><?php echo $term->name; ?></option>
                            <?php
                            }
                        }
                        ?>
                    </select>
                    <a href="#" class="izw-pro-all">All</a> |
                    <a href="#" class="izw-pro-none">None</a>
                </div>
                <select name="izw_location" id="izw-location" data-placeholder="Select Location" style="width: 49%;">
                    <option value="0">Select Location</option>
                    <?php
                    $args = array(
                        'orderby' => 'name',
                        'order' => 'ASC',
                        'hide_empty' => false,
                        'exclude' => array(),
                        'exclude_tree' => array(),
                        'include' => array(),
                        'number' => '',
                        'fields' => 'all',
                        'slug' => '',
                        'name' => '',
                        'parent' => '',
                        'hierarchical' => true,
                        'child_of' => 0,
                        'get' => '',
                        'name__like' => '',
                        'description__like' => '',
                        'pad_counts' => false,
                        'offset' => '',
                        'search' => '',
                        'cache_domain' => 'core'
                    );

                    $terms = get_terms('location', $args);
                    if (!empty($terms) && !is_wp_error($terms)) {
                        foreach ($terms as $term) {
                            ?>
                            <option <?php selected( $term->term_id, $location_selected ); ?> value="<?php echo $term->term_id; ?>"><?php echo $term->name; ?></option>
                        <?php
                        }
                    }
                    ?>
                </select>
            </div>
            <input type="submit" name="izw_search" class="button button-primary" value="Search"/>
        </div><!-- END .izw-column -->
        <!--<div class="izw-column">
            <div class="izw-search-box">
                    <input type="hidden" name="page" value="<?php /*echo $_REQUEST['page']; */?>"/>
                    <input type="hidden" name="tab" value="<?php /*echo $_REQUEST['tab']; */?>"/>
                    <p>
                        <select name="izw_search_key[]" multiple id="izw-search-key" data-placeholder="Select Categories">
                            <?php
/*                            $args = array(
                                'orderby' => 'name',
                                'order' => 'ASC',
                                'hide_empty' => false,
                                'exclude' => array(),
                                'exclude_tree' => array(),
                                'include' => array(),
                                'number' => '',
                                'fields' => 'all',
                                'slug' => '',
                                'name' => '',
                                'parent' => '',
                                'hierarchical' => true,
                                'child_of' => 0,
                                'get' => '',
                                'name__like' => '',
                                'description__like' => '',
                                'pad_counts' => false,
                                'offset' => '',
                                'search' => '',
                                'cache_domain' => 'core'
                            );

                            $terms = get_terms('product_cat', $args);
                            if (!empty($terms) && !is_wp_error($terms)) {
                                foreach ($terms as $term) {
                                    */?>
                                    <option value="<?php /*echo $term->term_id; */?>"><?php /*echo $term->name; */?></option>
                                <?php
/*                                }
                            }
                            */?>
                        </select>
                        <input type="submit" name="izw_search" class="button button-primary" value="Search"/>
                    </p>
                    <script type="text/javascript">
                        jQuery(document).ready(function ($) {
                            $("#izw-search-key").chosen({width: "80%"});
                        });
                    </script>
            </div><!-- END .izw-search-box -->
        <!--</div><!-- END .izw-column -->
    </div><!-- END .izw-row -->
        <script type="text/javascript">
            jQuery(document).ready(function ($) {
                $("#izw-promoter").chosen({width: "34%"});
                $("#izw-location").chosen({width: "45%"});
            });
        </script>
        <style type="text/css">
            .izw-search-data table.wp-list-table .column-name{
                width: 11%;
            }
            .izw-search-data table.wp-list-table #promoter{
                width: 11%;
            }
            .izw-search-data table.wp-list-table #booking_type{
                width: 20%;
            }
        </style>
    </form>
    <div class="izw-search-data">
        <?php
            $listdata->display();
        ?>
    </div>
</div><!-- END .izw-report-categories-->