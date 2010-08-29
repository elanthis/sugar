{{ inherit 'layout.tpl' }}

{{ section title }}Foreach Tests{{ /section }}

<p>Test: foreach $i in $list; $i..','; /foreach<br/>
Expect: one,two,three,bar,<br/>
Result: {{ foreach $i in $list; $i..','; /foreach }}</p>

<p>Test: foreach $k,$i in $list; $k .. '=' .. $i .. ','; /foreach<br/>
Expect: 0=one,1=two,2=three,foo=bar,<br/>
Result: {{ foreach $k,$i in $list; $k .. '=' .. $i .. ','; /foreach }}</p>

<p>Test: foreach $i in [1, 'one', 'bar', 42]; $i..','; /foreach<br/>
Expect: 1,one,bar,42,<br/>
Result: {{ foreach $i in [1, 'one', 'bar', 42]; $i..','; /foreach }}</p>
