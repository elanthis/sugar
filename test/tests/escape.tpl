Test: '&lt;test&gt;'
Expect: &lt;test&gt;
Result: {{ '' }}

Test: '&lt;test&gt;'|escape
Expect: &lt;test&gt;
Result: {{ ''|escape }}

Test: &lt;test&gt;'|escape:'xml'
Expect: &lt;test&gt;
Result: {{ ''|escape:'xml' }}

Test: '&lt;test&gt;'|escape:'url'
Expect: %3Ctest%3E
Result: {{ ''|escape:'url' }}

Test: '"test"'|escape
Expect: &quot;test&quot;
Result: {{ '"test"'|escape }}

Test: '"test"'|escape|escape
Expect: &amp;quot;test&amp;quot;
Result: {{ '"test"'|escape|escape }}

Test: '"test"'|escape:'js'|escape
Expect: '\&quot;test\&quot;'
Result: {{ '"test"'|escape:'js'|escape }}

Test: showHtmlNoEscape html='&lt;b&gt;hi&lt;/b&gt;'
Expect: hi
Test: {{ showHtmlNoEscape html='hi' }}
