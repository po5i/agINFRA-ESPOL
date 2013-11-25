<?php include "../config.php" ?>
<?php
echo date("Y-m-d H:i:s")."\r\n";
echo "4Store query to CouchDB...\r\n";
$debug = true;
/**
Get the data from the 4store
*/
$url = 'http://4store.ipb.ac.rs:81/sparql/';
$query = 'SELECT DISTINCT * WHERE { ?s ?p ?o FILTER (REGEX(STR(?p), "lom.lifecycle.contribute.entity.type", "i")) } ';  //LIMIT 10
$data = array('query' => $query, 'soft-limit' => '1000000');

// use key 'http' even if you send the request to https://...
$options = array(
    'http' => array(
        'header'  => "Content-type: application/x-www-form-urlencoded\r\nAccept: application/sparql-results+json\r\n",
        'method'  => 'POST',
        'content' => http_build_query($data),
    ),
);
$context  = stream_context_create($options);
$result = file_get_contents($url, false, $context);

if($debug)
{
    echo "4store response:\r\n";
    var_dump($result);
}
    

$result_php = json_decode($result);
$documents = array();

foreach($result_php->results->bindings as $res)
{
    $documents[$res->s->value]["authors"][] = $res->o->value;

    if($debug)
    {
        echo "Triples:\r\n";
        echo "s: ".$res->s->value;
        echo "\r\n";
        echo "p: ".$res->p->value;
        echo "\r\n";
        echo "o: ".$res->o->value;
        echo "\r\n";
        echo "\r\n";
    }
}

if($debug)
{
    echo "Documents:\r\n";
    var_dump($documents);
}

/**
Insert documents to our local CouchDB
*/


require_once 'lib/couch.php';
require_once 'lib/couchClient.php';
require_once 'lib/couchDocument.php';


// set a new connector to the CouchDB server
$client = new couchClient ('http://localhost:5984',$COUCHDB_AGINFRA);

foreach($documents as $key => $value)
{
    //value is an array with authors    
    /*echo $key;
    var_dump($value);    
    echo "\r\n";*/    

    $authors = array();
    foreach($value["authors"] as $author)
    {
        $authors[] = array("name"=>$author);
    }

    $result = $client->key($key)->getView('relationships','ids');
    //echo "\n\n=== key $key === \n";
    //var_dump($result->rows);

    if(empty($result->rows))
    {
        //save the document in the database
        $doc = new couchDocument($client);
        $doc->paper = $key;
        $doc->authors = $authors;
        if($debug)
            echo "Saved Document in CouchDB \r\n";
    }
    else
    {
        //update document
        foreach($result->rows as $couch_doc)
        {
            $doc = $client->getDoc($couch_doc->id);
            $doc->paper = $key;
            $doc->authors = $authors;
            $client->storeDoc($doc);
            if($debug)
                echo "Updated Document in CouchDB with id=".$couch_doc->id."\r\n";
        }
    }
}

echo "Finished\r\n";
echo date("Y-m-d H:i:s")."\r\n";
?>
