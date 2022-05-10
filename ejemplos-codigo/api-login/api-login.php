<?php
/**
 * Plugin Name: API Rest Login
 * Plugin URI:  https://github.com/gwannon/WordCamp-Irun-2022
 * Description: Permite crear un endpoint /wp-json/wp/login para hacer logeos
 * Version:     1.0
 * Author:      gwannon
 * Author URI:  https://github.com/gwannon
 * License:     GNU General Public License v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: api-login
 *
 * PHP 7.4
 * WordPress 5.9.3
 */

/*
 *
 * Irun WordCamp 2022:
 * Permite crear un endpoint /wp-json/wp/login GET para hacer logeos pasando los parámetros username y password
 * https://stackoverflow.com/questions/13679001/register-login-user-with-wordpress-json-api
 * ¡¡¡OJO!!!! Nada de implementar este ejemplo sin securizarlo. Ahora mismo es un agujero de seguridad muy grande.
 * 
 */

function api_login_register_endpoints() {
  register_rest_route( 'wp', '/login', array(
      'methods' => 'GET',
      'callback' => 'api_login_login',
    ) 
  );
}

function api_login_login( $request ) {
  $data =[
    'user_login' => $request["username"],
    'user_password' =>  $request["password"],
    'remember' => true
  ];
  $user = wp_signon( $data, false );
  if ( !is_wp_error($user) ) return $user;
  else {
    return new WP_REST_Response(array("error" => "1", "message" => "Usuario y contraseña incorrectos"), 202);
  }
}

add_action( 'rest_api_init', 'api_login_register_endpoints' );
