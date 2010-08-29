{{ inherit 'layout.tpl' }}

{{ section title }}json Tests{{ /section }}

<p>Test: json value=$test<br>
Expect: 'dancing mice'<br>
Result: {{ json value=$test }}

<p>Test: json value=$list<br>
Expect: {0:'one',1:'two',2:'three','foo':'bar'}<br>
Result: {{ json value=$list }}

<p>Test: json value=$i<br>
Expect: 10<br>
Result: {{ json value=$i }}

<p>Test: json value=$obj<br>
Expect: {'phpType':'Test','bar':'BAR'}<br>
Result: {{ json value=$obj }}

<p>Test: json value=$newlines<br>
Expect: 'This\nhas\nnewlines!'<br>
Result: {{ json value=$newlines }}
