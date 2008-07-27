{% $title = 'Variable Tests' %}
{% include tpl= 'header' %}

<p>Expect: dancing mice<br>
Result: {% $test %}

<p>Expect: 10<br>
Result: {% $i %}

<p>Expect: 50<br/>
Result: {% $i*5 %}</p>

<p>Expect: 7<br/>
Result: {% $i=7 ; $i %}</p>

<p>Expect: 35<br/>
Result: {% $i*5 %}</p>

{% include tpl= 'footer' %}
