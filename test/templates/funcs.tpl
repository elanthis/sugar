<% $title = 'Function Tests' %>
<% include 'header' %>

<p>Test: showHtml html='&lt;b&gt;bold&lt;/b&gt;'<br>
Expect: <b>bold</b><br>
Result: <% showHtml html='<b>bold</b>' %>

<p>Test: one 'test'<br>
Expect: Unotest<br>
Result: <% one 'test' %>

<p>Test: showText text=1<br>
Expect: 1<br>
Result: <% showText text=1 %>

<p>Test: showText text=one()<br>
Expect: Uno<br>
Result: <% showText text=one() %>

<p>Test: array foo='a string' bar=array('an', 'array') number=42<br>
Expect: {'foo':'a string','bar':['an','array'],'number':42}<br>
Result: <% array foo='a string' bar=array('an', 'array') number=42 %> 

<% include 'footer' %>
