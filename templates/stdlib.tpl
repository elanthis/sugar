<% $title = 'stdlib tests' %>
<% include header %>

<p>date 'Y-m-d': <% date 'Y-m-d' %></p>
<p>date format='m/d/Y' date='next Tuesday': <% date format='Y-m-d' date='next Tuesday' %></p>
<p>date: <% date %></p>

<p>jsValue [12/4,['one'],2*3]: <% jsValue [12/4,['one'],2*3] %></p>
<p>jsValue 'sally sells sea shells': <% jsValue 'sally sells sea shells' %></p>

<p>eval source='&lt;% $x = 4; $x*$x %&gt;': <% eval source='<% $x = 4; $x*$x %>' %></p>

<p>urlEncode 'this+is%(illegal)in#url': <% urlEncode 'this+is%(illegal)in#url' %></p>
<p>urlEncodeAll a=foo bar='baz+gar' boo='1%2 3': <% urlEncodeAll a=foo bar='baz+gar' boo='1%2 3' %></p>

<% include footer %>
