Test: '&lt;test&gt;'
Expect: &lt;test&gt;
Result: {{ '<test>' }}

Test: '&lt;test&gt;'|escape
Expect: &lt;test&gt;
Result: {{ '<test>'|escape }}

Test: &lt;test&gt;'|escape:'xml'
Expect: &lt;test&gt;
Result: {{ '<test>'|escape:'xml' }}

Test: '&lt;test&gt;'|escape:'url'
Expect: %3Ctest%3E
Result: {{ '<test>'|escape:'url' }}

Test: '"test"'|escape
Expect: &quot;test&quot;
Result: {{ '"test"'|escape }}

Test: '"test"'|escape|escape
Expect: &amp;quot;test&amp;quot;
Result: {{ '"test"'|escape|escape }}

Test: '"test"'|escape:'js'|escape
Expect: '\&quot;test\&quot;'
Result: {{ '"test"'|escape:'js'|escape }}
