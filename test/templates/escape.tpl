{% $title = 'Escape Modifier' %}
{% include tpl='header' %}

<p>Test: '&lt;test&gt;'<br/>
Expect: &lt;test&gt;<br/>
Result: {% '<test>' %}

<p>Test: '&lt;test&gt;'|escape<br/>
Expect: &lt;test&gt;<br/>
Result: {% '<test>'|escape %}

<p>Test: &lt;test&gt;'|escape:'xml'<br/>
Expect: &lt;test&gt;<br/>
Result: {% '<test>'|escape:'xml' %}

<p>Test: '&lt;test&gt;'|escape:'url'<br/>
Expect: %3Ctest%3E<br/>
Result: {% '<test>'|escape:'url' %}

<p>Test: '"test"'|escape<br/>
Expect: &quot;test&quot;<br/>
Result: {% '"test"'|escape %}

<p>Test: '"test"'|escape|escape<br/>
Expect: &amp;quot;test&amp;quot;<br/>
Result: {% '"test"'|escape|escape %}

<p>Test: '"test"'|escape:'js'|escape<br/>
Expect: '\&quot;test\&quot;'<br/>
Result: {% '"test"'|escape:'js'|escape %}

{% include tpl='footer' %}
