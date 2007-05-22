<% $title = 'All Tests' %>
<% include header %>

<ul>
<%foreach $tpl in $templates%>
	<% if $tpl != 'index.tpl' && $tpl != 'header.tpl' && $tpl != 'footer.tpl' %>
		<li><a href="test.php?t=<%$tpl%>&s=<%$s%>"><%$tpl%></a></li>
	<% end %>
<%end%>
</ul>

<% include footer %>
