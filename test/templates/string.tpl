{% $title = 'String Tests' %}
{% include 'header' %}

<p>Test: ''<br>
Expect: <br>
Result: {% '' %}

<p>Test: '\'\"\\'<br>
Expect: '"\<br>
Result: {% '\'\"\\' %}

<p>Test: "\'\"\\"<br>
Expect: '"\<br>
Result: {% "\'\"\\" %}

<p>Test: printf('%04d', 42)<br>
Expect: 0042<br>
Result: {% printf('%04d', 42) %}

{% include 'footer' %}
