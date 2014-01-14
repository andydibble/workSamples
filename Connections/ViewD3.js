var GRAPH_CONFIG = {
	'width':960,
	'height':500,
	'graphCharge':-120,
	'graphlinkDistance':30,	
	'nodeLinkDistance':90,
	'alpha':200,
	'gravity':0.02
};

$(document).ready(
		function() {

			var force = d3.layout.force()
				.charge(GRAPH_CONFIG.graphCharge)
				.linkDistance(GRAPH_CONFIG.graphLinkDistance).size(
				[ GRAPH_CONFIG.width, GRAPH_CONFIG.height ]);

			var svg = d3.select("svg")
					.attr("width", GRAPH_CONFIG.width)
					.attr("height",	GRAPH_CONFIG.height);

			// focusId and graph data set in view

			d3.json(data, function(error, graph) {
				graph = JSON.parse(data);

				var idMap = [];
				$(graph.users).each(function(i, n) {
					idMap[n.id] = i;
				});

				$(graph.edges).each(function(i, l) {
					graph.edges[i].source = idMap[l.source];
					graph.edges[i].target = idMap[l.target];
				});

				force.nodes(graph.users).links(graph.edges).linkDistance(GRAPH_CONFIG.nodeLinkDistance)
						.gravity(GRAPH_CONFIG.gravity).alpha(GRAPH_CONFIG.alpha).start();

				var link = svg.selectAll(".d3-link").data(graph.edges).enter()
						.append("line").attr("class", "d3-link").style(
								"stroke-width", function(d) {
									return Math.sqrt(d.value);
								});

				var node = svg.selectAll(".d3-node").data(graph.users).enter()
						.append("g").attr(
								"class",
								function(d) {
									return d.id == focusId ? "d3-focus-node"
											: "d3-node";
								}).call(force.drag);

				node.append('circle').attr("r", function(d) {
					return d.id == focusId ? FOCUS_NODE_RADIUS : NODE_RADIUS;
				})

				.attr("style", function(d) {
					if ($("#user-img-" + d.id).length) {
						return "fill: url(#user-img-" + d.id + ")";
					} else {
						if (focusId == d.id) {
							return "fill: url(#no-img-focus)";
						} else {
							return "fill: url(#no-img)";
						}
					}
				})

				node.append("text").attr("dx", 12).attr("dy", ".35em").attr(
						'fill', "#000").text(function(d) {
					return d.name;
				});

				node.append("title").text(function(d) {
					return d.name;
				});

				force.on("tick", function() {
					link.attr("x1", function(d) {
						return d.source.x;
					}).attr("y1", function(d) {
						return d.source.y;
					}).attr("x2", function(d) {
						return d.target.x;
					}).attr("y2", function(d) {
						return d.target.y;
					});

					node.attr("transform", function(d) {
						return "translate(" + d.x + "," + d.y + ")";
					});
				});
			});
		});