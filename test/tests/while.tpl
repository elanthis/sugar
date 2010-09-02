Test: $i = 0; while $i < 10; $i ; $i = $i + 1; /while
Expect: 0123456789
Result:
{{ $i = 0; while $i < 10; $i ; $i = $i + 1; /while }}

Test: $i = 1; while $i in [1, 2, 4, 8, 16, 32, 60, 128]; $i ; $i = $i * 2; /while
Expect: 12481632
Result: {{ $i = 1; while $i in [1, 2, 4, 8, 16, 32, 60, 128]; $i ; $i = $i * 2; /while }}
