<p><b>Available Templates:</b></p>

<ul>
<%foreach $tpl in $templates%>
	<li><a href="test.php?t=<%$tpl%>&s=<%$s%>"><%$tpl%></a></li>
<%end%>
</ul>

<% if !$s %>
	<a href="test.php?s=1">[view source]</a>
<% else %>
	<a href="test.php">[hide source]</a>
<% end %>

<% echo $source %>
