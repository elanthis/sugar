{{ inherit file='layout.tpl' }}

{{ section name='title' }}Section Tests{{ /section }}

<p>Expect: <br/>
Result: {{ section name='test' }}
<b>FAILURE</b>
{{ /section }}</p>

<p>Expect: TEST2<br/>
Result: {{ section|insert name='test2' }}TEST2{{ /section }}</p>

<p>Expect: TEST3<br/>
Result:	{{ section name='test3' }}TEST3{{ /section }}{{ insert name='test3' }}</p>
