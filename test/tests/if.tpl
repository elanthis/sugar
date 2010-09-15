{{ $test = 24 }}

Expect: A
Result:	{{ if $test>10 ; if $test>20; 'A'; else; 'B'; /if; else; 'C'; /if }}

Expect: B
Result: {{ if $test%4=1 ; 'A'; elif $test%4==0; 'B'; else ; 'C' ; /if }}

Expect: SUCCESS
Result: {{ if $test == 12 }}FAIL{{ else if $test == 24 }}SUCCESS{{ /if }}
