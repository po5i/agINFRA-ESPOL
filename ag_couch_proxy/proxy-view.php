<?php include "../config.php" ?>
<?php
header('Content-type: application/json');
/**
http://localhost:5984/aginfra/_design/relationships/_view/relationships
*/

$center = isset($_REQUEST["center"]) ? $_REQUEST["center"] : "";



require_once 'lib/couch.php';
require_once 'lib/couchClient.php';
require_once 'lib/couchDocument.php';

// set a new connector to the CouchDB server
$client = new couchClient ('http://localhost:5984',$COUCHDB_AGINFRA);

// view fetching, using the view option limit
try {
   $view = $client->startkey($center)->endkey($center."Z")->asArray()->stale('ok')->getView('relationships','relationships');
   /*$view_filtered = array();
   $total_rows = 0;
   foreach($view["rows"] as $idkeyvalue){
        $person1 = $idkeyvalue["key"][0]["name"];
        $person2 = $idkeyvalue["key"][1]["name"];
        $paper = $idkeyvalue["value"];
        if( (($person1 == $center || $person2 == $center)) ) {
            $view_filtered["rows"][] = $idkeyvalue;
            $total_rows++;
        }
   }
   $view_filtered["total_rows"] = $total_rows;*/
   echo json_encode($view);
   //echo json_encode($view_filtered);
} catch (Exception $e) {
   echo "something weird happened: ".$e->getMessage()."<BR>\n";
}

?>