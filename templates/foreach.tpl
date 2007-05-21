<html>
	<head>
		<title>Test Foreach</title>
	</head>
	<body>
		<p><b>Test Foreach</b></p>

		<ul>
		<% foreach $i in $list %>
			<li><% $i %></li>
		<% end %>
		</ul>
		<ul>
		<% foreach $k,$i in $list %>
			<li><% $k %>=<% $i %></li>
		<% end %>
		</ul>

		<% echo $source %>
	</body>
</html>
