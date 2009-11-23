{% section title %}Section Tests{% /section %}

<p>Expect: <br/>
Result: {% section test %}
<b>FAILURE</b>
{% /section %}</p>

<p>Expect: TEST2<br/>
Result: {% section|insert test2 %}TEST2{% /section %}</p>

<p>Expect: TEST3<br/>
Result:	{% section test3 %}TEST3{% /section %}{% insert test3 %}</p>
