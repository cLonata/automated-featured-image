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
 * Description:       Automatically fetches Featured Image for Post which has no Featured Image set.
 * Version:           1.1.0
 * Requires at least: 5.2
 * Requires PHP:      7.1
 * Author:            cLonata
 * Author URI:        https://www.github.com/cLonata
 * Text Domain:       automated-featured-image
 * License:           The 3-Clause BSD License
 * License URI:       https://opensource.org/licenses/BSD-3-Clause
 * Update URI:        https://www.github.com/cLonata/automated-featured-image
 * 
 * Automated Featured Image is free software: you can redistribute it and/or modify
 * it under the terms of the BSD-3-Clause License as published by
 * the Open Source Initiative, either version 2 of the License, or
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
if ( !function_exists( 'afi_get_first_image' ) ) {
    function afi_get_first_image() {
        try {
            $queried_post = get_post();
            if ( ! $queried_post ) {
                return new WP_Error( 'post_not_found', __( 'The queried post could not be found.', 'afi' ) );
            }
            $dom = new DOMDocument();
            if ( ! $dom->loadHTML( $queried_post->post_content ) ) {
                return new WP_Error( 'html_parsing_error', __( 'An error occurred while parsing the HTML content of the post.', 'afi' ) );
            }
            $images = $dom->getElementsByTagName( 'img' );
            if ( $images->length > 0 ) {
                $thumbnail_url = $images[0]->getAttribute( 'src' );
                return $thumbnail_url;
            } else {
                return new WP_Error( 'image_not_found', __( 'No images were found in the post content.', 'afi' ) );
            }
        } catch( Exception $e ) {
            return new WP_Error( 'unexpected_error', sprintf( __( 'An unexpected error occurred: %s', 'afi' ), $e->getMessage() ) );
        }
    }
}

// If function of the same name already exist, abort.
if ( !function_exists( 'afi_get_video_thumbnail' ) ) {
    function afi_get_video_thumbnail( string $video_type, string $video_id, string $poster_image )
    {
        switch ($video_type) {
            case 'youtube':
                return 'http://img.youtube.com/vi/' . $video_id . '/0.jpg';
            case 'vimeo':
                $response = wp_remote_get("http://vimeo.com/api/v2/video/$video_id.php");
                if (is_wp_error($response)) {
                    return new WP_Error( 'vimeo_error', 'Error in retrieving data from Vimeo API' );
                }
                $hash = unserialize($response['body']);
                return $hash[0]['thumbnail_large'];
            case 'html5':
                return $poster_image;
            case 'daily':
                return 'http://www.dailymotion.com/thumbnail/video/' . $video_id;
            case 'facebook':
                return 'https://graph.facebook.com/' . $video_id . '/picture';
            default:
                return new WP_Error( 'invalid_video_type', 'Invalid video type' );
        }
    }
}

// If function of the same name already exist, abort.
if ( !function_exists( 'afi_get_gallery_thumbnail' ) ) {
    function afi_get_gallery_thumbnail( $slides ) {
        $image_id = $slides[0]['imgid'] ?? '';
        if ( ! empty( $image_id ) ) {
            $image_array = wp_get_attachment_image_src( $image_id, 'thumbnail' );
            return $image_array[0] ?? '';
        }
        return '';
    }
}

// If function of the same name already exist, abort.
if ( ! function_exists( 'afi_fake_thumbnail_id' ) ) {
    /**
     * Summary of afi_fake_thumbnail_id
     * 
     * If the Posts doesn't have $thumbnail_id then
     * create a fake $thumbnail_id for it in order
     * to bypass theme's placeholder images.
     *
     * @param  int $thumbnail_id
     * @return int $fake_id
     */
    function afi_fake_thumbnail_id( $thumbnail_id ) {
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

// If function of the same name already exist, abort.
if ( ! function_exists( 'afi_get_post_thumbnail_url' ) ) {
    /**
     * Summary of afi_get_post_thumbnail_url
     *
     * If the Posts doesn't have $thumbnail_url then
     * search
     * 
     * @param  string $thumbnail_url
     * @return string $thumbnail_url
     */
    function afi_get_post_thumbnail_url( $thumbnail_url ) {
        if ( $thumbnail_url ) {
            return $thumbnail_url;
        }
    
        $post_meta = get_post_meta( get_the_ID(), '', true );
        $post_format = get_post_format();
    
        switch ( $post_format ) {
            case 'video':
                if ( ! empty( $post_meta['video_type'] ) && ! empty( $post_meta['video_id'] ) ) {
                    $thumbnail_url = afi_get_video_thumbnail( 
                        $post_meta['video_type'], 
                        $post_meta['video_id'], 
                        $post_meta['html5_poster_img'] ?? ''
                    );
                }
                break;
            case 'gallery':
                $thumbnail_url = afi_get_gallery_thumbnail( $post_meta['slides'] ?? [] );
                break;
        }
    
        return $thumbnail_url ?: afi_get_first_image();
    }
    add_filter( 'post_thumbnail_url', 'afi_get_post_thumbnail_url' );
}