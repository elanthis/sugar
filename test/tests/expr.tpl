{{ $a = 4 }}
{{ $b = 15 }}
{{ $c = 7 }}

Expect: 17
Result: {{ 1+2*6+4 }}

Expect: 1
Result: {{ 2*$a+-$c }}

Expect: true
Result: {{ 0||0+1 }}

Expect: 9
Result: {{ 3*(-8+$a/2)/-2 }}
