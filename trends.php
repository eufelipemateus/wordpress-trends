<?php
/**
 * Plugin Name: Trends
 * Description: This is a trends topics  plugins jetpack extension
 * Version:     1.0
 * Author:      eufelipemateus
 * Author URI:  http://eufelipemateus.com
 * License:     GPLv2 or later
 * Text Domain: eufelipemateus
 */


if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly;
}


function head(){
    ?>
<style>
    #trends a{
        color: #000;
        font-weight: bolder;
        text-decoration: none;
    }

</style>
<?php
}


function get_posts_url(){
    $html = '';
    $top_posts = stats_get_csv( 'postviews', 'period=week&limit=10' );
    foreach ($top_posts as $post_item):
        $html .= '<li>
            <a href="'.$post_item['post_permalink'].'" class="special-slide">
            '.$post_item['post_title'].' 
            </a>
            </li>';
    endforeach; 
    return $html;
    
}


function trends(){
    return '<ol id="trends">
            '.get_posts_url().'
        </ol>';
}


add_action('wp_head', 'head');
add_shortcode('trends', 'trends');
