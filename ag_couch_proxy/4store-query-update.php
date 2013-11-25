<?php include "../config.php" ?>
<?php
echo date("H:i:s");
echo " 4Store query to CouchDB...\r\n";

$debug = true;
$facets = array("lom.educational.context.value","lom.technical.format","lom.general.language","lom.technical.location","lom.general.title.string");



foreach($facets as $f):

    /**
    Get the data from the 4store
    */
    $url = 'http://4store.ipb.ac.rs:81/sparql/';
    echo $query = 'SELECT DISTINCT ?s ?p ?o WHERE { ?s ?p ?o FILTER (REGEX(STR(?p), "'.$f.'", "i")) } ';  //LIMIT 10
    echo "\n";
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
        $documents[$res->s->value]["value"][] = $res->o->value;

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
     local CouchDB
    */


    require_once 'lib/couch.php';
    require_once 'lib/couchClient.php';
    require_once 'lib/couchDocument.php';


    // set a new connector to the CouchDB server
    $client = new couchClient ('http://localhost:5984',$COUCHDB_AGINFRA);


    foreach($documents as $key => $value)
    {
        $val = $value["value"][0];

        $result = $client->key($key)->getView('relationships','ids');
        //var_dump($result->rows);

        //iterate the search results
        if(!empty($result->rows))
        {
            foreach($result->rows as $couch_doc)
            {
                if($debug)
                    var_dump($couch_doc->id);

                $stored_doc = $client->getDoc($couch_doc->id);
                switch($f)
                {
                    case "lom.general.language":
                    $stored_doc->language = $val;
                    break;

                    case "lom.educational.context.value":
                    $stored_doc->context = $val;
                    break;

                    case "lom.general.title.string":
                    $stored_doc->title = $val;
                    break;

                    case "lom.technical.format":
                    $stored_doc->format = $val;
                    break;

                    case "lom.technical.location":
                    $stored_doc->location = $val;
                    break;
                }

                //update the dataset
                //if(!isset($stored_doc->dataset)){
                    $strds = strpos($key,"ds/");
                    $dataset = substr($key,$strds+3,strpos($key,"/",$strds+3)-$strds-3);
                    $stored_doc->dataset = $dataset;
                //}
                
                $client->storeDoc($stored_doc);

                //update
                if($debug)
                    echo "Updated Document in CouchDB with id = ".$couch_doc->id."\r\n";
                
            }
        }
        else
        {
            echo "Key not found $key \r\n";
        }
            
        
    }

endforeach;






echo date("H:i:s");
echo " Finished\r\n";
?>