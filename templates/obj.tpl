<% $title = 'Object Tests' %>
<% include header %>

<p>$obj->bar <% $obj->bar %></p>
<p>$obj->doit(1,2,3) <% $obj->doit(1,2,3) %></p>
<p>$obj->foo() <% $obj->foo() %></p>
<p>1+$obj->foo()*5 <% 1+$obj->foo()*5 %></p>

<% include footer %>
