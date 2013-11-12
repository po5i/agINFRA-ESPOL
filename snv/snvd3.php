<?php include "../config.php" ?>
<?php
if(isset($_GET["center"])) 
  $center = $_GET["center"]; 
else
  $center = "Salvador%20Sanchez-Alonso";
$center = utf8_encode($center);
?>
<!DOCTYPE html>
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

</style>
<body>
<script src="http://d3js.org/d3.v3.min.js"></script>
<script type="text/javascript" src="http://code.jquery.com/jquery-1.6.2.min.js"></script> 
<script type="text/javascript" src="dist/jquery.tipsy.js"></script>
<link href="dist/tipsy.css" rel="stylesheet" type="text/css" />
<script>

var width = 580,
    height = 500;

var color = d3.scale.category10();

var force = d3.layout.force()
    .charge(-120)
    .linkDistance(75)
    .size([width, height]);

var svg = d3.select("body").append("svg")
    .attr("width", width)
    .attr("height", height);

d3.json("http://<?php echo $HOST ?>/ag_couch_proxy/proxy-view-d3.php?center=<?php echo $center ?>", function(error, graph) {
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
        .attr("r", 8)
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

</script>