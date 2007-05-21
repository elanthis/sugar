<html>
	<head>
		<title>Test Variables</title>
	</head>
	<body>
		<p><b>Test Variables</b></p>

		<p>$test: <b><% $test %></b></p>
		<p>$i: <b><% $i %></b></p>
		<p>$i*5=<% $i*5 %> $i=7<% $i=7 %> $i*5=<% $i*5 %></p>
		<p><% $html %> <% echo $html %></p>
		<p>$test+$i: <% $test+$i %> $i+$test: <% $i+$test %></p>

		<% echo $source %>
	</body>
</html>
