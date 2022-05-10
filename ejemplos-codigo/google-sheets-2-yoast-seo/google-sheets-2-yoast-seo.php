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
 * En Ajustes > Admin GS2YS poedemos configurar un token de seguridad que debemos usar el AppScript de Google Sheets
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
  
  $headers = $request->get_headers();
  if($headers['token'][0] == get_option("_gs_2_ys_token")) {
    $body = $request->get_body_params();
    $object = get_page_by_path($body['slug']);
    if($object->ID) {
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
    } else {
      return new WP_REST_Response(array("error" => "2", "message" => "No existe un objecto con ese slug"), 404);
    }
  } else {
    return new WP_REST_Response(array("error" => "1", "message" => "Token incorrecto"), 404);
  }
}
add_action( 'rest_api_init', 'gs_2_ys_register_endpoints' );

//Zona de admin -----------------
add_action( 'admin_menu', 'gs_2_ys_plugin_menu' );
function gs_2_ys_plugin_menu() {
	add_options_page( __('Administración Google Sheets 2 Yoast SEO', 'gs-2-ys'), __('Admin GS2YS', 'gs-2-ys'), 'manage_options', 'gs-2-ys', 'gs_2_ys_page_settings');
}

function gs_2_ys_page_settings() { 
	//echo "<pre>"; print_r($_REQUEST); echo "</pre>";
	if(isset($_REQUEST['send']) && $_REQUEST['send'] != '') { 
		update_option('_gs_2_ys_token', $_POST['_gs_2_ys_token']);
		?><p style="border: 1px solid green; color: green; text-align: center;"><?php _e("Datos guardados correctamente.", 'gs-2-ys'); ?></p><?php
	} ?>
	<form method="post">
		<h1><?php _e("Configuración de la conexión con API PKF Attest", 'gs-2-ys'); ?></h1>
    <input type="text" name="_gs_2_ys_token" value="<?php echo get_option("_gs_2_ys_token"); ?>" style="width: 100%" /><br/><br/>
    <button id="generatetoken"><?php _e("Generar token", 'gs-2-ys'); ?></button>
		<input type="submit" name="send" value="<?php _e("Send"); ?>" />
  </form>
  <script>
    jQuery("#generatetoken").click(function(e) {
      e.preventDefault();
      let token = generateToken(40);
      console.log(token);
      jQuery("input[name=_gs_2_ys_token]").val(token);
    });
    function generateToken(n) {
        var chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        var token = '';
        for(var i = 0; i < n; i++) {
            token += chars[Math.floor(Math.random() * chars.length)];
        }
        return token;
    }
  </script>
  <?php
}