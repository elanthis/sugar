<html>
	<head>
		<title>Test If</title>
	</head>
	<body>
		<p><b>Test If</b></p>

		<% $test=24 %>
		<p>$test: <% $test %></p>
		<p>
			<% if $test>10 %>
				<% if $test>20 %>
					$test>20 (correct)
				<% else %>
					$test<=20 (wrong)
				<% end %>
			<% else %>
				$test<=10 (wrong)
			<% end %>
		</p>
		<p>
			<% if $test%4=1 %>
				$test%4=1 (wrong)
			<% else %>
				$test%4!=1 (correct)
			<% end %>
		</p>

		<% echo $source %>
	</body>
</html>
