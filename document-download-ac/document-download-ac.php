<?php
/**
 * Plugin Name: Document Download Active Campaign
 * Plugin URI:  https://www.enutt.net/
 * Description: Codigo corto para la descarga de documentos.
 * Version:     1.1
 * Author:      Eñutt
 * Author URI:  https://www.enutt.net/
 * License:     GNU General Public License v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: doc_download_ac
 *
 * PHP 7.3
 * WordPress 5.5.3
 */

ini_set("display_errors", 1);


register_activation_hook( __FILE__, 'docDownloadAcActivatePlugin');
function docDownloadAcActivatePlugin() {
  if (!file_exists(dirname(__FILE__).'/logs/')) {
    mkdir(dirname(__FILE__).'/logs/', 0777, true);
  }
}

 
function docDownloadAcPluginsLoaded() {
  load_plugin_textdomain('doc_download_ac', false, dirname( plugin_basename( __FILE__ ) ) . '/langs/' );
}
add_action('plugins_loaded', 'docDownloadAcPluginsLoaded', 0 );

include(__DIR__."/admin.php");
include(__DIR__."/classes/lib.php");

define('MEDIA_API_DOMAIN', get_option("_doc_download_ac_media_url")); //URL de la API de Wordpress donde están los documentos
define('AC_API_DOMAIN', get_option("_doc_download_ac_api_domain")); //URL de la API de Active Campaign
define('AC_API_TOKEN', get_option("_doc_download_ac_api_token")); //Token de la API de Active Campaign

$fields = array();
$tags = array();
$lists = array();

function docDownloadAcForm($params = array(), $content = null) {
  global $post;
  ob_start();
  $media = docDownloadAcGetMediaById($params['doc_id']);
  if(!$media) return ''; //Chequeamos que exista el archivo ?>


  <button class="ac_download-pop-up-open pop-up-open<?=$media['id']; ?><?=" ".$params['class']; ?>"><?=$content; ?></button>
  <div class="ac_download-pop-up-bg pop-up-bg<?=$media['id']; ?><?=" ".$params['class']; ?>">
    <div class="pop-up">
      <div class="pop-up-close">✕</div>
      <h3><?php echo (isset($params['title']) && $params['title'] != '' ? $params['title'] : sprintf(__("Descarga de %s", "doc_download_ac"), $media['title'] )); ?></h3>
      <p><?php echo (isset($params['text']) && $params['text'] != '' ? $params['text'] : sprintf(__("Solicita la descarga del documento %s. En breve, te lo enviaremos por correo electrónico.", "doc_download_ac"), $media['title'] )); ?></p>
      <form class="ac_download-form" id="ac_download<?=$media['id']; ?>">
        <div class="ac_download-innerform">
          <input type="hidden" name="media_id" value="<?=$media['id']; ?>" />
          <input type="hidden" name="lists" value="<?php echo (isset($params['list']) ? $params['list'] : get_option("_doc_download_ac_default_list")); ?>" />
          <input type="hidden" name="automations" value="<?php echo (isset($params['automation']) ? $params['automation'] : get_option("_doc_download_ac_default_autom")); ?>" />   
          <input type="hidden" name="tags" value="<?=$params['tag']; ?>" />
          <?php if($params['format'] >= 2) { ?>
            <input type="text" name="firstname" value="" placeholder='<?php _e("Nombre*", "doc_download_ac"); ?>' required /><br/>
            <input type="text" name="lastname" value="" placeholder='<?php _e("Apellidos*", "doc_download_ac"); ?>' required /><br/>
          <?php } ?>
          <input type="email" name="email" value="" placeholder='<?php _e("Email*", "doc_download_ac"); ?>' required /><br/>
          <?php if($params['format'] >= 3) { ?>
            <input type="text" name="dni" value="" placeholder='<?php _e("DNI", "doc_download_ac"); ?>' /><br/>
            <input type="text" name="company" value="" placeholder='<?php _e("Empresa*", "doc_download_ac"); ?>' required /><br/>
            <input type="text" name="company_cif" value="" placeholder='<?php _e("CIF", "doc_download_ac"); ?>' /><br/>
            <select name="company_country" required>
              <option value=""><?php _e("País"); ?></option>
              <option value="España"><?php _e("España"); ?></option>      
              <option value="Francia"><?php _e("Francia"); ?></option>      
              <option value="Alemania"><?php _e("Alemania"); ?></option>      
              <option value="Portugal"><?php _e("Portugal"); ?></option>      
            </select><br/>          
            <select name="company_state_spain" style="display: none;" disabled="disabled">
              <option value=""><?php _e("Provincia"); ?></option>
              <option value="Araba"><?php _e("Araba/Álava"); ?></option>
              <option value="Albacete"><?php _e("Albacete"); ?></option>
              <option value="Alicante"><?php _e("Alicante-Alacant"); ?></option>
              <option value="Almería"><?php _e("Almería"); ?></option>
              <option value="Asturias"><?php _e("Asturias"); ?></option>
              <option value="Ávila"><?php _e("Ávila"); ?></option>
              <option value="Badajoz"><?php _e("Badajoz"); ?></option>
              <option value="Barcelona"><?php _e("Barcelona"); ?></option>
              <option value="Burgos"><?php _e("Burgos"); ?></option>
              <option value="Cáceres"><?php _e("Cáceres"); ?></option>
              <option value="Cádiz"><?php _e("Cádiz"); ?></option>
              <option value="Cantabria"><?php _e("Cantabria"); ?></option>
              <option value="Castellón"><?php _e("Castellón-Castelló"); ?></option>
              <option value="Ceuta"><?php _e("Ceuta"); ?></option>
              <option value="Ciudad Real"><?php _e("Ciudad Real"); ?></option>
              <option value="Córdoba"><?php _e("Córdoba"); ?></option>
              <option value="A Coruña"><?php _e("A Coruña"); ?></option>
              <option value="Cuenca"><?php _e("Cuenca"); ?></option>
              <option value="Girona"><?php _e("Girona"); ?></option>
              <option value="Granada"><?php _e("Granada"); ?></option>
              <option value="Guadalajara"><?php _e("Guadalajara"); ?></option>
              <option value="Gipuzkoa"><?php _e("Gipuzkoa"); ?></option>
              <option value="Huelva"><?php _e("Huelva"); ?></option>
              <option value="Huesca"><?php _e("Huesca"); ?></option>
              <option value="Islas Baleares"><?php _e("Illes Balears"); ?></option>
              <option value="Jaén"><?php _e("Jaén"); ?></option>
              <option value="León"><?php _e("León"); ?></option>
              <option value="Lleida"><?php _e("Lleida"); ?></option>
              <option value="Lugo"><?php _e("Lugo"); ?></option>
              <option value="Madrid"><?php _e("Madrid"); ?></option>
              <option value="Málaga"><?php _e("Málaga"); ?></option>
              <option value="Melilla"><?php _e("Melilla"); ?></option>
              <option value="Murcia"><?php _e("Murcia"); ?></option>
              <option value="Navarra"><?php _e("Navarra"); ?></option>
              <option value="Ourense"><?php _e("Orense"); ?></option>
              <option value="Palencia"><?php _e("Palencia"); ?></option>
              <option value="Las Palmas"><?php _e("Las Palmas"); ?></option>
              <option value="Pontevedra"><?php _e("Pontevedra"); ?></option>
              <option value="La Rioja"><?php _e("La Rioja"); ?></option>
              <option value="Salamanca"><?php _e("Salamanca"); ?></option>
              <option value="Segovia"><?php _e("Segovia"); ?></option>
              <option value="Sevilla"><?php _e("Sevilla"); ?></option>
              <option value="Soria"><?php _e("Soria"); ?></option>
              <option value="Tarragona"><?php _e("Tarragona"); ?></option>
              <option value="Santa Cruz de Tenerife"><?php _e("Santa Cruz de Tenerife"); ?></option>
              <option value="Teruel"><?php _e("Teruel"); ?></option>
              <option value="Toledo"><?php _e("Toledo"); ?></option>
              <option value="Valencia"><?php _e("Valencia-València"); ?></option>
              <option value="Valladolid"><?php _e("Valladolid"); ?></option>
              <option value="Bizkaia"><?php _e("Bizkaia"); ?></option>
              <option value="Zamora"><?php _e("Zamora"); ?></option>
              <option value="Zaragoza"><?php _e("Zaragoza"); ?></option>   
            </select>
            <input type="text" name="company_state" value="" placeholder='<?php _e("Provincia*", "doc_download_ac"); ?>' disabled="disabled" required />
            <br/>
            <input type="text" name="company_city" value="" placeholder='<?php _e("Ciudad*", "doc_download_ac"); ?>' required /><br/>
          <?php } ?>
          <?php if(isset($params['update']) && $params['update'] == 1) { ?><input type="checkbox" name="update" value="1" /> <?php _e("Deseo recibir actualizaciones de este documento.", "doc_download_ac"); ?><br/><?php } ?>
          <input type="checkbox" name="privacy" value="1" required /> <?php _e("Acepto la <a href='' target='_blank'>politica de privacidad</a>.", "doc_download_ac"); ?><br/>
          <button type="submit" name="download"><?php _e("Descargar", "doc_download_ac"); ?></button>
        </div>
        <p class="ac_download-response"></p>
      </form>
    </div>
  </div>
  <style>
    /* ---------------- PopUps --------------- */
    .pop-up-bg<?=$media['id']; ?> {
      position: fixed;
      top: 0px;
      left: 0px;
      width: 100%;
      height: 100vh;
      z-index: 1000;
      background-color: #4a4a4abf;
      display: none;
    }

    .pop-up-bg<?=$media['id']; ?>.opened {
      display: flex;
      justify-content: center;
      align-items: center;
    }

    .pop-up-bg<?=$media['id']; ?> .pop-up {
      background-color: #efefef;
      border-radius: 0px;
      position: relative;
      max-width: 862px;
      /*width: 100%;
      max-height: 559px;
      height: 100vh;*/
      padding: 50px;
    }

    .pop-up-bg<?=$media['id']; ?> .pop-up-close {
      right: 0px;
      position: absolute;
      color: #000;
      font-size: 35px;
      width: 41px;
      cursor: pointer;
      top: 16px;
    }

    .pop-up-bg<?=$media['id']; ?> .ac_download-response {
      height: 0px;
      padding: 0px;
    }

    .pop-up-bg<?=$media['id']; ?> .ac_download-response.loading {
      background: transparent url(/wp-content/plugins/document-download-ac/assets/images/loader.gif) center left no-repeat;
      background-size: contain;
      height: 20px;
    }

    /* Extra CSS */
    <?php echo get_option("_doc_download_ac_extra_css"); ?>
  </style>
  <script>
    jQuery(".pop-up-open<?=$media['id']; ?>, .pop-up-bg<?=$media['id']; ?> .pop-up-close").click(function(e) {
      e.preventDefault();
      jQuery(".pop-up-bg<?=$media['id']; ?>").toggleClass("opened");
      jQuery("#ac_download<?=$media['id']; ?> .ac_download-response").html("");
      jQuery("#ac_download<?=$media['id']; ?> .ac_download-response").removeClass("loading");
      jQuery("#ac_download<?=$media['id']; ?> .ac_download-innerform").css("display", "block");
    });

    jQuery("#ac_download<?=$media['id']; ?> select[name=company_country]").change(function() {
      if(jQuery(this).val() == 'España') {
        jQuery("#ac_download<?=$media['id']; ?> input[name=company_state]").css("display", "none");
        jQuery("#ac_download<?=$media['id']; ?> input[name=company_state]").prop('disabled', true);
        jQuery("#ac_download<?=$media['id']; ?> select[name=company_state_spain]").css("display", "inline-block");
        jQuery("#ac_download<?=$media['id']; ?> select[name=company_state_spain]").prop('disabled', false);
      } else {
        jQuery("#ac_download<?=$media['id']; ?> input[name=company_state]").css("display", "inline-block");
        jQuery("#ac_download<?=$media['id']; ?> input[name=company_state]").prop('disabled', false);
        jQuery("#ac_download<?=$media['id']; ?> select[name=company_state_spain]").css("display", "none");
        jQuery("#ac_download<?=$media['id']; ?> select[name=company_state_spain]").prop('disabled', true);
      }

    });

		jQuery("#ac_download<?=$media['id']; ?>").submit(function(event) {
			event.preventDefault();
      //console.log(jQuery("#ac_download<?=$media['id']; ?> select[name=company_country]").val());
      jQuery.ajax({
        url : '/wp-admin/admin-ajax.php',
        data : {
          action: 'doc_download_ac',
          hash: '<?php echo docDownloadAcGenerateHash($media['id'], $params['tag'], (isset($params['automation']) ? $params['automation'] : get_option("_doc_download_ac_default_autom")), (isset($params['list']) ? $params['list'] : get_option("_doc_download_ac_default_list")), $params['topic'], $params['sector'], $params['country']); ?>',
          media_id: jQuery("#ac_download<?=$media['id']; ?> input[name=media_id]").val(),
          tags: jQuery("#ac_download<?=$media['id']; ?> input[name=tags]").val(),
          lists: jQuery("#ac_download<?=$media['id']; ?> input[name=lists]").val(),
          automations: jQuery("#ac_download<?=$media['id']; ?> input[name=automations]").val(),
          email: jQuery("#ac_download<?=$media['id']; ?> input[name=email]").val(),
          <?php if($params['format'] >= 2) { ?>firstname: jQuery("#ac_download<?=$media['id']; ?> input[name=firstname]").val(),<?php } ?>
          <?php if($params['format'] >= 2) { ?>lastname: jQuery("#ac_download<?=$media['id']; ?> input[name=lastname]").val(),<?php } ?>
          <?php if($params['format'] >= 3) { ?>dni: jQuery("#ac_download<?=$media['id']; ?> input[name=dni]").val(),<?php } ?>
          <?php if($params['format'] >= 3) { ?>company: jQuery("#ac_download<?=$media['id']; ?> input[name=company]").val(),<?php } ?>
          <?php if($params['format'] >= 3) { ?>company_cif: jQuery("#ac_download<?=$media['id']; ?> input[name=company_cif]").val(),<?php } ?>
          <?php if($params['format'] >= 3) { ?>company_city: jQuery("#ac_download<?=$media['id']; ?> input[name=company_city]").val(),<?php } ?>
          <?php if($params['format'] >= 3) { ?>company_state: (jQuery("#ac_download<?=$media['id']; ?> select[name=company_country]").val() == 'España' ? jQuery("#ac_download<?=$media['id']; ?> select[name=company_state_spain]").val() : jQuery("#ac_download<?=$media['id']; ?> input[name=company_state]").val() ),<?php } ?>
          <?php if($params['format'] >= 3) { ?>company_country: jQuery("#ac_download<?=$media['id']; ?> select[name=company_country]").val(),<?php } ?>
          <?php if(isset($params['topic']) && $params['topic'] != "") { ?>topic: '<?=$params['topic']; ?>',<?php } ?>
          <?php if(isset($params['sector']) && $params['sector'] != "") { ?>sector: '<?=$params['sector']; ?>',<?php } ?>
          <?php if(isset($params['country']) && $params['country'] != "") { ?>country: '<?=$params['country']; ?>',<?php } ?>
          <?php if(isset($params['update']) && $params['update'] == 1) { ?>update: (jQuery("#ac_download<?=$media['id']; ?> input[name=update]").is(':checked') ? "1" : ""),<?php } ?>
        },
        type : 'GET',
        dataType : 'json',
        beforeSend: function () {
          //console.log("Comienza");
          jQuery("#ac_download<?=$media['id']; ?> .ac_download-response").html("");
          jQuery("#ac_download<?=$media['id']; ?> .ac_download-response").addClass("loading");
          jQuery("#ac_download<?=$media['id']; ?> button").prop('disabled', true);
        },
        success : function(json) {
          //console.log("Exito");
          jQuery("#ac_download<?=$media['id']; ?> .ac_download-innerform").css("display", "none");
          jQuery("#ac_download<?=$media['id']; ?> input[name=email], #ac_download<?=$media['id']; ?> input[name=firstname], #ac_download<?=$media['id']; ?> input[name=lastname], #ac_download<?=$media['id']; ?> input[name=dni], #ac_download<?=$media['id']; ?> input[name=company], #ac_download<?=$media['id']; ?> input[name=company_cif], #ac_download<?=$media['id']; ?> input[name=company_city], #ac_download<?=$media['id']; ?> input[name=company_state]").val("");
          jQuery("#ac_download<?=$media['id']; ?> select[name=company_country]").val("");
          jQuery("#ac_download<?=$media['id']; ?> select[name=company_state_spain]").val("");
          jQuery("#ac_download<?=$media['id']; ?> input[type=checkbox]").prop( "checked", false );
          jQuery("#ac_download<?=$media['id']; ?> .ac_download-response").html(json.data.message);
        },
        error : function(xhr, status) {
          //console.log("Error");
          jQuery("#ac_download<?=$media['id']; ?> .ac_download-response").html("<?php _e("Lo sentimos, ha ocurrido un error. Vuelve a intentarlo dentro de un rato.", "doc_download_ac"); ?>");
        },
        complete : function(xhr, status) {
          //console.log("Fin");
          jQuery("#ac_download<?=$media['id']; ?> .ac_download-response").removeClass("loading");
          jQuery("#ac_download<?=$media['id']; ?> button").prop('disabled', false);
        }
      });
		});
  </script>
  <?php
  $html = ob_get_clean(); 
  return $html;
}
add_shortcode('doc_download_ac', 'docDownloadAcForm');


//AJAX ----------------------
function docDownloadAcAjax() {
  global $fields, $tags, $lists;

  foreach ($_REQUEST as $label => $value) { //Limpiamos la URL por si hubiera cosas raras
    $_REQUEST[$label] = strip_tags($value);
  }
  
  if(docDownloadAcGenerateHash($_REQUEST['media_id'], $_REQUEST['tags'], $_REQUEST['automations'], $_REQUEST['lists'], $_REQUEST['topic'], $_REQUEST['sector'], $_REQUEST['country']) != $_REQUEST['hash']) {
    $error = new WP_Error( '1', 'Incorrect hash. '.docDownloadAcGenerateHash($_REQUEST['media_id'], $_REQUEST['tags'], $_REQUEST['automations'], $_REQUEST['lists'], $_REQUEST['topic'], $_REQUEST['sector'], $_REQUEST['country']).' = '.$_REQUEST['hash'] );
    wp_send_json_error( $error, 500);
  }
  include(__DIR__."/classes/user.php");
  include(__DIR__."/classes/curl.php");

  $fields = [
    "DOC_url" => 47, //%DOCURL%
    "DOC_nombre" => 48, //%DOCNOMBRE%
    "DNI" => 42,
    "Empresa" => 41,
    "Empresa_CIF" => 43,
    "Empresa_provincia" => 7
  ];

  $tags = docDownloadAcGetTags();
  
  $lists = array();
  $aclists = curlCallGet("/lists");
  foreach ($aclists->lists as $list) {
    $lists[] = $list->id;
  }

  //Obtenemos el usuario y el medio
  $user = new UserAC($_REQUEST['email']);
  $media = docDownloadAcGetMediaById($_REQUEST['media_id']);

  //Guardamos el contact_id en una cookie
  setcookie('contact_id', $user->id, time()+get_option("_doc_download_cookie_time"), "/", get_option("_doc_download_cookie_domain"));

  //Establecemos los datos del usuario y del documento
  if(isset($_REQUEST['firstname']) && $_REQUEST['firstname'] != '' && $user->nombre == '') $user->setNombre($_REQUEST['firstname']);
  if(isset($_REQUEST['lastname']) && $_REQUEST['lastname'] != '' && $user->apellidos == '') $user->setApellidos($_REQUEST['lastname']);
  $user->setField("DOC_url", $media['url']);
  $user->setField("DOC_nombre", $media['title']); 
  if(isset($_REQUEST['dni']) && $_REQUEST['dni'] != '' && $user->fields['DNI'] == '') $user->setField("DNI", $_REQUEST['dni']);
  if(isset($_REQUEST['company']) && $_REQUEST['company'] != '' && $user->fields['Empresa'] == '') $user->setField("Empresa", $_REQUEST['company']); 
  if(isset($_REQUEST['company_cif']) && $_REQUEST['company_cif'] != '' && $user->fields['Empresa_CIF'] == '') $user->setField("Empresa_CIF", $_REQUEST['company_cif']);
  if(isset($_REQUEST['company_state']) && $_REQUEST['company_state'] != '' && $user->fields['Empresa_provincia'] == '') $user->setField("Empresa_provincia", $_REQUEST['company_state']); 
  $user->updateProfileAC(); 

  //Asignamos tags
  if(isset($_REQUEST['tags']) && $_REQUEST['tags'] != "") { //La que venga desde el URL
    foreach (explode(",", $_REQUEST['tags']) as $tag_id) {
      if(is_numeric(chop($tag_id)) && !$user->hasTag(chop($tag_id))) { $user->setTag(chop($tag_id)); }
    }
  }

  //Asignamos listas  
  if(isset($_REQUEST['lists']) && $_REQUEST['lists'] != "") { //La que venga desde el URL
    foreach (explode(",", $_REQUEST['lists']) as $list_id) {
      if(is_numeric(chop($list_id)) && !$user->hasList(chop($list_id))) { $user->setList(chop($list_id)); }
    }
  }

  //Asignamos automatización
  if(isset($_REQUEST['automations']) && $_REQUEST['automations'] != "") { 
    foreach (explode(",", $_REQUEST['automations']) as $automation_id) {
      if(is_numeric(chop($automation_id))) { $user->executeAutomation(chop($automation_id)); }
    }
  }

  //Metemos en Google Sheets-----------------------
  if(get_option("_doc_download_ac_sheet_id") != '') {
    docDownloadAcInsertGoogleSheets($user, $media);
  }
  
  //Devolvemos resultado
  $json = [
    "success" => true,
    "data" => [
      "message" => __("Muchas gracias, en unos minutos recibirá un email para poder descargar el documento.", "doc_download_ac"),
      "user" => $user
    ]
  ];

  echo json_encode($json);
  wp_die();
}

add_action('wp_ajax_doc_download_ac', 'docDownloadAcAjax');
add_action('wp_ajax_nopriv_doc_download_ac', 'docDownloadAcAjax');
