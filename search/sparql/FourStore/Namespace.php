<?php

class FourStore_Namespace {

    protected static $_namespaces = array(
      'dc' => 'http://purl.org/dc/elements/1.1/',
      'foaf' => 'http://xmlns.com/foaf/0.1/',
      'rdfs' => 'http://www.w3.org/2000/01/rdf-schema#',
      'xsd' => 'http://www.w3.org/2001/XMLSchema#',
      'db' => 'http://93.63.35.32:8080/d2rq/resource/',
      'dwc' => 'http://rs.tdwg.org/dwc/terms/',
      'germplasmType' => 'http://purl.org/germplasm/germplasmType#',
      'meta' => 'http://www4.wiwiss.fu-berlin.de/bizer/d2r-server/metadata#',
      'germplasmTerm' => 'http://purl.org/germplasm/germplasmTerm#',
      'd2r' => 'http://sites.wiwiss.fu-berlin.de/suhl/bizer/d2r-server/config.rdf#',
      'map' => 'http://93.63.35.32:8080/d2rq/resource/#',
      'owl' => 'http://www.w3.org/2002/07/owl#',
      'rdf' => 'http://www.w3.org/1999/02/22-rdf-syntax-ns#',
      'vocab' => 'http://93.63.35.32:8080/d2rq/resource/vocab/'
    );

    public static function add($short, $long)
    {
        self::$_namespaces[$short] = $long;
    }

    public static function get($short)
    {
        return self::$_namespaces[$short];
    }

    public static function to_sparql() {
        $sparql = "";
        foreach(self::$_namespaces as $short => $long) {
            $sparql .= "PREFIX $short: <$long>\n";
        }
        return $sparql;
    }

    public static function to_turtle() {
        $turtle = "";
        foreach(self::$_namespaces as $short => $long) {
            $turtle .= "@prefix $short: <$long> .\n";
        }
        return $turtle;
    }


}

?>
