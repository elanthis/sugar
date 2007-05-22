<html>
	<head>
		<title>Test Functions</title>
	</head>
	<body>
		<p><b>Test Functions</b></p>

		<p>$obj->bar <% $obj->bar %></p>
		<p>$obj->doit(1,2,3) <% $obj->doit(1,2,3) %></p>
		<p>$obj->foo() <% $obj->foo() %></p>
		<p>1+$obj->foo()*5 <% 1+$obj->foo()*5 %></p>

		<% echo $source %>
	</body>
</html>
