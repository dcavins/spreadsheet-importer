<?php
/**
 * Community Commons MoCWP
 *
 * @package   CARES_Spreadsheet_Importer
 * @author    AuthorName
 * @license   GPL-2.0+
 * @link      http://www.communitycommons.org
 * @copyright 2017 Community Commons
 */
/**
 * Create all six advocacy target icons with links to the taxonomy archive
 *
 * @since   1.0.0
 * @param   string $section used to incorporate correct section in link
 * @param   int $columns  number of columns to arrange icons in
 * @param   int $icon_size Size of icons to use, in px. Will be converted to 30, 60 or 90.
 * @return  html used to show icons
 */
function cares_spreadsheet_importer_dashboard_shortcode( $atts ) {
    $a = shortcode_atts( array(
        'chart' => 'bar_pie_duo'
        ), $atts );
    ob_start();
    cares_spreadsheet_importer_dashboard( $a['chart'] );
    return ob_get_clean();
}
add_shortcode( 'snaped_dashboard', 'cares_spreadsheet_importer_dashboard_shortcode' );