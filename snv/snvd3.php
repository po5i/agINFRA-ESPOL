<?php include "../config.php" ?>
<?php
if(isset($_GET["center"])) 
  $center = $_GET["center"]; 
else
  $center = "Salvador%20Sanchez-Alonso";

if(mb_detect_encoding($center) != "UTF-8")
  $center = utf8_encode($center);

$graphtype = isset($_REQUEST["graphtype"]) ? $_REQUEST["graphtype"] : "PersonGraph";  //PersonGraph|InstitutionGraph|ProjectGraph|PaperGraph|CountryGraph
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <style>

  .node {
    stroke: #eee;
    stroke-width: 1.5px;
  }

  .link {
    stroke: #999;
    stroke-opacity: .6;
  }

  .center_name {
    text-align: center;
    color: #797979;
    font: bold 12px/21px Arial, sans-serif;
  }

  #left_snv {
    /*float:left;*/
    position: absolute;
    width:160px;
    /*height:100%;*/
    border-right:solid thin gray;
    background: #ffffff;
    opacity: 0.75;
  }

  #main_svg {
    /*float:right;*/
    width:100%;
    height:100%;
  }
  #main_snv {
    /*float:right;*/
    /*width:100%;*/
    height:100%;
  }

  </style>
  <script src="http://d3js.org/d3.v3.min.js"></script>
  <!--script type="text/javascript" src="http://code.jquery.com/jquery-1.6.2.min.js"></script--> 
  <script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js"></script>

  <script type="text/javascript" src="dist/jquery.tipsy.js"></script>
  <link href="dist/tipsy.css" rel="stylesheet" type="text/css" />

  <link rel="stylesheet" href="../search/css/accordionmenu.css" type="text/css" media="screen" />

  <script>
  /* 
  Native FullScreen JavaScript API
  -------------
  Assumes Mozilla naming conventions instead of W3C for now
  */

  (function() {
    var 
      fullScreenApi = { 
        supportsFullScreen: false,
        isFullScreen: function() { return false; }, 
        requestFullScreen: function() {}, 
        cancelFullScreen: function() {},
        fullScreenEventName: '',
        prefix: ''
      },
      browserPrefixes = 'webkit moz o ms khtml'.split(' ');
    
    // check for native support
    if (typeof document.cancelFullScreen != 'undefined') {
      fullScreenApi.supportsFullScreen = true;
    } else {   
      // check for fullscreen support by vendor prefix
      for (var i = 0, il = browserPrefixes.length; i < il; i++ ) {
        fullScreenApi.prefix = browserPrefixes[i];
        
        if (typeof document[fullScreenApi.prefix + 'CancelFullScreen' ] != 'undefined' ) {
          fullScreenApi.supportsFullScreen = true;
          
          break;
        }
      }
    }
    
    // update methods to do something useful
    if (fullScreenApi.supportsFullScreen) {
      fullScreenApi.fullScreenEventName = fullScreenApi.prefix + 'fullscreenchange';
      
      fullScreenApi.isFullScreen = function() {
        switch (this.prefix) {  
          case '':
            return document.fullScreen;
          case 'webkit':
            return document.webkitIsFullScreen;
          default:
            return document[this.prefix + 'FullScreen'];
        }
      }
      fullScreenApi.requestFullScreen = function(el) {
        return (this.prefix === '') ? el.requestFullScreen() : el[this.prefix + 'RequestFullScreen']();
      }
      fullScreenApi.cancelFullScreen = function(el) {
        return (this.prefix === '') ? document.cancelFullScreen() : document[this.prefix + 'CancelFullScreen']();
      }   
    }

    // jQuery plugin
    if (typeof jQuery != 'undefined') {
      jQuery.fn.requestFullScreen = function() {
    
        return this.each(function() {
          var el = jQuery(this);
          if (fullScreenApi.supportsFullScreen) {
            fullScreenApi.requestFullScreen(el);
          }
        });
      };
    }

    // export api
    window.fullScreenApi = fullScreenApi; 
  })();

  </script>
<head>



<body>
  
  <div id="left_snv">
    <img src="img/userprofile.png" style="width:159px;height:159px;border:solid thin gray;">
    <div class="center_name"><?php echo $center ?></div>
    <ul class="accordion">
      
      <!-- mode-->
      <li class="cloud">
        <a href="#">Graph filtering</a>
        <ul class="sub-menu" id="mode_sub">
          <li><a href="#" onclick="return refreshFull()" style="padding-left:5px">Full</a></li>
          <li><a href="#" onclick="return refreshSimple()" style="padding-left:5px">Only co-authorship</a></li>
          <li><a href="#" onclick="return refreshLanguage()" style="padding-left:5px">Languages</a></li>
          <li><a href="#" onclick="return refreshFormat()" style="padding-left:5px">Format of papers</a></li>
        </ul>
      </li>

      <!-- publications-->
      <li class="files">
        <a href="#">Publications</a>
        <ul class="sub-menu" id="publications_sub">
          <!--li><a href="#"><em>01</em>Sub Menu<span>1</span></a></li-->
        </ul>
      </li>

      <li class="sign">
        <a id="fsbutton" href="#">Go Fullscreen</a>
      </li>
            
    </ul>
  </div>
  <script type="text/javascript">

    function refreshFull(){
      drawSNV("PersonGraph","full");
      return false;
    }

    function refreshSimple(){
      drawSNV("PersonGraph","simple");
      return false;
    }

    function refreshLanguage(){
      drawSNV("LanguageGraph","full");
      return false;
    }

    function refreshFormat(){
      drawSNV("FormatGraph","full");
      return false;
    }

    $(document).ready(function() {
      
      ///////////////////////////////////////
      //main
      drawSNV("PersonGraph","full");

      ///////////////////////////////////////
      //Accordion
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

      ///////////////////////////////////////
      //ajax for retrieve publications
      $.ajax({
              url: "http://<?php echo $HOST ?>/ag_couch_proxy/proxy-meta.php?center=<?php echo $center ?>", 
              dataType: "json",
              cache: false
            }).done(function( json ) {
                            
              
              for(row in json.rows) {
                var article = json.rows[row].value.title;
                var resource = json.rows[row].value.resource;
                var language = json.rows[row].value.language;
                var issued = json.rows[row].value.issued;
                if(article != undefined && resource != undefined){
                  article_short = article.substring(0,21) + "...";
                  $('#publications_sub').append("<li><a target=\"_blank\" href=\""+resource+"\" title=\"Click to open: "+article+"\" style=\"padding-left:5px\">"+article_short+"</a></li>");
                }
              }
            }); 

      /////////////////////////////////////
      //fullscreen
      // do something interesting with fullscreen support
      var fsButton = document.getElementById('fsbutton'),
        fsElement = document.getElementById('main_svg');
      var w = window,
        d = document,
        e = d.documentElement,
        g = d.getElementsByTagName('body')[0];

      if (window.fullScreenApi.supportsFullScreen) {
        // handle button click
        fsButton.addEventListener('click', function() {
          window.fullScreenApi.requestFullScreen(fsElement);
        }, true);
        
        fsElement.addEventListener(fullScreenApi.fullScreenEventName, function() {
          if (fullScreenApi.isFullScreen()) {
            console.log("fullScreen");
            //fsStatus.innerHTML = 'Whoa, you went fullscreen';

            x = /*w.innerWidth || e.clientWidth ||*/ g.clientWidth;
            y = /*w.innerHeight|| e.clientHeight||*/ g.clientHeight;
            //fsElement.attr("width", x).attr("height", y);
            
            //$("#main_snv").css("width",x);
            //$("#main_snv").css("height",y);
            //fsContainer.width = x;
            //fsContainer.height = y;
            $("#main_svg").css("width",x);
            
          } else {
            console.log("Normal");
            //fsStatus.innerHTML = 'Back to normal';

            x = /*w.innerWidth || e.clientWidth ||*/ g.clientWidth;
            y = /*w.innerHeight|| e.clientHeight||*/ g.clientHeight;
            //fsElement.attr("width", x).attr("height", y);
            
            $("#main_svg").css("width",x);

          }
        }, true);
        
      } else {
        //fsStatus.innerHTML = 'SORRY: Your browser does not support FullScreen';
      }
      /*function updateWindow(){
          x = w.innerWidth || e.clientWidth || g.clientWidth;
          y = w.innerHeight|| e.clientHeight|| g.clientHeight;

          fsElement.attr("width", x).attr("height", y);
      }
      window.onresize = updateWindow;*/



    });
  </script>



  <div id="main_snv">
  </div>



  <!--div style="clear:both"></div-->







  <script>

  function drawSNV(graphtype,mode){
    var width = "640",
        height = "480";

    var color = d3.scale.category10();

    var force = d3.layout.force()
        .charge(-120)
        //.linkDistance(100)
        .linkDistance(function(d) {
          //console.log(d); 
          distance = (1/d.value)*200;
          return distance;      //number of relationships found between these two nodes in the dataset
        })
        .size([width, height]);

    //var svg = d3.select("body").append("svg")
    $("#main_snv").empty();    
    var svg = d3.select("#main_snv").append("svg")
        .attr("id", "main_svg")
        //.attr("width", width)
        //.attr("height", height)
        //.attr("width", "100%")
        //.attr("height", "100%")
        //.attr("viewBox", "0 0 640 480")
        .attr("preserveAspectRatio", "xMidYMid meet");


    d3.json("http://<?php echo $HOST ?>/ag_couch_proxy/proxy-view-d3.php?center=<?php echo $center ?>&mode="+mode+"&graphtype="+graphtype, function(error, graph) {
      force
          .nodes(graph.nodes)
          .links(graph.links)
          .on("tick", tick) //highlight
          .start();

      var link = svg.selectAll(".link")
          .data(graph.links)
          .enter().append("line")
          .attr("class", "link")
          .style("stroke-width", function(d) { return Math.sqrt(d.value); });

      var drag = force.drag()
          .on("dragstart", dragstart);

      var node = svg.selectAll(".node")
          .data(graph.nodes)
          .enter().append("circle")
            .attr("class", "node")
            .attr("r", 12)
            .style("fill", function(d) { return color(d.group); })
            //.on("click", function(d,i) { alert(d.name); })
            //.on("mouseover", function(d,i) { console.log(d.name); })
            //.call(force.drag) //use default force drag
            .call(drag)         //let the user drag the nodes and distances
            .on("mouseover", fade(.1))    //highlight
            .on("mouseout", fade(1));     //highlight

      node.on("dblclick", function(d) {
                                        //console.log(d);
                                        if(d.group == 2){
                                          location.href = "snvd3.php?center="+d.name+"&entity=person&graphtype=PersonGraph"; 
                                          //console.log("snvd3.php?center="+d.name+"&graphtype=PersonGraph");
                                        }
      });  //double click

      node.append("title")
          .text(function(d) { return d.name; });

      $('circle').tipsy({ 
              gravity: 'w', 
              html: true, 
              title: function() {
                title = this.__data__.name;
                //var d = this.__data__, c = colors(d.i);
                return title; 
              }
            });

      force.on("tick", function() {
        link.attr("x1", function(d) { return d.source.x; })
            .attr("y1", function(d) { return d.source.y; })
            .attr("x2", function(d) { return d.target.x; })
            .attr("y2", function(d) { return d.target.y; });

        node.attr("cx", function(d) { return d.x; })
            .attr("cy", function(d) { return d.y; });
      });


      //highlight
      var linkedByIndex = {};
        graph.links.forEach(function(d) {
            linkedByIndex[d.source.index + "," + d.target.index] = 1;
        });

      function dragstart(d) {
        d3.select(this).classed("fixed", d.fixed = true);
      }

      
      function isConnected(a, b) {
          return linkedByIndex[a.index + "," + b.index] || linkedByIndex[b.index + "," + a.index] || a.index == b.index;
      }

      function tick() {
          node.attr("cx", function(d) {
              return d.x = Math.max(r, Math.min(w - r, d.x));
          }).attr("cy", function(d) {
              return d.y = Math.max(r, Math.min(h - r, d.y));
          });

          link.attr("x1", function(d) {
              return d.source.x;
          }).attr("y1", function(d) {
              return d.source.y;
          }).attr("x2", function(d) {
              return d.target.x;
          }).attr("y2", function(d) {
              return d.target.y;
          });
      }

      function fade(opacity) {
          return function(d) {
              node.style("stroke-opacity", function(o) {
                  thisOpacity = isConnected(d, o) ? 1 : opacity;
                  this.setAttribute('fill-opacity', thisOpacity);
                  return thisOpacity;
              });

              link.style("stroke-opacity", function(o) {
                  return o.source === d || o.target === d ? 1 : opacity;
              });
          };
      }

    });
  }

  </script>





  

</body>
</html>