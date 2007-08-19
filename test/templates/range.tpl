<% $title = 'Range Loop Tests' %>
<% include 'header' %>

<p>1..3</p>
<ul>
<% loop $i in 1, 3 %>
	<li><% $i %></li>
<% end %>
</ul>

<p>3..1 step -1</p>
<ul>
<% loop $i in 3, 1, -1 %>
	<li><% $i %></li>
<% end %>
</ul>

<% $i = 6 %>
<p>$i = <% $i %></p>
<p>3..$i step 1</p>
<ul>
<% loop $i in 3, $i %>
	<li><% $i %></li>
<% end %>
</ul>

<p>3..$i*2 step 2</p>
<ul>
<% loop $i in 3, $i*2, 2 %>
	<li><% $i %></li>
<% end %>
</ul>

<p>3..1 step 1</p>
<ul>
<% loop $i in 3, 1 %>
	<li><% $i %></li>
<% end %>
</ul>

<p>3..1 step -2</p>
<ul>
<% loop $i in 3, 1, -2 %>
	<li><% $i %></li>
<% end %>
</ul>

<% include 'footer' %>
