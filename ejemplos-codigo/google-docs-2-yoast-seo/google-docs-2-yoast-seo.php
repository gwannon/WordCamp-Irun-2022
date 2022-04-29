<?php
/**
 * Plugin Name: Google Docs 2 Yoast SEO
 * Plugin URI:  https://github.com/gwannon/WordCamp-Irun-2022
 * Description: Permite modificar los titles de las páginas con Yoast SEO y una hoja de calculo de Google Docs 
 * Version:     1.0
 * Author:      gwannon
 * Author URI:  https://github.com/gwannon
 * License:     GNU General Public License v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: gd-2-ys
 *
 * PHP 7.4
 * WordPress 5.9.3
 */

/*
 *
 * Irun WordCamp 2022:
 * Permite modificar los titles de las páginas con Yoast SEO y una hoja de calculo de Google Docs
 * 
 */

function gd_2_ys_register_endpoints() {
  register_rest_route( 'wp', '/update-seo', array(
      'methods' => 'PUT',
      'callback' => 'gd_2_ys_update_seo',
      /*'permission_callback' => function ($request) {
        if (current_user_can('manage_options')) return true;
      }*/
    ) 
  );
}

function gd_2_ys_update_seo( $request ) {
  $body = $request->get_body_params();
  if(update_post_meta(get_page_by_path($body['slug'])->ID, '_yoast_wpseo_metadesc', $body['desc'])) return true;
  return false;
}

add_action( 'rest_api_init', 'gd_2_ys_register_endpoints' );