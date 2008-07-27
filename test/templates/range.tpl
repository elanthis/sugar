{% $title = 'Range Loop Tests' %}
{% include tpl= 'header' %}

<p>Test: loop $i in 1,3 ; $i ; end<br/>
Expect: 123<br/>
Result: {% loop $i in 1,3 ; $i ; end %}</p>

<p>Test: loop $i in 3,1,-1 ; $i ; end<br/>
Expect: 321<br/>
Result: {% loop $i in 3,1,-1 ; $i ; end %}</p>

<p>Test: loop $i in 3,4*6,7 ; $i ; end<br/>
Expect: 3101724<br/>
Result: {% loop $i in 3,4*6,7 ; $i ; end %}</p>

<p>Test: loop $i in 2,1 ; $i ; end <br/>
Expect: <br/>
Result: {% loop $i in 2,1 ; $i ; end %}</p>

{% include tpl= 'footer' %}
