<% $title = 'jsValue Tests' %>
<% include 'header' %>

<p>Expect: 'dancing mice'<br>
Result: <% jsValue $test %>

<p>Expect: {0:'one',1:'two',2:'three','foo':'bar'}<br>
Result: <% jsValue $list %>

<p>Expect: 10<br>
Result: <% jsValue $i %>

<p>Expect: {'phpType':'Test','bar':'BAR'}<br>
Result: <% jsValue $obj %>


<% include 'footer' %>
