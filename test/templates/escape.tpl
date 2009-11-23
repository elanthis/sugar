{% section title %}Escape Modifier{% /section %}

<p>Test: '&lt;test&gt;'<br/>
Expect: &lt;test&gt;<br/>
Result: {% '<test>' %}</p>

<p>Test: '&lt;test&gt;'|escape<br/>
Expect: &lt;test&gt;<br/>
Result: {% '<test>'|escape %}</p>

<p>Test: &lt;test&gt;'|escape:'xml'<br/>
Expect: &lt;test&gt;<br/>
Result: {% '<test>'|escape:'xml' %}</p>

<p>Test: '&lt;test&gt;'|escape:'url'<br/>
Expect: %3Ctest%3E<br/>
Result: {% '<test>'|escape:'url' %}</p>

<p>Test: '"test"'|escape<br/>
Expect: &quot;test&quot;<br/>
Result: {% '"test"'|escape %}</p>

<p>Test: '"test"'|escape|escape<br/>
Expect: &amp;quot;test&amp;quot;<br/>
Result: {% '"test"'|escape|escape %}</p>

<p>Test: '"test"'|escape:'js'|escape<br/>
Expect: '\&quot;test\&quot;'<br/>
Result: {% '"test"'|escape:'js'|escape %}</p>

<p>Test: showHtmlNoEscape html='&lt;b&gt;hi&lt;/b&gt;'<br/>
Expect: <b>hi</b><br/>
Test: {% showHtmlNoEscape html='<b>hi</b>' %}</p>
