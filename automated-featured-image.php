<?php
/**
 * Plugin Name
 *
 * @package           Automated Featured Image
 * @author            cLonata
 * @copyright         2023 cLonata
 * @license           BSD-3-Clause
 *
 * @wordpress-plugin
 * Plugin Name:       Automated Featured Image
 * Plugin URI:        https://www.github.com/cLonata
 * GitHub Plugin URI: cLonata/automated-featured-image
 * Description:       Automatically fetches Featured Image
 * Version:           1.0.0
 * Requires at least: 5.2
 * Requires PHP:      7.1
 * Author:            cLonata
 * Author URI:        https://www.github.com/cLonata
 * Text Domain:       automated-featured-image
 * License:           The 3-Clause BSD License
 * License URI:       https://opensource.org/licenses/BSD-3-Clause
 * Update URI:        https://www.github.com/cLonata
 * 
 * Automated Featured Image is free software: you can redistribute it and/or modify
 * it under the terms of the BSD-3-Clause License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * any later version.
 * 
 * Automated Featured Image is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * BSD-3-Clause License for more details.
 * 
 * You should have received a copy of the BSD-3-Clause License
 * along with Automated Featured Image. If not, see 
 * https://opensource.org/licenses/BSD-3-Clause.
 */

// If this plugin is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// If function of the same name already exist, abort.
if ( ! function_exists( 'afi_fake_thumbnail_id' ) ) {    
    /**
     * Summary of afi_fake_thumbnail_id
     * 
     * If the Posts doesn't have $thumbnail_id then
     * create a fake $thumbnail_id for Posts
     * which doesn't have thumbnails in order to
     * bypass theme's placeholder images.
     *
     * @param  int $thumbnail_id
     * @return int $fake_id
     */
    function afi_fake_thumbnail_id( $thumbnail_id ) {
        global $post;
        $fake_id = null;

        if( get_post_type() == 'post' && get_post_status() == 'publish' && ! $thumbnail_id ) {
            $fake_id = 1;
            return $fake_id;
        } else {
            return $thumbnail_id;
        }
    }
    add_filter( 'post_thumbnail_id', 'afi_fake_thumbnail_id' );
}

if ( ! function_exists( 'afi_auto_thumbnails' ) ) {    
    /**
     * Summary of afi_auto_thumbnails
     *
     * If the Posts doesn't have $thumbnail_url then
     * search
     * 
     * @param  string $thumbnail_url
     * @return string $thumbnail_url
     */
    function afi_auto_thumbnails( $thumbnail_url ) {
        // $featured_image_exists = has_post_thumbnail($post->ID);
        if ( !$thumbnail_url ) {
            //$thumbnail_url = 'https://pbs.twimg.com/media/FRUNUjxXoAIpZN9?format=jpg&name=large';
    
    
    
            $queried_post = get_post();
            $first_image_in_post = '';
            ob_start();
            ob_end_clean();
            $output = preg_match_all('/<img.+src=[\'"]([^\'"]+)[\'"].*>/i', $queried_post->post_content, $matches);
            
            $first_image_in_post = '';
            
            if (isset($matches[1][0])) {$first_image_in_post = $matches[1][0];}
            $thumbnail_url = $first_image_in_post;
    
    
    
            // echo ' DOESNT EXIST ';
        }
        return $thumbnail_url;
        
    }
    add_filter( 'post_thumbnail_url', 'afi_auto_thumbnails' );
}
