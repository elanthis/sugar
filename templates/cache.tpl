<% $title = 'Cache Tests' %>
<% include header %>

<p>cache:</p>
<p>$i = random(); $i * 2: <% $i = random(); $i * 2 %></p>

<p>nocache:</p>
<% nocache %>
<p>$i = random(); $i * 2: <% $i = random(); $i * 2 %></p>
<% end %>

<% include footer %>
