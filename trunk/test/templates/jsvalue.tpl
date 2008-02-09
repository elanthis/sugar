<% $title = 'jsValue Tests' %>
<% include 'header' %>

<p>Test: jsValue $test<br>
Expect: 'dancing mice'<br>
Result: <% jsValue $test %>

<p>Test: jsValue $list<br>
Expect: {0:'one',1:'two',2:'three','foo':'bar'}<br>
Result: <% jsValue $list %>

<p>Test: jsValue $i<br>
Expect: 10<br>
Result: <% jsValue $i %>

<p>Test: jsValue $obj<br>
Expect: {'phpType':'Test','bar':'BAR'}<br>
Result: <% jsValue $obj %>

<p>Test: jsValue $newlines<br>
Expect: 'This\nhas\nnewlines!'<br>
Result: <% jsValue $newlines %>

<% include 'footer' %>
