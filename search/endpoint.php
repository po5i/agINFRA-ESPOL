<?php include "../config.php" ?>
<?php
//TODO: Need includes for Elastica (php library: https://github.com/ruflin/Elastica/)
include "/var/www/aginfra/Elastica/Exception/ExceptionInterface.php";
include "/var/www/aginfra/Elastica/Exception/ClientException.php";
include "/var/www/aginfra/Elastica/Client.php";
include "/var/www/aginfra/Elastica/Exception/ResponseException.php";
include "/var/www/aginfra/Elastica/Exception/InvalidException.php";
include "/var/www/aginfra/Elastica/Exception/ConnectionException.php";
include "/var/www/aginfra/Elastica/Exception/Connection/HttpException.php";
include "/var/www/aginfra/Elastica/Util.php";
include "/var/www/aginfra/Elastica/Response.php";
include "/var/www/aginfra/Elastica/Result.php";
include "/var/www/aginfra/Elastica/ResultSet.php";
include "/var/www/aginfra/Elastica/Param.php";
include "/var/www/aginfra/Elastica/Query.php";
include "/var/www/aginfra/Elastica/Connection.php";
include "/var/www/aginfra/Elastica/SearchableInterface.php";
include "/var/www/aginfra/Elastica/Type.php";
include "/var/www/aginfra/Elastica/Request.php";
include "/var/www/aginfra/Elastica/Search.php";
include "/var/www/aginfra/Elastica/Index.php";
include "/var/www/aginfra/Elastica/Transport/AbstractTransport.php";
include "/var/www/aginfra/Elastica/Transport/Http.php";

//TODO: define array of ES servers
global $es_servers;
//$es_servers = array(
//					    'servers' => array(
					        //array('host' => '200.126.23.173', 'port' => 9200)
//					        array('host' => $SERVER_IP, 'port' => $ES_PORT)
//					    )
//					);

$es_servers = array('host' => $SERVER_IP, 'port' => $ES_PORT);

//OPTIONAL: only allow a whitelist of particular indices and particular fields within those indices
//  also defines highlighting and which fields to return.
/*$whitelist_idx = array( 
	'index/type' => array( 
							'highlight' => array( 'title' => array( 'fragment_size' => '50', 'number_of_fragments' => 3 ) ), 
							'fields' => array( 'title', 'language', 'context', 'format' ) 
						),
);*/

if ( empty( $_REQUEST['query'] ) || empty( $_REQUEST['idx'] ) || empty( $_REQUEST['type'] ) ) {
	header("HTTP/1.0 400 Bad Request"); //improper request
	die;
}

$idx = $_REQUEST['idx'];
$type = $_REQUEST['type'];
$query = str_replace( '\\', '', $_REQUEST['query'] );

$idx_type = $idx;
if ( '' !== $type ) 
	$idx_type .= '/' . $type;

//OPTIONAL: uncomment to enable whitelisting
//if ( ! in_array( $idx_type, array_keys( $whitelist_idx ) ) ) {
//	header("HTTP/1.0 403 Forbidden"); //forbidden
//	die;
//}

try{
	//$esclient = new \Elastica\Client( array( 'servers' => $es_servers ) );
        $esclient = new \Elastica\Client( $es_servers  );
	$esQ = new \Elastica\Query();
	$esQ->setRawQuery( get_object_vars( json_decode( $query ) ) );
	if ( isset( $whitelist_idx[ $idx_type ] ) ) {
		$esQ->setHighlight( array( 'fields' => $whitelist_idx[ $idx_type ][ 'highlight' ], 
			'pre_tags' => array( '<b>' ),
			'post_tags' => array( '</b>' ) ) );
		$esQ->setFields( $whitelist_idx[ $idx_type ][ 'fields' ] );
	}
	if ( '' != $type )
		$estype = $esclient->getIndex( $idx )->getType( $type );
	else
		$estype = $esclient->getIndex( $idx );

	$results = $estype->search( $esQ );
	echo json_encode( $results->getResponse()->getData() );
}
catch ( Exception $e ){
	error_log( $e->getMessage() );
	header("HTTP/1.0 500 Server Error"); //server error
	echo json_encode( array( 'error' => 'query_error: ' . $e->getMessage() ) );
}
