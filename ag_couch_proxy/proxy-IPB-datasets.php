<?php
/**
http://localhost:5984/aginfra/_design/relationships/_view/relationships

Requirements:

php curl extension
php sqlite extension
maximum execution time increased (or unlimited)
*/

require_once 'lib/couch.php';
require_once 'lib/couchClient.php';
require_once 'lib/couchDocument.php';

$debug = false;

/*class MyDB extends SQLite3
 {
    function __construct()
    {
       $this->open('ipb.ds.sqlite');
    }
 }
 $db = new MyDB();
 if(!$db){
    echo $db->lastErrorMsg();
 }*/


// set a new connector to the CouchDB server
$client = new couchClient ('http://agro.ipb.ac.rs','agcouchdb');

// view fetching, using the view option limit
try {
   $view = $client->asArray()->getView('datasets','list');  //get all datasets
   $view_filtered = array();
   
   $count = 1;

   foreach($view["rows"] as $key => $value){    
        $type = $value["value"]["dataset"]["type"];
        
        if($debug)
          echo $count."\n"; $count++;
        
        if($type == "oai_lom"){
          $location = $value["value"]["dataset"]["http_dataset_location"];
          if(isset($value["value"]["dataset"]["harvesting_target"]))
            $name = $value["value"]["dataset"]["harvesting_target"]["name"];
          elseif(isset($value["value"]["dataset"]["dataset"]["harvesting_target"]))
            $name = $value["value"]["dataset"]["dataset"]["harvesting_target"]["name"];
          else{
            if($debug)
              echo "HARVESTING TARGET NOT DETECTED at key ".$value["key"].".. \n";
            continue;
          }
          
          //$view_filtered[] = $location;

          /*$sql = "SELECT * FROM datasets WHERE dataset_id = '{$value["id"]}'";
          $ret = $db->query($sql);
          $insert = true;
          if($row = $ret->fetchArray(SQLITE3_ASSOC)){
            $insert = false;
            if($row["dataset_id"] == $value["id"])
              continue;
          } 

          if($insert){
            $sql = "INSERT INTO datasets(filename,name,dataset_id) VALUES('$location','$name','{$value["id"]}')";
            @$ret = $db->exec($sql);
          }*/




          //MAIN OUTPUT: CSV mapping
          echo basename($location).",".$name."\n";






          $dspath = isset($_REQUEST["dspath"]) ? $_REQUEST["dspath"] : "/home/carlos/workspace/WebAPI/ds/";
          $path = $dspath.basename($location);
          
          //avoid re-download
          if(file_exists($path)){
            if($debug)
              echo "... Exists."."\n";
            continue;
          }

          //download
          if(!file_exists($path)) {
            $fp = fopen($path, 'w');
            if($fp){
              $ch = curl_init($location);
              curl_setopt($ch, CURLOPT_FILE, $fp);
              $data = curl_exec($ch);
              curl_close($ch);
              fclose($fp);
            }
          }
        }
   }

   //print_r($view_filtered);
} catch (Exception $e) {
   echo "something weird happened: ".$e->getMessage()."<BR>\n";
}
//$db->close();

?>
