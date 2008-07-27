{% $title = 'Comparison Tests' %}
{% include 'header' %}

<p>Expect: true<br>
Result: {% 1+1=2 %}

<p>Expect: true<br>
Result: {% 2+5!=6 %}

<p>Expect: true<br>
Result: {% 1||0 %}

<p>Expect: false<br>
Result: {% 1&&0 %}

<p>Expect: false<br>
Result: {% 2>3 %}

<p>Expect: true<br>
Result: {% 3>1 %}


{% include 'footer' %}
