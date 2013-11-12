<?php include "../config.php" ?>
<!DOCTYPE html>
<html>
<head>
    <title>agINFRA Social Network Visualization Component</title>
    <script type="text/javascript" src="dist/vivagraph.js"></script>
    <script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js"></script>
    <script type="text/javascript" src="dist/jquery.autocomplete.js"></script>
    <script type="text/javascript" src="http://<?php echo $COUCH ?>/_utils/script/couch.js"></script>

    <script type="text/javascript">
        function main(origin, previous) {
            
            // This demo shows how to create an SVG node which is a bit more complex
            // than single image. Do accomplish this we use 'g' element and 
            // compose group of elements to represent a node.            
            var graph = Viva.Graph.graph();

            //pre-load center
            //center = "Cooperative Extension Service";
            //center = "Geir Lieblein";
            center = "<?php if(isset($_GET["center"])) echo $_GET["center"]; else echo "" ?>";
            //center = "Lorna Luck";
            //center = "Fiona Burnett";

            if(origin != undefined){
              center = origin;                
            }
            
            // ***************** //
            // Generated content //
            $.ajax({
              //url: "http://localhost:5984/aginfra/_design/relationships/_view/relationships?jsonp=mycallback", 
              url: "http://<?php echo $HOST ?>/ag_couch_proxy/proxy-view.php?center="+center, 
              dataType: "json",
              cache: false
            }).done(function( json ) {
              var added_nodes = [];          
              var count = 1;    

              for(row in json.rows) {
                //var person1 = json.rows[row].key[0].name;     //as dictionary
                //var person2 = json.rows[row].key[1].name;
                var person1 = json.rows[row].key.split("+")[0]  //+ separated
                var person2 = json.rows[row].key.split("+")[1]
                var paper = json.rows[row].value; //+count.toString();
                
                //if not center, pick the first one
                if(center == ""){
                  center = person2;
                }

                if(person1 != center && person2 != center){
                  continue;
                }
                  
                
                if(added_nodes.indexOf(person1) == -1) {
                  if(person1 == center)
                    graph.addNode(person1, {'meta':person1,'avatar':'img/center.png','type':'person'});
                  else
                    graph.addNode(person1, {'meta':person1,'avatar':'img/avatar.png','type':'person'});
                  added_nodes.push(person1);
                }


                if(added_nodes.indexOf(person2) == -1) {
                  if(person2 == center)
                    graph.addNode(person2, {'meta':person2,'avatar':'img/center.png','type':'person'});
                  else
                    graph.addNode(person2, {'meta':person2,'avatar':'img/avatar.png','type':'person'});
                  added_nodes.push(person2);
                }          

                if(added_nodes.indexOf(paper) == -1) {
                  graph.addNode(paper, {'meta':paper,'avatar':'img/book.png','type':'article'});
                  added_nodes.push(paper);
                  count++;
                } 

                //graph.addLink(person1, person2);
                graph.addLink(person1, paper);
                graph.addLink(paper, person2);
              }

              if(center){
                $('#meta').html("<img src=\"img/ajax-loader.gif\" />");
                $.ajax({
                  url: "http://<?php echo $HOST ?>/ag_couch_proxy/proxy-meta.php?center="+center, 
                  dataType: "json",
                  cache: false
                }).done(function( json ) {
                  $('#meta').html("<h3>"+center+"</h3>");
                  //$('#meta').append("<small><a href=\"snv.php?center="+center+"\">Center here... <img alt=\"Center here\" src=\"img/chart.png\"></a></small>");

                  if(previous != undefined){
                    $('#meta').append("<small><a href=\"snv.php?center="+previous+"\"><img alt=\"Back\" src=\"img/back.png\"> Back to "+previous+"</a></small>");
                  }
                  
                  $('#meta').append("<ul>");
                  $('#meta').append("<hr>")
                  $('#meta').append("<h3>Articles</h3>");
                  for(row in json.rows) {
                    var article = json.rows[row].value.title;
                    var resource = json.rows[row].value.resource;
                    var language = json.rows[row].value.language;
                    var issued = json.rows[row].value.issued;
                    if(article != undefined && resource != undefined){
                      $('#meta').append("<span>"+article+"</span>");
                      $('#meta').append(" <img src=\"img/flags/"+language+".png\"> ");                      
                      $('#meta').append("<small>"+issued+"</small>");                                        
                      $('#meta').append("<small> <a target=\"_blank\" href=\""+resource+"\">Go to source...</a></small>");                      
                      $('#meta').append("<hr>")
                    }
                  }
                  $('#meta').append("</ul>");
                });  
              }
              //console.log(added_nodes);
            });      

            //Autocomplete
            var options, a;
            jQuery(function(){
               options = { 
                            serviceUrl:'http://<?php echo $HOST ?>/ag_couch_proxy/proxy-meta.php?search=1',
                            onSelect: function (suggestion) {
                                $('#gotosearch').attr("href",$('#gotosearch')[0] + suggestion.value);
                            },
                            minChars:3,
                            paramName: 'center',
                            transformResult: function(response) {                                
                                var json_res = eval("(" + response + ")");
                                return {                                    
                                    suggestions: $.map(json_res.rows, function(dataItem) {
                                        return { value: dataItem.key, data: dataItem.key };
                                    })
                                };
                            }
                          };
               a = $('#query').autocomplete(options);
            });      
            

            /*graph.addNode('Carlos V.', {'meta':'Carlos Villavicencio','gravatar':'3371aed4f73724343cacfba60357a644'});
            graph.addNode('indexzero', {'meta':'Index Zero 1','gravatar':'d43e8ea63b61e7669ded5b9d3c2e980f'});
            graph.addNode('indexzero2', {'meta':'Index Zero 2','gravatar':'d43e8ea63b61e7669ded5b9d3c2e980f'});
            graph.addNode('indexzero3', {'meta':'Index Zero 3','gravatar':'d43e8ea63b61e7669ded5b9d3c2e980f'});
            graph.addNode('indexzero4', {'meta':'Index Zero 4','gravatar':'d43e8ea63b61e7669ded5b9d3c2e980f'});

            graph.addLink('Carlos V.', 'indexzero');
            graph.addLink('Carlos V.', 'indexzero2');
            graph.addLink('Carlos V.', 'indexzero4');
            graph.addLink('indexzero2', 'indexzero3');
            graph.addLink('indexzero4', 'indexzero2');
            graph.addLink('indexzero', 'indexzero3');*/


            var graphics = Viva.Graph.View.svgGraphics(),
                nodeSize = 20;

                highlightRelatedNodes = function(nodeId, isOn) {
                   // just enumerate all realted nodes and update link color:
                   graph.forEachLinkedNode(nodeId, function(node, link){
                       if (link && link.ui) {
                           // link.ui is a special property of each link
                           // points to the link presentation object.
                           link.ui.attr('stroke', isOn ? '#999999' : '#DDDDDD').attr('stroke-width', isOn ? '1' : '1');;
                       }
                   });
                };


            graphics.node(function(node) {
              // This time it's a group of elements: http://www.w3.org/TR/SVG/struct.html#Groups

              //trim the string to the maximum length
              var maxLength = 20;
              var trimmedString = node.id.substr(0, maxLength) + "...";
              //re-trim if we are in the middle of a word
              //trimmedString = trimmedString.substr(0, Math.min(trimmedString.length, trimmedString.lastIndexOf(" ")))

              var ui = Viva.Graph.svg('g'),
                  // Create SVG text element with user id as content
                  //TODO: create a white semi transparent rectangle                 
                  svgText = Viva.Graph.svg('text')
                                                  .attr('font-size', 10)
                                                  .attr('y', '-4px')
                                                  .attr('text-anchor','left')
                                                  .text(trimmedString),
                  img = Viva.Graph.svg('image')
                     .attr('width', nodeSize)
                     .attr('height', nodeSize)
                     //.link('https://secure.gravatar.com/avatar/' + node.data.gravatar+'?s=100');
                     .link(node.data.avatar);
                     //.link('img/avatar.png');
            
              ui.append(svgText);
              ui.append(img);

              

              //highlight
              $(ui).hover(function() { // mouse over
                  highlightRelatedNodes(node.id, true);
                  $('#snv').css('cursor', 'pointer');
              }, function() { // mouse out
                  highlightRelatedNodes(node.id, false);
                  $('#snv').css('cursor', 'move');
              });                            

              //click on node event
              $(ui).click(function() {
                //publications and metadata...
                /*if(node.data.meta) {
                  if(node.data.type == "person") {
                    $('#meta').html("<img src=\"img/ajax-loader.gif\" />");
                    $.ajax({
                      url: "http://<?php echo $HOST ?>/ag_couch_proxy/proxy-meta.php?center="+node.data.meta, 
                      dataType: "json",
                      cache: false
                    }).done(function( json ) {
                      $('#meta').html("<h3>"+node.data.meta+"</h3>");
                      //$('#meta').append("<small><a href=\"snv.php?center="+node.data.meta+"\">Center here... <img alt=\"Center here\" src=\"img/chart.png\"></a></small>");
                      $('#meta').append("<hr>")
                      $('#meta').append("<h3>Articles</h3>");
                      for(row in json.rows) {
                        var article = json.rows[row].value.title;
                        var resource = json.rows[row].value.resource;
                        var language = json.rows[row].value.language;
                        var issued = json.rows[row].value.issued;
                        if(article != undefined && resource != undefined){
                          $('#meta').append("<span>"+article+"</span>");
                          $('#meta').append(" <img src=\"img/flags/"+language+".png\"> ");                      
                          $('#meta').append("<small>"+issued+"</small>");                      
                          $('#meta').append("<small> <a target=\"_blank\" href=\""+resource+"\">Go to source...</a></small>");                      
                          $('#meta').append("<hr>");
                        }
                      }
                      //$('#meta').append("</ul>");
                    });
                  }
                  else if(node.data.type == "article"){
                    //TODO: show co-authors in meta
                  }                  
                }*/

                //navigations
                if(node.data.type == "person" && center != node.data.meta){
                  //if(confirm("Goto to node: "+node.data.meta+" (cancel to read publications)")){
                    renderer.dispose();
                    main(node.data.meta, center);
                  //}
                }
                  
              });

              return ui;
            }).placeNode(function(nodeUI, pos) {
                // 'g' element doesn't have convenient (x,y) attributes, instead
                // we have to deal with transforms: http://www.w3.org/TR/SVG/coords.html#SVGGlobalTransformAttribute 
                nodeUI.attr('transform', 
                            'translate(' + 
                                  (pos.x - nodeSize/2) + ',' + (pos.y - nodeSize/2) + 
                            ')');
            }); 

            var layout = Viva.Graph.Layout.forceDirected(graph, {
                springLength : 150,
                springCoeff : 0.0005,
                dragCoeff : 0.02,
                gravity : -1.6
            });

            //Style the links
            graphics.link(function(link){
                return Viva.Graph.svg('path')
                           .attr('stroke', '#DDDDDD')
                           .attr('stroke-width', '1');
            }).placeLink(function(linkUI, fromPos, toPos) {
                // linkUI - is the object returend from link() callback above.
                var data = 'M' + fromPos.x + ',' + fromPos.y + 
                           'L' + toPos.x + ',' + toPos.y;

                // 'Path data' (http://www.w3.org/TR/SVG/paths.html#DAttribute ) 
                // is a common way of rendering paths in SVG:
                linkUI.attr("d", data);
            });

            // Render the graph
            var renderer = Viva.Graph.View.renderer(graph, {
                    graphics : graphics, 
                    layout : layout,
                    container : document.getElementById('snv'),
                });
            renderer.run();


            /*graph.forEachNode(function(node){
                console.log(node.id, node.data);
            });*/
        }
    </script>
    
    <style type="text/css" media="screen">
        html, body, svg { 
          width: 100%; 
          height: 100%;
          font-family: Arial, 'Trebuchet MS', Verdana, Helvetica, sans-serif;
        }
        #meta {
          
          background: #eeeeee;
          border-radius: 10px;
          z-index: 1;
          padding: 10px;
          font-size: 75%;
          overflow: auto;
        }
        #search {
          /*position: absolute;
          top: 5px;
          right: 5px;
          width: 200px;
          height:50px;*/
          background: #eeeeee;
          border-radius: 10px;
          z-index: 1;
          padding: 10px;
          font-size: 75%;
          overflow: auto;
          margin-bottom: 20px;
        }
        #sidebar {
          /*position: absolute;
          top: 5px;
          left: 5px;*/
          width: 200px;
          height:500px;
        }

        #snv {
          width: 900px;
          height: 700px;
          position: fixed;
          left: 200px;
          cursor:move;
        }

        .autocomplete-suggestions { border: 1px solid #999; background: #FFF; overflow: auto;font-size:75%; }
        .autocomplete-suggestion { padding: 2px 5px; white-space: nowrap; overflow: hidden; }
        .autocomplete-selected { background: #F0F0F0; }
        .autocomplete-suggestions strong { font-weight: normal; color: #3399FF; }
    </style>
</head>
<body onload='main()'>

  <div id='snv'></div>
  <div id='sidebar'>
    <div id='search'>
      <div style="font-weight:bold;">Search Authors:</div>
      <div><input type="text" name="q" id="query" style="width:175px" autocomplete="off" /></div>
      <div><a id="gotosearch" href="snv.php?center="><img alt="GO!" src="img/go.png"></a></div>
    </div>
    <div id='meta'></div>
  </div>
    

</body>
</html>