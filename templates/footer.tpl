
		<% if !$s %>
			<a href="test.php?t=<% $t %>&s=1">[view source]</a>
		<% else %>
			<% echo $source %>
			<a href="test.php?t=<% $t %>&s=0">[hide source]</a>
		<% end %>
	</body>
</html>
