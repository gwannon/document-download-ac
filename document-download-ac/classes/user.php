<?php

/*
 * TODO:
 * - Add/remove user's lists
 *
 */

/* ----------------- CLASS User -------------------- */
class UserAC {
  public $id;
  public $nombre;
  public $apellidos;
  public $email;
  public $telefono;
  public $fields;
  public $tags;
  public $lists;

  public function __construct($id) {
    global $fields, $tags;
    if (is_numeric($id)) {
      $response = curlCallGet("/contacts/".$id)->contact;
    } else if (filter_var($id, FILTER_VALIDATE_EMAIL)) {
      $response = curlCallGet("/contacts?email=".$id)->contacts[0];
    }

    if($response) {
      $this->id = $response->id;
      $this->nombre = $response->firstName;
      $this->apellidos = $response->lastName;
      $this->email = $response->email;
      $this->telefono = $response->phone;
      $this->fields = $this->getApiFields(); //Campos personalizados
      $this->tags = $this->getApiTags(); //Etiquetas
      $this->lists = $this->getApiLists(); //Listas
    } else if (filter_var($id, FILTER_VALIDATE_EMAIL)) { //Si no existe y tenemos el email lo creamos
      $data['contact'] = [
        'email' => $id, 
      ];
      $response = curlCallPost("/contacts", json_encode($data))->contact;
      $this->id = $response->id;
      $this->email = $response->email;
    }
  }
	
  //SETs --------------------------------	
  function setNombre($val) { $this->nombre = $val; }
  function setApellidos($val) { $this->apellidos = $val; }
  function setEmail($val) { $this->email = $val; }
  function setTelefono($val) { $this->telefono = $val; }
  function setField($field_label, $value) { 
    $this->fields[$field_label] = $value;
  } 
  function setTag($tag_id) { 
    $data['contactTag'] = [
      "contact" => $this->id,
      "tag"     => $tag_id
    ];
    $response = curlCallPost("/contactTags", json_encode($data)); 
    $this->tags[$tag_id] = $response->contactTag->id;
  } 

  function setList($list_id, $status = 1) {
    $data['contactList'] = [
      "contact" => $this->id,
      "list"     => $list_id,
      "status" => $status  // Status: Set to "1" to subscribe the contact to the list. Set to "2" to unsubscribe the contact from the list.  
    ];
    $response = curlCallPost("/contactLists", json_encode($data)); 
    $this->lists[$list_id] = $response->contactList->status;
  } 

  //EXECUTE
  function executeAutomation ($automation_id) {
    $data['contactAutomation'] = [
      "contact" => $this->id,
      "automation" => $automation_id
    ];
    $response = curlCallPost("/contactAutomations", json_encode($data)); 
    return ($response->contactAutomation->status == 1 ? true : false );
  }

  //HAS --------------------------------
  function hasTag($tag_id) {
    if(isset($this->tags[$tag_id]) && $this->tags[$tag_id] > 0) return true;
    else return false;
  }

  function hasList($list_id) {
    if(isset($this->lists[$list_id]) && $this->lists[$list_id] == 1) return true;
    else return false;
  }


  //DELETE --------------------------------
  function deleteTag($tag_id) { 
    $response = curlCallDelete("/contactTags/".$this->tags[$tag_id]);
    $this->tags[$tag_id] = "";
  } 

	//UPDATEs --------------------------------
	function updateProfileAC() {
    global $fields;
    foreach ($fields as $field_label => $field_id) {
      $myfields[] = [
        "field" => $field_id,
        "value" => $this->fields[$field_label]
      ];
    }
    $data['contact'] = [
      'email'       => $this->email, 
			'firstName'   => $this->nombre,
    	'lastName'    => $this->apellidos,
      'fieldValues' => $myfields
		];
    $response = curlCallPut("/contacts/".$this->id, json_encode($data));
		return $response;
	}

  //APIs calls --------------------------------
  function getApiTags() {
    global $tags;
    $usertags = curlCallGet("/contacts/".$this->id."/contactTags")->contactTags;
    foreach ($tags as $tag_id) {
      $currenttags[$tag_id] = false;
      foreach ($usertags as $usertag) {
        if ($tag_id == $usertag->tag) {
          $currenttags[$tag_id] = $usertag->id;
          break;
        }
      }
    }
    return $currenttags;
  }

  function getApiFields() {
    global $fields;
    $userfields = curlCallGet("/contacts/".$this->id."/fieldValues")->fieldValues;
    foreach ($fields as $field_label => $field_id) {
      $currentfields[$field_label] = false;
      foreach ($userfields as $userfield) {
        if ($field_id == $userfield->field) {
          $currentfields[$field_label] = $userfield->value;
          break;
        }
      }
    }
    return $currentfields;
  }

  function getApiLists() {
    global $lists;
    $userlists = curlCallGet("/contacts/".$this->id."/contactLists")->contactLists;
    foreach ($lists as $list_id) {
      $currentlists[$list_id] = false;
      foreach ($userlists as $userlist) {
       if ($list_id == $userlist->list) {
          $currentlists[$list_id] = $userlist->status;
          break;
        }
      }
    }
    return $currentlists;
  }
}
