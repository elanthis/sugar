{{ section title }}While Tests{{ /section }}

<p>Test: $i = 0; while $i &lt; 10; $i ; $i = $i + 1; /while<br/>
Expect: 0123456789<br/>
Result:
{{ $i = 0; while $i < 10; $i ; $i = $i + 1; /while }}</p>

<p>Test: $i = 1; while $i in [1, 2, 4, 8, 16, 32, 60, 128]; $i ; $i = $i * 2; /while<br/>
Expect: 12481632<br/>
Result: {{ $i = 1; while $i in [1, 2, 4, 8, 16, 32, 60, 128]; $i ; $i = $i * 2; /while }}</p>
