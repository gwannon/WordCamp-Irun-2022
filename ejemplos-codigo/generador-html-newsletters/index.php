<?php
/**
 *
 * Irun WordCamp 2022:
 * Generador de html para mandar por MailChimp, MailRelay, HubSpot, Active Campaign, ...
 * 
 */

 ini_set("display_errors", 1);

$dominios = [
  "https://realunionclub.com",
  "http://erroibide.org",
  "https://www.cdbidasoa.eus",
  "https://cdsanmarcialirun.com"
];
$posts = getPosts($dominios);
?>
<html>
<head>
  <title>Generador de boletines</title>
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=0">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-BmbxuPwQa2lc/FVzBcNJ7UAyJxM6wuqIj61tLrc4wSX0szH/Ev+nYRRuWlolflfl" crossorigin="anonymous">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta2/dist/js/bootstrap.bundle.min.js" integrity="sha384-b5kHyXgcpbZJO/tY9Ul7kGkf1S0CWuKcCD38l8YkeH8z8QjE0GmW1gYU5S9FOnJ0" crossorigin="anonymous"></script>
</head>
<body>
  <section>
    <div class="container">
      <div class="row">
        <div class="col-12 p-3 text-center">
          <h1>Generador de boletines</h1>
        </div>
        <?php if(isset($_REQUEST['generar'])) {
          $newsletter = generateNewsletterHtml($intereses, $posts, $lang); ?>
          <p class="text-center alert alert-success">Newsletter generada correctamente</p>
          <iframe style='width: 100%; height: 33vh;' src="temp.html"></iframe>
          <textarea style='width: 100%; height: 33vh;'><?=$newsletter ?></textarea>
        <?php } ?>
        <form method="post">
          <div id="noticias" class="p-0 m-0">
            <?php for($i = 0; $i < 5; $i++) { ?>
              <div id="post_<?php echo $i; ?>" class="col-12 p-1 noticia_drag" order="<?php echo $i; ?>">
                <div class="p-3 rounded-3 noticia">
                  <div class="row">
                    <div class="col-12"><h5>Noticia <?php echo ($i + 1); ?></h5></div>
                    <div class="col-12">
                      <select name="data[<?php echo $i; ?>][post_id]" data-post-id="<?php echo $i; ?>">
                        <option value="-1">Elegir noticia</option>
                        <?php foreach($posts as $key => $post) { $parse = parse_url($post->link);?>
                          <option value="<?php echo $key; ?>"<?php if(isset($_REQUEST['data'][$i]['post_id']) && intval($_REQUEST['data'][$i]['post_id']) == intval($key)) echo " selected='selected'"; ?>><?php echo date("Y-m-d", strtotime($post->date)); ?> - <?=$parse['host']?> - <?php echo $post->title->rendered; ?></option>
                        <?php } ?>
                      </select>
                      <select name="data[<?php echo $i; ?>][format]">>
                        <option value="">Elegir formato</option>
                        <option value="big"<?php echo ($_REQUEST['data'][$i]['format'] == 'big' ? " selected='selected'" : ""); ?>>GRANDE</option>
                        <option value="medium"<?php echo ($_REQUEST['data'][$i]['format'] == 'medium' ? " selected='selected'" : ""); ?>>MEDIANO</option>
                        <option value="small"<?php echo ($_REQUEST['data'][$i]['format'] == 'small' ? " selected='selected'" : ""); ?>>PEQUEÑO</option>
                      </select>
                    </div>
                  </div>
                </div>
              </div>
            <?php } ?>
          </div>
          <input type="submit" name="generar" value="Crear HTML" />
        </form>
      </div>
    </div>
  </section>
</body>
</html><?php

function getPosts($dominios) {
  $posts = array();
  foreach($dominios as $dominio) {
    $arrContextOptions = array(
      "ssl"=>array(
        "verify_peer"=>false,
        "verify_peer_name"=>false,
      ),
    );
    foreach (json_decode(file_get_contents($dominio."/wp-json/wp/v2/posts?orderby=date&order=desc&after=".date('Y-m-d', strtotime('-30 days'))."T00:00:00&per_page=100", false, stream_context_create($arrContextOptions))) as $post) {
      $posts[] = $post;
    }
  }  
  return $posts;
}

function generateNewsletterHtml($intereses, $posts, $lang) {

  //Sacamos todas las plantillas
  $files = scandir("./templates/");
  foreach ($files as $file)  {
    $file_parts = pathinfo($file);
    if($file_parts['extension'] == 'html') {
      $template[$file_parts['filename']] =  file_get_contents ("./templates/".$file);
    }
  }

  $arrContextOptions=array(
    "ssl"=>array(
      "verify_peer"=>false,
      "verify_peer_name"=>false,
    ),
  ); 
  
  //Noticias ------------------
  $news = '';
  foreach ($_REQUEST['data'] as $item ) {
    if($item['post_id'] >= 0 && $item['format'] != '') {
      $parse = parse_url($posts[$item['post_id']]->link);
      $temp = str_replace("[text]", strip_tags($posts[$item['post_id']]->excerpt->rendered), str_replace("[link]", $posts[$item['post_id']]->link, str_replace("[title]", $posts[$item['post_id']]->title->rendered, $template[$item['format']])));
      $cat = json_decode(file_get_contents($parse['scheme']."://".$parse['host']."/wp-json/wp/v2/categories/". $posts[$item['post_id']]->categories[0], false, stream_context_create($arrContextOptions)));
      $temp = str_replace("[category]", $cat->name, $temp);
      $current_image = json_decode(file_get_contents($parse['scheme']."://".$parse['host']."/wp-json/wp/v2/media/". $posts[$item['post_id']]->featured_media, false, stream_context_create($arrContextOptions)));
      if($current_image->media_details->sizes->medium_large) $temp = str_replace("[image]", $current_image->media_details->sizes->medium_large->source_url, $temp);
      else $temp = str_replace("[image]", $current_image->media_details->sizes->full->source_url, $temp);
    } else $temp = "";
    $news .= $temp;
  }

  //Generamos la plantilla
  $newsletter = str_replace("[NEWS]", $news, $template['mail']);
  file_put_contents("temp.html", $newsletter);
  return $newsletter;
}