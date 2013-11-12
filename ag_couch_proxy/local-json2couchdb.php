<?php include "../config.php" ?>
<?php
echo date("Y-m-d H:i:s")."\r\n";
echo "JSON files to CouchDB...\r\n";
$debug = false;     $limit = 10000;
/**
Read a folder and import content
*/
//$url = 'http://localhost:5984/'.$COUCHDB_AGINFRA;         //upload to localhost
$url = 'http://212.189.144.208:5984/'.$COUCHDB_AGINFRA;     //upload to INFN

//poner en $data el contenido del JSON
$dspath = isset($_REQUEST["dspath"]) ? $_REQUEST["dspath"] : "/home/carlos/workspace/WebAPI/ds/";
$path = $dspath."json/";

$json_files = scandir($path);

foreach($json_files as $idx => $file)
{
    if($file == "." or $file == "..")
        continue;
    if($limit > 1 and $idx >= $limit)
        break;

    $data = file_get_contents($path.$file);
    

    // use key 'http' even if you send the request to https://...
    $options = array(
        'http' => array(
            'header'  => "Content-type: application/json",
            'method'  => 'POST',          
            'content' => $data,
        ),
    );
    $context  = stream_context_create($options);
    $result = file_get_contents($url, false, $context);

    if($debug)
    {
        echo "couchdb response:\r\n";
        var_dump($result);
    }
}

/**
curl -XDELETE 'http://localhost:9200/aginfra_datasets/'
curl -XPUT 'http://localhost:9200/aginfra_ds/'
curl -XPUT 'http://localhost:9200/_river/aginfra_ds/_meta' -d '{
    "type" : "couchdb",
    "couchdb" : {
        "host" : "localhost",
        "port" : 5984,
        "db" : "aginfra_datasets",
        "filter" : null
    },
    "index" : {
        "index" : "aginfra_ds",
        "type" : "aginfra_ds",
        "bulk_size" : "100",
        "bulk_timeout" : "10ms"
    }
}'
*/


echo "Finished\r\n";
echo date("Y-m-d H:i:s")."\r\n";
?>
