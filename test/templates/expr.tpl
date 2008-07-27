{% $title = 'Expression Tests' %}
{% include tpl= 'header' %}

{% $a = 4 %}
{% $b = 15 %}
{% $c = 7 %}

<p>Expect: 17<br>
Result: {% 1+2*6+4 %}

<p>Expect: 1<br>
Result: {% 2*$a+-$c %}

<p>Expect: true<br>
Result: {% 0||0+1 %}

<p>Expect: 9<br>
Result: {% 3*(-8+$a/2)/-2 %}

{% include tpl= 'footer' %}
