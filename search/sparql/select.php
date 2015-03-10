<?php
require_once 'FourStore/Store.php';
require_once 'FourStore/Namespace.php';
require_once 'Zend/Loader.php';
spl_autoload_register(array('Zend_Loader', 'autoload'));

$s = new FourStore_Store('http://93.63.35.32:8080/d2rq/sparql');

$q = $_REQUEST["q"];

$r = $s->select("SELECT DISTINCT * WHERE {
  ?s a germplasmTerm:GermplasmAccession;
     dwc:scientificName ?scientificName;
     dwc:country ?country;
     vocab:cropName ?cropName;
     dwc:genus ?genus;
     rdfs:label ?label .
   FILTER (REGEX(STR(?scientificName), '$q', 'i') || REGEX(STR(?cropName), '$q', 'i'))
}
LIMIT 30
");

//echo "<pre>";
//print_r($r);
//echo "</pre>";


if(empty($r)){
	echo "There are no germplasm data to display for $q";
}

foreach($r as $row){
	$out = "<div class='germplasm-result'>";
	$out .= "<h3>".$row["cropName"]." (".$row["scientificName"].")</h3>";
	$out .= "<div style='float:left;width:200px;'><a rel='meta' type='application/rdf+xml' href='".$row["s"]."' target='_blank' title='Go to Accession (Resource URI)'><img src='img/rdf.png' alt='rdf' /></a></div>";
	$out .= "<div style='float:left;width:200px;'><b>Country:</b> ".$row["country"]."<br><b>Genus:</b> ".$row["genus"]."</div>";
	$out .= "<div style='float:left;width:300px;'>".$row["label"]."</div>";
	$out .= "<div style='clear:both;'></div>";
	$out .= "</div>";
	echo $out;
}

?>
