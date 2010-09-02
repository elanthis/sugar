{{ inherit file='layout.tpl' }}

{{ section name='title' }}Range Loop Tests{{ /section }}

<p>Test: loop $i in 1,3 ; $i ; /loop<br/>
Expect: 123<br/>
Result: {{ loop $i in 1,3 ; $i ; /loop }}</p>

<p>Test: loop $i in 3,1,-1 ; $i ; /loop<br/>
Expect: 321<br/>
Result: {{ loop $i in 3,1,-1 ; $i ; /loop }}</p>

<p>Test: loop $i in 3,4*6,7 ; $i ; /loop<br/>
Expect: 3101724<br/>
Result: {{ loop $i in 3,4*6,7 ; $i ; /loop }}</p>

<p>Test: loop $i in 2,1 ; $i ; /loop <br/>
Expect: <br/>
Result: {{ loop $i in 2,1 ; $i ; /loop }}</p>
