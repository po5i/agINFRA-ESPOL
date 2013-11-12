


var esbbSimpleAppView = Backbone.View.extend({
	query: null,
	//TODO: define the containing elements you want on the page (define the layout)
	template: '\
		<div id="image-header"></div>\
		<div id="{{prefix}}-header">\
			<div id="{{prefix}}-search-url"></div>\
			<div id="{{prefix}}-search-bar"></div>\
			<div id="{{prefix}}-search-filters"></div>\
			<div id="{{prefix}}-date-range"></div>\
		</div>\
		<div id="{{prefix}}-left-col">\
			<ul class="accordion">\
				<li class="files">\
					<a href="#">Context<span>100%</span></a>\
					<ul class="sub-menu" id="{{prefix}}-context-selector"></ul>\
				</li>\
				<li class="mail">\
					<a href="#">Language<span>100%</span></a>\
					<ul class="sub-menu" id="{{prefix}}-language-selector"></ul>\
				</li>\
				<li class="cloud">\
					<a href="#">Dataset<span>100%</span></a>\
					<ul class="sub-menu" id="{{prefix}}-dataset-selector"></ul>\
				</li>\
				<li class="sign">\
					<a href="#">Format<span>100%</span></a>\
					<ul class="sub-menu" id="{{prefix}}-format-selector"></ul>\
				</li>\
			</ul>\
		</div>\
		<div id="{{prefix}}-center-col">\
			<div id="{{prefix}}-timeline" class="esbb-timeline" style="display:none"></div>\
			<div style="display:none" id="{{prefix}}-sort"></div>\
			<div id="{{prefix}}-navigation"></div>\
			<div id="{{prefix}}-search-results"></div>\
		</div>\
		<div id="{{prefix}}-right-col">\
		</div>\
	',

	//TODO: customize how the results will be rendered.
	//  this is a mustache.js template (http://mustache.github.com/)
	templateResults: '\
		<h3>{{header}} [{{number}}/{{total}}]</h3>\
		{{#hits}}\
		<div class="esbb-result"> \
			{{#_source.aginfra_eu.lom_general_title_string_type}}\
				<h4>{{value}}</h4>\
			{{/_source.aginfra_eu.lom_general_title_string_type}}\
			{{#_source.aginfra_eu.lom_general_description_string_type}}\
				<div>{{value}}</div>\
			{{/_source.aginfra_eu.lom_general_description_string_type}}\
			\
			{{#_source.aginfra_eu.lom_technical_location_type}}\
				<div><a href="{{value}}" target="_blank">{{value}}</a> <img src="img/download.png"></div>\
			{{/_source.aginfra_eu.lom_technical_location_type}}\
			<div>Context:</div>\
			<ul class="facets-results">\
				{{#_source.aginfra_eu.lom_educational_context_value_type}}\
					<li>{{value}}</li>\
				{{/_source.aginfra_eu.lom_educational_context_value_type}}\
			</ul>\
			<div>Format:</div>\
			<ul class="facets-results">\
				{{#_source.aginfra_eu.lom_technical_format_type}}\
					<li>{{value}}</li>\
				{{/_source.aginfra_eu.lom_technical_format_type}}\
			</ul>\
			<div>Language:</div>\
			<ul class="facets-results">\
				{{#_source.aginfra_eu.lom_general_language_type}}\
					<li><img src="img/flags/{{value}}.png"></li>\
				{{/_source.aginfra_eu.lom_general_language_type}}\
			</ul>\
			<div>Authors:</div>\
			<ul class="facets-results">\
				{{#_source.aginfra_eu.lom_lifecycle_contribute_entity_type}}\
					<li><a href="#" onclick="return openSNV(\'{{value}}\');" target="_blank">{{value}}</a></li>\
				{{/_source.aginfra_eu.lom_lifecycle_contribute_entity_type}}\
			</ul>\
		</div>\
		{{/hits}}\
		',


	initialize: function() {
		this.query = this.options.query;
		_.bindAll( this, 'render' );
		this.render();
	},
	
	render: function() {
		this.$el.empty();
		this.$el.html( Mustache.render( this.template, { prefix: this.options.id_prefix } ) );

		//TODO: instantiate the desired header elements and connect to the proper element ids
		//  Also don't forget to change your facetName where appropriate
		new esbbSearchURLView( { 
			model: this.query,
			baseURL: 'http://localhost:9200/aginfra/_search',
			el: '#' + this.options.id_prefix + '-search-url',
		} );
		new esbbSearchBarView( { 
			model: this.query,
			el: '#' + this.options.id_prefix + '-search-bar',
			headerName: 'Simple Search:'
		} );
		new esbbSearchFilterSelectView( { 
			model: this.query, 
			el: '#' + this.options.id_prefix + '-search-filters',
			//TODO: fields that will appear in autocomplete (full syntax is "author:gibrown", so this is really just a hit to the user
			avail_fields: [ 'context:other', 'language:es','language:en','language:hu', 'format:', 'dataset:' ]
		} );
		/*new esbbSearchDateRangePickerView( { 
			model: this.query,
			el: '#' + this.options.id_prefix + '-date-range',
			headerName: 'Date Range',
			facetName: 'date'
		} );*/

		//TODO: instantiate the desired center column elements and connect to the proper element ids
		/*new esbbSearchFacetTimelineView( { 
			facetName: 'date',
			el: '#' + this.options.id_prefix + '-timeline',
			model: this.model,
			searchQueryModel: this.query
		} );*/
		new esbbSearchResultsView( { 
			model: this.model, 
			template: this.templateResults,
			el: '#' + this.options.id_prefix + '-search-results' ,
			highlightField: 'title' //TODO: set to whatever your highlighted field name is
		} );

		new esbbSortView( {
			model: this.query, 
			el: '#' + this.options.id_prefix + '-sort' ,
			headerName: 'Sort | ',
			sorts: [ {name: 'title', data: 'title'} ]
		} );

		new esbbNavigationView( {
			model: this.query, 
			el: '#' + this.options.id_prefix + '-navigation' ,
			headerName: 'Navigate | ',
		} );

		//TODO: instantiate the desired left column elements and connect to the proper element ids
		/*new esbbSearchFacetPieView( { 
			facetName: 'author',
			el: '#' + this.options.id_prefix + '-author-pie',
			model: this.model,
			searchQueryModel: this.query
		} );
		new esbbSearchFacetSelectView( { 
			facetName: 'tag',
			headerName: 'Tags',
			el: '#' + this.options.id_prefix + '-tag-selector',
			searchQueryModel: this.query,
			model: this.model
		} );*/
		new esbbSearchFacetSelectView( { 
			facetName: 'aginfra_eu.lom_general_language_type.value',
			headerName: 'Language',
			el: '#' + this.options.id_prefix + '-language-selector',
			searchQueryModel: this.query,
			model: this.model,
			template: '\
				{{#items}}\
					<li><a class="esbb-facet-item" href="{{name}}"><em>{{count}}</em>{{name}}<span>{{perc}}%</span></a></li>\
				{{/items}}\
				{{^items}}\
					<li><a href="#"><em>01</em>None<span>0%</span></a></li>\
				{{/items}}\
				',
			templateNoResults: '\
					<li><a href="#"><em>0</em>No Results<span>0%</span></a></li>\
				',
		} );
		new esbbSearchFacetSelectView( { 
			facetName: 'aginfra_eu.lom_educational_context_value_type.value',
			headerName: 'Context',
			el: '#' + this.options.id_prefix + '-context-selector',
			searchQueryModel: this.query,
			model: this.model,
			template: '\
				{{#items}}\
					<li><a class="esbb-facet-item" href="{{name}}"><em>{{count}}</em>{{name}}<span>{{perc}}%</span></a></li>\
				{{/items}}\
				{{^items}}\
					<li><a href="#"><em>01</em>None<span>0%</span></a></li>\
				{{/items}}\
				',
			templateNoResults: '\
					<li><a href="#"><em>0</em>No Results<span>0%</span></a></li>\
				',
		} );
		new esbbSearchFacetSelectView( { 
			facetName: 'aginfra_eu.lom_technical_format_type.value',
			headerName: 'Format',
			el: '#' + this.options.id_prefix + '-format-selector',
			searchQueryModel: this.query,
			model: this.model,
			template: '\
				{{#items}}\
					<li><a class="esbb-facet-item" href="{{name}}"><em>{{count}}</em>{{name}}<span>{{perc}}%</span></a></li>\
				{{/items}}\
				{{^items}}\
					<li><a href="#"><em>01</em>None<span>0%</span></a></li>\
				{{/items}}\
				',
			templateNoResults: '\
					<li><a href="#"><em>0</em>No Results<span>0%</span></a></li>\
				',
		} );
		new esbbSearchFacetSelectView( { 
			facetName: 'aginfra_eu.dataset.value',
			headerName: 'Dataset',
			el: '#' + this.options.id_prefix + '-dataset-selector',
			searchQueryModel: this.query,
			model: this.model,
			template: '\
				{{#items}}\
					<li><a class="esbb-facet-item" href="{{name}}"><em>{{count}}</em>{{name}}<span>{{perc}}%</span></a></li>\
				{{/items}}\
				{{^items}}\
					<li><a href="#"><em>01</em>None<span>0%</span></a></li>\
				{{/items}}\
				',
			templateNoResults: '\
					<li><a href="#"><em>0</em>No Results<span>0%</span></a></li>\
				',
		} );
		
	}

	//TODO: instantiate the desired right column elements and connect to the proper element ids

});
