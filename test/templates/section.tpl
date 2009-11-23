{% $title = 'Section Tests' %}
{% include tpl= 'header' %}

<p>Expect: <br/>
Result: {% section test %}
TEST
{% /section %}</p>

<p>Expect: TEST<br/>
Result:	{% insert test %}</p>

{% include tpl= 'footer' %}
