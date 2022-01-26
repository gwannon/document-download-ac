<?php

//Funciones CURL-----------------------------
function curlCall($link, $request = 'GET', $payload = false) {
  $curl = curl_init();
  curl_setopt($curl, CURLOPT_URL, AC_API_DOMAIN.$link);
  curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($curl, CURLOPT_HTTPHEADER, array('Api-Token: '.AC_API_TOKEN));
  if (in_array($request, array("PUT", "POST", "DELETE"))) curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $request);
  if ($payload) curl_setopt($curl, CURLOPT_POSTFIELDS, $payload);
  $response = curl_exec($curl);
  $json = json_decode($response);
  $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
  curl_close($curl);
  if (in_array($httpcode, array(200, 201))) {
    curlLog("log", $link, $request, $payload );
    return $json;
  } else {
    curlLog("errors", $link, $request, $payload );
    return false;
  }
}

//GET
function curlCallGet($link) { return curlCall($link); }

//PUT
function curlCallPut($link, $payload) { return curlCall($link, "PUT", $payload); }

//POST
function curlCallPost($link, $payload) { return curlCall($link, "POST", $payload); }

//DELETE
function curlCallDelete($link) { return curlCall($link, "DELETE"); }

//Log system
function curlLog($file, $link, $request, $payload ) {
  $f = fopen(dirname(__FILE__)."/../logs/".$file.".txt", "a+");
  $line = date("Y-m-d H_i:s")." | ".$link." | ".$request." | ".$payload."\n";
  fwrite($f, $line);
  fclose($f);
}