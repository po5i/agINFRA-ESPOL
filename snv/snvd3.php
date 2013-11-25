<?php include "../config.php" ?>
<?php
if(isset($_GET["center"])) 
  $center = $_GET["center"]; 
else
  $center = "Salvador%20Sanchez-Alonso";
$center = utf8_encode($center);
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
    float:left;width:160px;height:100%;border-right:solid thin gray;
  }

  #main_snv {
    float:right;width:440px;height:100%;
  }

  </style>
  <script src="http://d3js.org/d3.v3.min.js"></script>
  <!--script type="text/javascript" src="http://code.jquery.com/jquery-1.6.2.min.js"></script--> 
  <script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js"></script>

  <script type="text/javascript" src="dist/jquery.tipsy.js"></script>
  <link href="dist/tipsy.css" rel="stylesheet" type="text/css" />

  <link rel="stylesheet" href="../search/css/accordionmenu.css" type="text/css" media="screen" />
<head>



<body>
  

  <div id="left_snv">
    <img src="img/userprofile.png" style="width:159px;height:159px;border:solid thin gray;">
    <div class="center_name"><?php echo $center ?></div>
    <ul class="accordion">
      
      <!-- mode-->
      <li class="cloud">
        <a href="#">Graph mode</a>
        <ul class="sub-menu" id="mode_sub">
          <li><a href="#" onclick="return refreshFull()" style="padding-left:5px">Full</a></li>
          <li><a href="#" onclick="return refreshSimple()" style="padding-left:5px">Only co-authorship</a></li>
        </ul>
      </li>

      <!-- publications-->
      <li class="files">
        <a href="#">Publications</a>
        <ul class="sub-menu" id="publications_sub">
          <!--li><a href="#"><em>01</em>Sub Menu<span>1</span></a></li-->
        </ul>
      </li>
            
    </ul>
  </div>
  <script type="text/javascript">

    function refreshFull(){
      drawSNV("full");
      return false;
    }

    function refreshSimple(){
      drawSNV("simple");
      return false;
    }

    $(document).ready(function() {
      
      ///////////////////////////////////////
      //main
      drawSNV("full");

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

    });
  </script>



  <div id="main_snv">
  </div>



  <div style="clear:both"></div>




  <script>

  function drawSNV(mode){
    var width = "440",
        height = "370";

    var color = d3.scale.category10();

    var force = d3.layout.force()
        .charge(-120)
        .linkDistance(100)
        .size([width, height]);

    //var svg = d3.select("body").append("svg")
    $("#main_snv").empty();    
    var svg = d3.select("#main_snv").append("svg")
        .attr("width", width)
        .attr("height", height);

    d3.json("http://<?php echo $HOST ?>/ag_couch_proxy/proxy-view-d3.php?center=<?php echo $center ?>&mode="+mode, function(error, graph) {
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

      var node = svg.selectAll(".node")
          .data(graph.nodes)
          .enter().append("circle")
            .attr("class", "node")
            .attr("r", 12)
            .style("fill", function(d) { return color(d.group); })
            //.on("click", function(d,i) { alert(d.name); })
            //.on("mouseover", function(d,i) { console.log(d.name); })
            .call(force.drag)
            .on("mouseover", fade(.1))    //highlight
            .on("mouseout", fade(1));     //highlight

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