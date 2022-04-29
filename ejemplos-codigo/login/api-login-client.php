<?php
/**
 * Plugin Name: API Rest Login Cliente
 * Plugin URI:  https://github.com/gwannon/WordCamp-Irun-2022
 * Description: Código corto que genera un formulario que se logea contra otro wordpress que tenga el plugin api-login.php
 * Version:     1.0
 * Author:      gwannon
 * Author URI:  https://github.com/gwannon
 * License:     GNU General Public License v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: api-login-client
 *
 * PHP 7.4
 * WordPress 5.9.3
 */

/*
 *
 * Irun WordCamp 2022:
 * Código corto que genera un formulario que se logea contra otro wordpress que tenga el plugin api-login.php
 * [api-login-client dominio="https://midominio.com"]<h1>Puedes ver esto por habeer metido un usuario y contraseña correcto.</h1>[/api-login-client]
 * 
 */

function api_login_client_shortcode ($params = array(), $content = null) {
  ob_start();
  if(isset($_POST['username']) && $_POST['username'] != '') {
    $arrContextOptions=array(
      "ssl"=>array(
        "verify_peer"=>false,
        "verify_peer_name"=>false,
      ),
    ); 
    $current_user = json_decode(file_get_contents($params['dominio']."/wp-json/wp/login?username=".urlencode($_POST['username'])."&password=".urlencode($_POST['password']), false, stream_context_create($arrContextOptions)));
    if(isset($current_user->ID)) echo $content;
    else echo $current_user->message;
  } else { ?>
  <form method="post">
    <input type="text" name="username" placeholder="<?=__("username") ?>" />
    <input type="password" name="password" placeholder="<?=__("password") ?>" />
    <input type="submit" value="<?=__("login") ?>" />
  </form>
  <?php }
  return ob_get_clean(); 
}
add_shortcode('api-login-client', 'api_login_client_shortcode');