<% $title = 'Array Tests' %>
<% include header %>

<p>$list: <% $list %></p>
<p>$list.1: <b><% $list.1 %></b></p>
<p>$list.foo: <b><% $list.foo %></b></p>
<p>"one" in $list: <b><% "one" in $list %></b>
<p>$c=2 $list.$c <b><% $c=2 %><% $list.$c %></b></p>

<% include footer %>
