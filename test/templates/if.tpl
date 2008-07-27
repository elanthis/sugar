{% $title = 'If Tests' %}
{% include tpl= 'header' %}

{% $test = 24 %}

<p>Expect: A<br/>
Result:	{% if $test>10 ; if $test>20; 'A'; else; 'B'; end; else; 'C'; end %}</p>

<p>Expect: B<br/>
Result: {% if $test%4=1 ; 'A'; elif $test%4==0; 'B'; else ; 'C' ; end %}</p>

{% include tpl= 'footer' %}
