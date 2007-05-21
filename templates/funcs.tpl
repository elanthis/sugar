<html>
	<head>
		<title>Test Functions</title>
	</head>
	<body>
		<p><b>Test Functions</b></p>

		<p>showHtml html='&lt;b&gt;bold&lt;/b&gt;' : <% showHtml html='<b>bold</b>' %></p>
		<p>one test : <% one test %></p>
		<p>showText text=1 : <% showText text=1 %></p>
		<p>showText text=(one) : <% showText text=(one) %></p>

		<% echo $source %>
	</body>
</html>
