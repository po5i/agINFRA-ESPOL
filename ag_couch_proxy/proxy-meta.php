<?php include "../config.php" ?>
<?php
header('Content-type: application/json');
/**
http://localhost:5984/aginfra/_design/relationships/_view/relationships
*/

$center = isset($_REQUEST["center"]) ? $_REQUEST["center"] : "";
$search = isset($_REQUEST["search"]) ? $_REQUEST["search"] : 0;


require_once 'lib/couch.php';
require_once 'lib/couchClient.php';
require_once 'lib/couchDocument.php';

function multisearch($array,$value) 
{ 
  foreach ($array as $subarray) 
    if($subarray["key"] == $value)
      return true;        
  
  return false;
} 

// set a new connector to the CouchDB server
$client = new couchClient ('http://localhost:5984',$COUCHDB_AGINFRA);

// view fetching, using the view option limit
try {
  if(!empty($center))
    if(!empty($search) and $search == 1){
      //all      
      $view = $client->startkey($center)->endkey($center."Z")->asArray()->stale('ok')->getView('authors','authors');
    }
    else
      //key
      $view = $client->key($center)->asArray()->stale('ok')->getView('authors','authors');
    
  else
    $view = $client->asArray()->stale('ok')->getView('authors','authors');
   
  $view_filtered = array();
  $view_filtered["rows"] = array();
  $total_rows = 0;

  /*if(!empty($search) and $search == 1){
    //TODO: buscar por search //($search == 1 and (strpos($person1,$center) !== false || strpos($person2,$center) !== false))
    foreach($view["rows"] as $idkeyvalue){
      $person = $idkeyvalue["key"];   
      if(stripos($person,$center) !== false){   #TODO: similar
          //if(!in_array($idkeyvalue,$view_filtered["rows"])){
          if(!multisearch($view_filtered["rows"],$person)){
            $view_filtered["rows"][] = $idkeyvalue;
            $total_rows++;
          }
          
      }
    }
    $view_filtered["total_rows"] = $total_rows;
    echo json_encode($view_filtered);
  }
  else*/
    echo json_encode($view);
} catch (Exception $e) {
   echo "something weird happened: ".$e->getMessage()."<BR>\n";
}

?>