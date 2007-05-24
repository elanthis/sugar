<% $title = 'All Tests' %>
<% include header %>

<ul>
<%foreach $tpl in $templates%>
	<% if $tpl != 'index' && $tpl != 'header' && $tpl != 'footer' %>
		<li><a href="test.php?t=<%$tpl%>"><%$tpl%></a></li>
	<% end %>
<%end%>
</ul>

<% include footer %>
