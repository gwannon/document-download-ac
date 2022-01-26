<?php

//Administrador 
add_action( 'admin_menu', 'docDownloadAcPluginMenu' );
function docDownloadAcPluginMenu() {
	add_menu_page( __('Descargas AC', 'doc_download_ac'), __('Descargas AC', 'doc_download_ac'), 'manage_options', 'doc_download_ac', 'docDownloadAcAdmin');
}

function docDownloadAcAdmin() { 
  
  if ( ! current_user_can( 'manage_options' ) ) {
    return;
  }
  
  ?><h1><?php _e("Descargas  Active Campaign", "doc_download_ac"); ?></h1><?php 
	if(isset($_REQUEST['send']) && $_REQUEST['send'] != '') { 
		?><p style="border: 1px solid green; color: green; text-align: center;"><?php _e("Datos guardados correctamente.", "doc_download_ac"); ?></p><?php
		update_option('_doc_download_ac_media_url', $_POST['_doc_download_ac_media_url']);
    update_option('_doc_download_ac_api_domain', $_POST['_doc_download_ac_api_domain']);
    update_option('_doc_download_ac_api_token', $_POST['_doc_download_ac_api_token']);
    update_option('_doc_download_ac_extra_css', $_POST['_doc_download_ac_extra_css']);
    update_option('_doc_download_ac_default_autom', $_POST['_doc_download_ac_default_autom']);
    update_option('_doc_download_ac_default_list', $_POST['_doc_download_ac_default_list']);	
    update_option('_doc_download_ac_sheet_id', $_POST['_doc_download_ac_sheet_id']);
    update_option('_doc_download_ac_sheet_page', $_POST['_doc_download_ac_sheet_page']);	
    update_option('_doc_download_ac_sheet_service_key', $_POST['_doc_download_ac_sheet_service_key']);
    update_option('_doc_download_ac_api_tags_prefix', $_POST['_doc_download_ac_api_tags_prefix']);
    
    $f = fopen(dirname(__FILE__)."/service_key.json", "w+");
    fwrite($f, stripslashes($_POST['_doc_download_ac_sheet_service_key']));
    fclose($f);

	} ?>
  <p>
    <a href="<?php echo admin_url( "admin.php?page=".$_GET["page"] ); ?>"><?php _e("Configuración", "doc_download_ac"); ?></a> | 
    <a href="<?php echo admin_url( "admin.php?page=".$_GET["page"] ); ?>&view=tags"><?php _e("Etiquetas disponibles", "doc_download_ac"); ?></a> | 
    <a href="<?php echo admin_url( "admin.php?page=".$_GET["page"] ); ?>&view=lists"><?php _e("Listas disponibles", "doc_download_ac"); ?></a> | 
    <a href="<?php echo admin_url( "admin.php?page=".$_GET["page"] ); ?>&view=automations"><?php _e("Automatizaciones disponibles", "doc_download_ac"); ?></a> | 
    <a href="<?php echo admin_url( "admin.php?page=".$_GET["page"] ); ?>&view=media"><?php _e("Ficheros", "doc_download_ac"); ?></a>
  </p>

  <?php if(isset($_REQUEST['view']) && $_REQUEST['view'] != '') {
    include(__DIR__."/classes/curl.php");
    $items = array();
    if($_REQUEST['view'] == 'tags') {
      foreach (explode(",", get_option("_doc_download_ac_api_tags_prefix")) as $search) {
        $actags = curlCallGet("/tags?search=".$search);
        foreach ($actags->tags as $tag) {
          if(strtolower(substr($tag->tag, 0, strlen($search))) == $search) $items[$tag->id] = $tag->tag;
        }
      }
    } else if($_REQUEST['view'] == 'lists') {
      $aclists = curlCallGet("/lists");
      foreach ($aclists->lists as $list) {
        $items[$list->id] = $list->name;
      }
    } else if($_REQUEST['view'] == 'automations') {
      $acautomations = curlCallGet("/automations?search=doc-");
      foreach ($acautomations->automations as $automation) {
        $items[$automation->id] = $automation->name;
      }
    } else if($_REQUEST['view'] == 'media') {
      $media = json_decode(file_get_contents(get_option("_doc_download_ac_media_url")."/wp-json/wp/v2/media?search=.pdf&per_page=100"));
      foreach ($media as $pdf) {
        $items[$pdf->id] = "<a href='".$pdf->source_url."' target='_blank'>".$pdf->title->rendered."</a>";
      }
    } ?>
    <table border="1" cellpadding="5">
      <thead>
        <tr>
          <th>ID</th>
          <th>VALUE</th>
        </tr>
      </thead>
      <toby>
        <?php foreach($items as $key => $value) { ?>
          <tr>
            <td><?=$key;?></td>
            <td><?=$value;?></td>
          </tr>
        <?php } ?>
      </tbody>  
    </table>
    <?php
  } else { ?>
	<form action="<?php echo admin_url( "admin.php?page=".$_GET["page"] ); ?>" method="post">

    <table border="0" width="100%" cellpadding="5" cellspacing="0">
      <tr bgcolor="cecece">
        <td colspan="2"><h2><?php _e("Media", "doc_download_ac"); ?></h2></td>
      </tr>
      <tr bgcolor="cecece">
        <td width="25%" valign="top">
          <b><?php _e("URL del WordPress donde se almacenarán los ficheros a descargar", "doc_download_ac"); ?></b>
        </td>
        <td width="75%" valign="top">
          <input type="text" name="_doc_download_ac_media_url" value="<?php echo get_option("_doc_download_ac_media_url"); ?>" placeholder='https://spri.eus' style="width: 100%;" />
        </td>
      </tr>

      <tr>
        <td colspan="2"><h2><?php _e("Active Campaign", "doc_download_ac"); ?></h2></td>
      </tr>
      <tr>
        <td width="25%" valign="top">
          <b><?php _e("URL de la API de Active Campaign", "doc_download_ac"); ?></b>
        </td>
        <td width="75%" valign="top">
		      <input type="text" name="_doc_download_ac_api_domain" value="<?php echo get_option("_doc_download_ac_api_domain"); ?>" placeholder='https://domain.api-us1.com/api/3' style="width: 100%;" />
        </td>
      </tr>
      <tr>
        <td width="25%" valign="top">
          <b><?php _e("Token de la API de Active Campaign", "doc_download_ac"); ?></b>
        </td>
        <td width="75%" valign="top">
          <input type="text" name="_doc_download_ac_api_token" value="<?php echo get_option("_doc_download_ac_api_token"); ?>" style="width: 100%;" />
        </td>
      </tr>
      <tr>
        <td width="25%" valign="top">
          <b><?php _e("Prefijos etiquetas permitidas", "doc_download_ac"); ?></b>
        </td>
        <td width="75%" valign="top">
          <input type="text" name="_doc_download_ac_api_tags_prefix" value="<?php echo get_option("_doc_download_ac_api_tags_prefix"); ?>" placeholder='<?php _e("Separados por comas", 'doc_download_ac'); ?>' style="width: 100%;" />
        </td>
      </tr>

      <tr bgcolor="cecece">
        <td colspan="2"><h2><?php _e("Código corto", "doc_download_ac"); ?></h2></td>
      </tr>
      <tr bgcolor="cecece">
        <td width="25%" valign="top">
          <b><?php _e("ID de la automatización por defecto", "doc_download_ac"); ?></b>
        </td>
        <td width="75%" valign="top">
          <input type="text" name="_doc_download_ac_default_autom" value="<?php echo get_option("_doc_download_ac_default_autom"); ?>" placeholder='<?php _e("Separados por comas", 'doc_download_ac'); ?>' style="width: 100%;" />
        </td>
      </tr>
      <tr bgcolor="cecece">
        <td width="25%" valign="top">
          <h4><?php _e("ID de la lista por defecto", "doc_download_ac"); ?></h4>
        </td>
        <td width="75%" valign="top">
          <input type="text" name="_doc_download_ac_default_list" value="<?php echo get_option("_doc_download_ac_default_list"); ?>" placeholder='<?php _e("Separados por comas", 'doc_download_ac'); ?>' style="width: 100%;" />
        </td>
      </tr>

      <tr>
        <td colspan="2"><h2><?php _e("Google Sheets", "doc_download_ac"); ?></h2></td>
      </tr>
      <tr>
        <td width="25%" valign="top">
          <h4><?php _e("ID documento de Google Sheet", "doc_download_ac"); ?></h4>
        </td>
        <td width="75%" valign="top">
          <input type="text" name="_doc_download_ac_sheet_id" value="<?php echo get_option("_doc_download_ac_sheet_id"); ?>" style="width: 100%;" />
        </td>
      </tr>
      <tr>
        <td width="25%" valign="top">
          <h4><?php _e("ID de página del documento de Google Sheet", "doc_download_ac"); ?></h4>
        </td>
        <td width="75%" valign="top">
          <input type="text" name="_doc_download_ac_sheet_page" value="<?php echo get_option("_doc_download_ac_sheet_page"); ?>" style="width: 100%;" />
        </td>
      </tr>
      <tr>
        <td width="25%" valign="top">
          <h4><?php _e("Google Sheets Service Key", "doc_download_ac"); ?></h4>
        </td>
        <td width="75%" valign="top">
          <textarea name="_doc_download_ac_sheet_service_key" style="width: 100%; min-height: 150px;"><?php echo stripslashes(get_option("_doc_download_ac_sheet_service_key")); ?></textarea>
          <a href="https://pocketadmin.tech/en/version-4-of-the-google-sheets-api-in-php/" target="_blank">Instrucciones para obtener Service Key</a>
        
        </td>
      </tr>

      <tr bgcolor="cecece">
        <td colspan="2"><h2><?php _e("Diseño", "doc_download_ac"); ?></h2></td>
      </tr>
      <tr bgcolor="cecece">
        <td width="25%" valign="top">
          <h4><?php _e("Código CSS", "doc_download_ac"); ?></h4>
        </td>
        <td width="75%" valign="top">
          <textarea name="_doc_download_ac_extra_css" style="width: 100%; min-height: 150px;"><?php echo get_option("_doc_download_ac_extra_css"); ?></textarea>
        </td>
      </tr>
    </table>

    <br/><br/><input type="submit" name="send" class="button button-primary" value="<?php _e("Guardar", "doc_download_ac"); ?>" />
	</form>
	<?php }
}