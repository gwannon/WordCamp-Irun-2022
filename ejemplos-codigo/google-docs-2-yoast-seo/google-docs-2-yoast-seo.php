<?php
/**
 * Plugin Name: Google Sheets 2 Yoast SEO
 * Plugin URI:  https://github.com/gwannon/WordCamp-Irun-2022
 * Description: Permite modificar los titles y descripciones de Yoast SEO de las páginas y posts a través de una hoja de calculo de Google Sheets
 * Version:     1.0
 * Author:      gwannon
 * Author URI:  https://github.com/gwannon
 * License:     GNU General Public License v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: gs-2-ys
 *
 * PHP 7.4
 * WordPress 5.9.3
 */

/*
 *
 * Irun WordCamp 2022:
 * Permite modificar los titles y descripciones de Yoast SEO de las páginas y posts a través de una hoja de calculo de Google Sheets
 * 
 */

/*
 *
 * ToDo: Validación por contraseña.
 * 
 */

function gs_2_ys_register_endpoints() {
  register_rest_route( 'wp', '/update-seo', array(
      'methods' => 'POST',
      'callback' => 'gs_2_ys_update_seo',
    ) 
  );
}

function gs_2_ys_update_seo( $request ) {
  $body = $request->get_body_params();
  $object = get_page_by_path($body['slug']);
  if(isset($body['title']) && $body['title'] != '' && update_post_meta($object->ID, '_yoast_wpseo_title', $body['title'])) {
    $status['title'] = "Modificado";
  } else {
    $status['title'] = "NO modificado";
  } 
  if(isset($body['desc']) && $body['desc'] != '' && update_post_meta($object->ID, '_yoast_wpseo_metadesc', $body['desc'])) {
    $status['desc'] = "Modificado";
  } else {
    $status['desc'] = "NO modificado";
  }  
  return $status;
}
add_action( 'rest_api_init', 'gs_2_ys_register_endpoints' );