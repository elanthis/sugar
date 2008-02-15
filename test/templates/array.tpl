<% $title = 'Array Tests' %>
<% include 'header' %>

<p>Test: array(1,2,3)<br/>
Expect: [1,2,3]<br>
Result: <% array(1,2,3) %></p>

<p>Test: [1,2,3]<br/>
Expect: [1,2,3]<br>
Result: <% [1,2,3] %></p>

<p>Test: ['foo','bar','baz']<br/>
Expect: ['foo','bar','baz']<br>
Result: <% ['foo','bar','baz'] %></p>

<p>Test: explode ' ', 'one two three'<br/>
Expect: ['one','two','three']<br>
Result: <% explode ' ', 'one two three' %></p>

<p>Test: $list<br/>
Expect: {0:'one',1:'two',2:'three','foo':'bar'}<br>
Result: <% $list %></p>

<p>Test: $list.1<br/>
Expect: two<br>
Result: <% $list.1 %></p>

<p>Test: $list[1]<br/>
Expect: two<br>
Result: <% $list[1] %></p>

<p>Test: $list->1<br/>
Expect: two<br>
Result: <% $list->1 %></p>

<p>Test: $list.notdefined<br/>
Expect: <br>
Result: <% $list.notdefined %></p>

<p>Test: $list.foo<br/>
Expect: bar<br>
Result: <% $list.foo %></p>

<p>Test: $list['foo']<br/>
Expect: bar<br>
Result: <% $list['foo'] %></p>

<p>Test: $list->foo<br/>
Expect: bar<br>
Result: <% $list->foo %></p>

<p>Test: "one" in $list<br/>
Expect: true<br>
Result: <% "one" in $list %></p>

<p>Test: "nope" in $list<br/>
Expect: false<br>
Result: <% "nope" in $list %></p>

<p>Test: "nope" !in $list<br/>
Expect: true<br>
Result: <% "nope" !in $list %></p>

<p>Test: $c=2 ; $list.$c<br/>
Expect: three<br>
Result: <% $c=2 ; $list.$c %></p>

<p>Test: $c=2 ; $list[$c]<br/>
Expect: three<br>
Result: <% $c=2 ; $list[$c] %></p>

<% include 'footer' %>
