{{ $test = 'dancing mice' }}
{{ $i = 10 }}

Expect: dancing mice
Result: {{ $test }}

Expect: 10
Result: {{ $i }}

Expect: 50
Result: {{ $i*5 }}

Expect: 7
Result: {{ $i=7 ; $i }}

Expect: 35
Result: {{ $i*5 }}
