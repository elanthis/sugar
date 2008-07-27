{% $title = 'Fetch Tests' %}
{% include tpl= 'header' %}

<p>Expect: 1+10=11<br>
Result: {% $fetch_string %}

<p>Expect: 1+1=11<br>
Result: {% $fetch_file %}

<p>Expect: 1+1=11<br>
Result: {% $fetch_cfile %}

{% include tpl= 'footer' %}
