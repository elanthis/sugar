<html>
	<head>
		<title>Test Expressions</title>
	</head>
	<body>
		<p><b>Test Expressions</b></p>

		<% $a = 4 %>
		<% $b = 15 %>
		<% $c = 7 %>

		<p>Result: <% 1+2*6+4 %> (17)</p>
		<p>Result: <% 2*$a+-$c %> (1)</p>
		<p>Result: <% 0||0+1 %> (true)</p>
		<p>Result: <% 3*(-8+$a/2)/-2 %> (9)</p>

		<% echo $source %>
	</body>
</html>
