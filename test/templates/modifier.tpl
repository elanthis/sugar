{{ section title }}Modifier Tests{{ /section }}

<p>Test: one str='test'|upper<br/>
Expect: UnoTEST<br/>
Result: {{ one str='test'|upper }}</p>

<p>Test: one|upper str='test'<br/>
Expect: UNOTEST<br/>
Result: {{ one|upper str='test' }}</p>

<p>Test: $test|upper<br/>
Expect: DANCING MICE<br/>
Result: {{ $test|upper }}</p>

<p>Test: $undefined|default:'Not Defined'<br/>
Expect: Not Defined<br/>
Result: {{ $undefined|default:'Not Defined' }}</p>

<p>Test: $undefined|default:'Not Defined'|upper<br/>
Expect: NOT DEFINED<br/>
Result: {{ $undefined|default:'Not Defined'|upper }}</p>

<p>Test: $undefined|default:(one)|upper<br/>
Expect: UNO<br/>
Result: {{ $undefined|default:(one)|upper }}</p>

<p>Test: '&lt;b&gt;bold&lt;/b&gt;'|raw<br/>
Expect: <b>bold</b><br/>
Result: {{ '<b>bold</b>'|raw }}</p>
