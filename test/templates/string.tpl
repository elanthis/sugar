{% $title = 'String Tests' %}
{% include tpl= 'header' %}

<p>Test: ''<br>
Expect: <br>
Result: {% '' %}

<p>Test: '\'\"\\'<br>
Expect: '"\<br>
Result: {% '\'\"\\' %}

<p>Test: "\'\"\\"<br>
Expect: '"\<br>
Result: {% "\'\"\\" %}

<p>Test: printf format='%04d' params=[42]<br>
Expect: 0042<br>
Result: {% printf format='%04d' params=[42] %}

{% include tpl= 'footer' %}
