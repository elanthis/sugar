<% $title = 'Array Tests' %>
<% include 'header' %>

<p>Expect: [1,2,3]<br>
Result: <% array(1,2,3) %>
<p>Expect: [1,2,3]<br>
Result: <% [1,2,3] %>
<p>Expect: ['foo','bar','baz']<br>
Result: <% ['foo','bar','baz'] %>
<p>Expect: {0:'one',1:'two',2:'three','foo':'bar'}<br>
Result: <% $list %>
<p>Expect: two<br>
Result: <% $list.1 %>
<p>Expect: bar<br>
Result: <% $list.foo %>
<p>Expect: true<br>
Result: <% "one" in $list %>
<p>Expect: three<br>
Result: <% $c=2 %><% $list.$c %>
<p>Expect: three<br>
Result: <% $c=2 %><% $list[$c] %>

<% include 'footer' %>
