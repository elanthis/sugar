<% $title = 'Raw Text Tests' %>
<% include 'header' %>

<p>Expect: &lt;foo&gt;<br/>
Result: <% '<foo>' %></p>

<p>Expect: checked="checked"<br/>
Result: <% ' checked="checked" ' %></p>

<p>Expect: checked="checked"<br/>
Result: <% checked(1) %></p>

<% include 'footer' %>
