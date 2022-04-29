<?php
/**
 * Plugin Name: API Rest Buscador Multidominio
 * Plugin URI:  https://github.com/gwannon/WordCamp-Irun-2022
 * Description: Código corto que genera un buscador multidominio
 * Version:     1.0
 * Author:      gwannon
 * Author URI:  https://github.com/gwannon
 * License:     GNU General Public License v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: buscador_multidominio
 *
 * PHP 7.4
 * WordPress 5.9.3
 */

/*
 *
 * Irun WordCamp 2022:
 * Código corto que genera un buscador multidominio
 * [buscador_multidominio dominios="https://realunionclub.com,http://erroibide.org,https://www.cdbidasoa.eus,https://cdsanmarcialirun.com" per_page="10"]
 * 
 */

function buscador_multidominio ($params = array(), $content = null) {
  ob_start(); ?>
  <form id="buscador_multidominio" methos="get">
    <input type="text" name="mds" value="<?=(isset($_REQUEST['mds']) ? strip_tags($_REQUEST['mds']) : "")?>" />
    <input type="submit" value="<?=__("Search") ?>" />
  </form>
  <div id="buscador_multidominio_resultados"></div>
  <script>
     jQuery("#buscador_multidominio").submit(function(e) {
      e.preventDefault();
      if (jQuery("#buscador_multidominio input[name=mds]").val() != '') {
        jQuery.ajax({
          url : '<?=admin_url('admin-ajax.php')?>',
          data : { 
            mds: jQuery("#buscador_multidominio input[name=mds]").val(),
            action: 'buscador_multidominio',
            dominios: '<?=$params['dominios']?>',
            per_page: <?=(isset($params['per_page']) ? $params['per_page'] : 10)?>
          },
          type : 'GET',
          dataType : 'json',
          beforeSend: function () {
            jQuery("#buscador_multidominio_resultados").empty();
            jQuery("#buscador_multidominio_resultados").html("<?=__("Loading ...", 'buscador_multidominio') ?>");
          },
          success : function(json) {
            jQuery("#buscador_multidominio_resultados").empty();
            if(json.lenght) {
              json.forEach(function(data, index) {
                jQuery("#buscador_multidominio_resultados").append("<h3><a href='"+data.link+"'>"+data.title+"</a></h3><p>"+data.date+" - "+data.dominio+"</p>"+data.excerpt+"<hr/>");
              });
            } else jQuery("#buscador_multidominio_resultados").append("<?=__("No results.", 'buscador_multidominio') ?>");
          },
          error : function(xhr, status) {
            jQuery("#buscador_multidominio_resultados").empty();
            jQuery("#buscador_multidominio_resultados").append("<?=__("Sorry, there is a error.", 'buscador_multidominio') ?>");
          }
        });
      }
    });
  </script>
  <?php return ob_get_clean(); 
}
add_shortcode('buscador_multidominio', 'buscador_multidominio');

function buscador_multidominio_ajax(){
  $wp_formato_fecha = get_option('date_format');
  $posts = array();
  if(isset($_GET['mds']) && $_GET['mds'] != '') { 
    foreach(explode(",", $_GET['dominios']) as $dominio) {
      $curl = curl_init();
      curl_setopt_array($curl, array(
        CURLOPT_URL => $dominio."/wp-json/wp/v2/posts?search=".urlencode($_GET['mds'])."&per_page=".(isset($_GET['per_page']) ? $_GET['per_page'] : 10),
        CURLOPT_RETURNTRANSFER => true,
        //CURLOPT_ENCODING => "",
        //CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 5,
        //CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_SSL_VERIFYHOST => false,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_FOLLOWLOCATION => true
      ));
      foreach (json_decode(curl_exec($curl)) as $post) {
        $posts[] = [
          "title" => $post->title->rendered,
          "link" => $post->link,
          "excerpt" => $post->excerpt->rendered,
          "date" => wp_date($wp_formato_fecha, strtotime($post->date)),
          "timestamp" => strtotime($post->date),
          "dominio" => $dominio
        ];
      }
    }
    usort($posts, function ($a, $b) { return $b['timestamp'] - $a['timestamp']; });
    $posts = array_slice($posts, 0, (isset($_GET['per_page']) ? $_GET['per_page'] : 10));
  }
  if(count($posts)) wp_send_json($posts);
}
add_action('wp_ajax_nopriv_buscador_multidominio', 'buscador_multidominio_ajax');
add_action('wp_ajax_buscador_multidominio', 'buscador_multidominio_ajax');