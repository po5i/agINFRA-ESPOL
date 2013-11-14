<?php include "../config.php" ?>
<?php
header('Content-type: application/json');
/**
http://localhost:5984/aginfra/_design/relationships/_view/relationships
*/

$center = isset($_REQUEST["center"]) ? $_REQUEST["center"] : "";
$mode = isset($_REQUEST["mode"]) ? $_REQUEST["mode"] : "full";  //simple|full



require_once 'lib/couch.php';
require_once 'lib/couchClient.php';
require_once 'lib/couchDocument.php';

// set a new connector to the CouchDB server
$client = new couchClient ('http://localhost:5984',$COUCHDB_AGINFRA);

// view fetching, using the view option limit
try {
   $view = $client->startkey($center)->endkey($center."Z")->asArray()->stale('ok')->getView('relationships','relationships');
   $view_filtered = array();

   $nodes = array();
   $links = array();
   $idx = 0;

   foreach($view["rows"] as $idkeyvalue){        

        $key_authors = explode("+", $idkeyvalue["key"]);

        if($key_authors[0] == $center)
          $group = 1;
        else
          $group = 2;
        $node = array("name" => $key_authors[0],"group"=>$group);
        if(array_search($node, $nodes) === false){
          $idx_1 = $idx;
          $nodes[$idx_1] = $node;
        }
        else{
          $keys = array_keys($nodes,$node);
          $idx_1 = $keys[0];
        }

        if(isset($key_authors[1])) {
          if($key_authors[1] == $center)
            $group = 1;
          else
            $group = 2;
          $node = array("name" => $key_authors[1],"group"=>$group);
          if(array_search($node, $nodes) === false){
            $idx_2 = ++$idx;
            $nodes[$idx_2] = $node;
          }
          else{
            $keys = array_keys($nodes,$node);
            $idx_2 = $keys[0];
          }
        }
          

        if($mode == "full"){
          if($idkeyvalue["value"] == $center)
            $group = 1;
          else
            $group = 3;
          $node = array("name" => $idkeyvalue["value"],"group"=>$group);
          if(array_search($node, $nodes) === false){
                    $idx_3 = ++$idx;
                    $nodes[$idx_3] = $node;
          }
          else{
            $keys = array_keys($nodes,$node);
            $idx_3 = $keys[0];
          }
        }
          

        if(!function_exists("single_search_1_3")){
          function single_search_1_3($member) {
            global $idx_1,$idx_2,$idx_3;
            if(($member["source"]==$idx_1 and $member["target"]==$idx_3) or ($member["source"]==$idx_3 and $member["target"]==$idx_1))
              return true;
          }
        }
        
        if(!function_exists("single_search_2_3")){
          function single_search_2_3($member) {
            global $idx_1,$idx_2,$idx_3;
            if(($member["source"]==$idx_2 and $member["target"]==$idx_3) or ($member["source"]==$idx_3 and $member["target"]==$idx_2))
              return true;
          }
        }

        if(!function_exists("single_search_1_2")){
          function single_search_1_2($member) {
            global $idx_1,$idx_2;
            if(($member["source"]==$idx_1 and $member["target"]==$idx_2) or ($member["source"]==$idx_2 and $member["target"]==$idx_1))
              return true;
          }
        }

        if($mode == "full"){
          $filtered = array_filter($links, 'single_search_1_3');
          $matched_keys = array_keys($filtered);

          if(!empty($matched_keys)){
            foreach ($matched_keys as $the_key) {
              $links[$the_key]["value"]++;
            }
          }
          else{
            $links[] = array("source"=>$idx_1, "target"=>$idx_3, "value"=>1);
          }

          $filtered = array_filter($links, 'single_search_2_3');
          $matched_keys = array_keys($filtered);

          if(!empty($matched_keys)){
            foreach ($matched_keys as $the_key) {
              $links[$the_key]["value"]++;
            }
          }
          else{
            $links[] = array("source"=>$idx_2, "target"=>$idx_3, "value"=>1);
          }
        }
        elseif($mode == "simple"){
          $filtered = array_filter($links, 'single_search_1_2');
          $matched_keys = array_keys($filtered);

          if(!empty($matched_keys)){
            foreach ($matched_keys as $the_key) {
              $links[$the_key]["value"]++;
            }
          }
          else{
            $links[] = array("source"=>$idx_1, "target"=>$idx_2, "value"=>1);
          }
        } 
   }
   
   //echo json_encode($view);
   echo json_encode(array("nodes"=>$nodes,"links"=>$links));
} catch (Exception $e) {
   echo "something weird happened: ".$e->getMessage()."<BR>\n";
}

?>