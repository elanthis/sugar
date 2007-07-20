<% $title = 'While Tests' %>
<% include header %>

<p>print 0 through 9</p>
<p>$i = 0; while $i < 10; $i ; $i = $i + 1; end</p>
<% $i = 0; while $i < 10; $i ; $i = $i + 1; end %>

<p>print powers of two from 2^0 (1) to 2^5 (32); notice 60 is not a power of two</p>
<p>$i = 1; while $i in [ 1, 2, 4, 8, 16, 32, 60, 128 ]; $i ; $i = $i * 2; end</p>
<% $i = 1; while $i in [ 1, 2, 4, 8, 16, 32, 60, 128 ]; $i ; $i = $i * 2; end %>

<% include footer %>
