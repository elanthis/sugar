<% $title = 'String Tests' %>
<% include 'header' %>

<p>Test: ''<br>
Expect: <br>
Result: <% '' %>

<p>Test: '\'\"\\'<br>
Expect: '"\<br>
Result: <% '\'\"\\' %>

<p>Test: "\'\"\\"<br>
Expect: '"\<br>
Result: <% "\'\"\\" %>

<% include 'footer' %>
