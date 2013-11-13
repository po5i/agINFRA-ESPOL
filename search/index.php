<?php include "../config.php" ?>
<!DOCTYPE HTML>
<html>
<head>
<meta name="viewport" content="width=device-width, minimum-scale=1.0, maximum-scale=1.0" />
<!-- TODO: i'm not a designer, you may want to update the style sheet, 
        very basic, please contribute improvements! :) -->
<link rel="stylesheet" type="text/css" href="css/simple.css?m=2012-06-18" />
<link rel="stylesheet" href="js/jquery-ui-1.8.21.custom.css" />
<link rel="stylesheet" href="js/select2.css" />
<link rel="stylesheet" href="css/accordionmenu.css" type="text/css" media="screen" />

<!-- Load JS libraries -->
<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js"></script>
<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.8.18/jquery-ui.min.js"></script>

<script type="text/javascript" src="js/mustache.js"></script>
<script type="text/javascript" src="js/underscore-min.js"></script>
<script type="text/javascript" src="js/backbone-min.js"></script>
<script type="text/javascript" src="js/spin.min.js"></script>
<script type="text/javascript" src="js/spin.js"></script>
<script type="text/javascript" src="js/jquery.spin.js"></script>
<script type="text/javascript" src="js/jquery.flot.min.js"></script>
<script type="text/javascript" src="js/jquery.flot.pie.min.js"></script>
<script type="text/javascript" src="js/jquery.flot.selection.min.js"></script>
<script type="text/javascript" src="js/jquery.deparam.js"></script>

<script type="text/javascript" src="js/select2.js"></script>

<script type="text/javascript" src="es-backbone.js?m=2012-06-18"></script>
<script type="text/javascript" src="simple-view.js?m=2012-06-18"></script>

<script>

(function($) {
$(function(){

var esbbSimpleSearchResults = new esbbSearchResultsModel( );

//TODO: the QueryModel defines the query that will be passed to your server.
// At a minimum you should change the field names, and ensure that you define all of the facets
// that your display will depend on.
var esbbSimpleSearchQuery = new esbbSearchQueryModel( {
	size : 10,
	from : 0,
	query : {
		filtered : {
			query : { 
				query_string: {
					fields: [ "aginfra_eu.lom_general_title_string_type.value","aginfra_eu.lom_lifecycle_contribute_entity_type","aginfra_eu.lom_general_description_string_type" ],	//"content", "title", "tag"
					query: "",
					default_operator: "OR"
			} },
			filter : {
				match_all: { }
			}
	} },
	facets : {
		"aginfra_eu.lom_general_language_type.value" : {
			terms : {
				field : "aginfra_eu.lom_general_language_type.value",
				size : 20
			},
			facet_filter : {
				match_all: { }
			}
		},
		"aginfra_eu.lom_educational_context_value_type.value" : {
			terms : {
				field : "aginfra_eu.lom_educational_context_value_type.value",
				size : 20
			},
			facet_filter : {
				match_all: { }
			}
		},
		"aginfra_eu.lom_technical_format_type.value" : {
			terms : {
				field    : "aginfra_eu.lom_technical_format_type.value",
				size : 20
			},
			facet_filter : {
				match_all: { }
			}
		},
		"aginfra_eu.dataset.value" : {
			terms : {
				field    : "aginfra_eu.dataset.value",
				size : 20
			},
			facet_filter : {
				match_all: { }
			}
		}
	}
} );
esbbSimpleSearchQuery.resultsModel = esbbSimpleSearchResults;

//TODO: define the url for your ES endpoint, index name, and doc type name
esbbSimpleSearchQuery.ajax_url = 'http://<?php echo $HOST ?>/search/endpoint.php';
esbbSimpleSearchQuery.index = 'aginfra_ds';
esbbSimpleSearchQuery.index_type = 'aginfra_ds';

	var esbbSimpleApp = new esbbSimpleAppView( { 
		model: esbbSimpleSearchResults, 
		query: esbbSimpleSearchQuery, 
		el: '#esbb-simple-app',
		id_prefix: 'esbb-simple'
	} );
	
});
})(jQuery);

</script>

<style type="text/css">#wrapper-menu{width:330px;margin:0 auto;}</style>


<link rel="stylesheet" href="windowfiles/dhtmlwindow.css" type="text/css" />
<script type="text/javascript" src="windowfiles/dhtmlwindow.js"></script>
<script>
	function openSNV(center){
		console.log(center);
		var googlewin=dhtmlwindow.open("googlebox", "iframe", "http://<?php echo $HOST ?>/snv/snvd3.php?center="+center+"&entity=person", "Social Network of "+center, "width=610px,height=370px,resize=1,scrolling=1,center=1,frameborder=0", "recal")
		/*googlewin.onclose=function(){ //Run custom code when window is being closed (return false to cancel action):
			return window.confirm("Close window 1?")
		}*/
		return false;
	}
</script>	
</head>




<body>
<div class="s5_body_background">
	<div id='esbb-simple-app'></div>	

	<!-- aginfra -->
	<div style="clear:both;"></div>
	<div style="text-align:center;padding:10px;margin:0 auto;">
		<img src="img/EUflag_logo.jpg">
		<img src="img/e-infrastructure_logo.jpg">
		<img src="img/FP7_capacities_logo.jpg">
	</div>
	<div class="ag_copyright">Copyright &copy; 2011 - <?php echo date("Y") ?>. <a href="http://aginfra.eu">agINFRA.eu</a></div>
</div>


<script type="text/javascript">
	$(document).ready(function() {
		// Store variables
		var accordion_head = $('.accordion > li > a'),
			accordion_body = $('.accordion li > .sub-menu');

		// Open the first tab on load
		accordion_head.first().addClass('active').next().slideDown('normal');

		// Click function
		accordion_head.on('click', function(event) {
			
			// Disable header links
			event.preventDefault();

			// Show and hide the tabs on click
			if ($(this).attr('class') != 'active'){
				accordion_body.slideUp('normal');
				$(this).next().stop(true,true).slideToggle('normal');
				accordion_head.removeClass('active');
				$(this).addClass('active');
			}
		});

		//default search
		if($(".esbb-search-query").val() == ""){
			$(".esbb-search-query").val("*")
			$(".esbb-search-button").click();
			$(".esbb-search-query").val("")
		}
	});
</script>

</body>
</html>
