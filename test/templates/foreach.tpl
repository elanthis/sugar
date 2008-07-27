{% $title = 'Foreach Tests' %}
{% include tpl= 'header' %}

<p>Test: foreach $i in $list; $i..','; end<br/>
Expect: one,two,three,bar,<br/>
Result: {% foreach $i in $list; $i..','; end %}</p>

<p>Test: foreach $k,$i in $list; $k .. '=' .. $i .. ','; end<br/>
Expect: 0=one,1=two,2=three,foo=bar,<br/>
Result: {% foreach $k,$i in $list; $k .. '=' .. $i .. ','; end %}</p>

<p>Test: foreach $i in [1, 'one', 'bar', 42]; $i..','; end<br/>
Expect: 1,one,bar,42,<br/>
Result: {% foreach $i in [1, 'one', 'bar', 42]; $i..','; end %}</p>

{% include tpl= 'footer' %}
