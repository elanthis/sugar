{{ $test = 24 }}

Expect: SUCCESS
Result: {{ if $test == 12 }}FAIL{{ else if $test == 24 }}SUCCESS{{ /if }}

Expect: SUCCESS
Result: {{ if $test == 12 }}FAIL{{ else if $test == 14 }}FAIL{{ else if $test == 24 }}SUCCESS{{ /if }}

Expect: SUCCESS
Result:	{{ if $test>10 ; if $test>20; 'SUCCESS'; else; 'FAIL'; /if; else; 'FAIL'; /if }}

Expect: SUCCESS
Result: {{ if $test%4=1 ; 'FAIL'; elif $test%4==0; 'SUCCESS'; else ; 'FAIL' ; /if }}
