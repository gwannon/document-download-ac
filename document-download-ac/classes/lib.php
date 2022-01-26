<?php

/* -------------------------------------- */
function docDownloadAcGetMediaById($doc_id) {
	$media = json_decode(file_get_contents(MEDIA_API_DOMAIN."/wp-json/wp/v2/media/".$doc_id));
	if(!isset($media->id) || $media->id != $doc_id) return false;
  $media = [
  	'id' => $media->id,
  	'url' => $media->source_url,
  	'title' => $media->title->rendered
	];
	return $media;
}

function docDownloadAcGenerateHash($media_id, $tags, $automations, $lists, $topic, $sector, $country) {
  $string  = $media_id.",".$tags.",".$automations.",".$lists.",".$topic.",".$sector.",".$country;
  //return $string;
  return hash ("md5", $string);
}


function docDownloadAcGetTags () {
  $tags = array();
  foreach (explode(",", get_option("_doc_download_ac_api_tags_prefix")) as $search) {
    $actags = curlCallGet("/tags?search=".$search);
    foreach ($actags->tags as $tag) {
      if(strtolower(substr($tag->tag, 0, strlen($search))) == $search) $tags[] = $tag->id;
    }
  }
  return $tags;
}



function docDownloadAcInsertGoogleSheets($user, $media) {
  require __DIR__ . '/../vendor/autoload.php';
  putenv('GOOGLE_APPLICATION_CREDENTIALS=' . __DIR__ . '/../service_key.json');
  $client = new Google_Client();
  $client->useApplicationDefaultCredentials();
  $client->addScope('https://www.googleapis.com/auth/spreadsheets');
  $service = new Google_Service_Sheets($client);
  $values = [
    [
      current_time('mysql'), 
      $user->nombre, 
      $user->apellidos, 
      base64_encode($user->email), 
      ($user->fields['DNI'] != '' ? $user->fields['DNI'] : ''), 
      ($user->fields['Empresa'] != '' ? $user->fields['Empresa'] : ''), 
      ($user->fields['Empresa_CIF'] != '' ? $user->fields['Empresa_CIF'] : ''), 
      ($_REQUEST['company_city'] != '' ? $_REQUEST['company_city'] : ''),
      ($user->fields['Empresa_provincia'] != '' ? $user->fields['Empresa_provincia'] : ''), 
      ($_REQUEST['company_country'] != '' ? $_REQUEST['company_country'] : ''),
      $user->id,
      $media['id'],
      $media['url'],
      $_REQUEST['topic'],
      $_REQUEST['sector'],
      $_REQUEST['country'],
      $_SERVER['HTTP_REFERER']
    ]
  ];

  $body = new Google_Service_Sheets_ValueRange(['values' => $values]);
  $params = ['valueInputOption' => "RAW"];
  $result = $service->spreadsheets_values->append(get_option("_doc_download_ac_sheet_id"), get_option("_doc_download_ac_sheet_page").'!A:Q', $body, $params);
  return $result;
}