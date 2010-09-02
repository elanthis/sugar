Expect: true
Result: {{ 1+1=2 }}

Expect: true
Result: {{ 2+5!=6 }}

Expect: true
Result: {{ 1||0 }}

Expect: false
Result: {{ 1&&0 }}

Expect: false
Result: {{ 2>3 }}

Expect: true
Result: {{ 3>1 }}

