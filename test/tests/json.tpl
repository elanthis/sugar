Test: json value=$test
Expect: 'dancing mice'
Result: {{ json value=$test }}

Test: json value=$list
Expect: {0:'one',1:'two',2:'three','foo':'bar'}
Result: {{ json value=$list }}

Test: json value=$i
Expect: 10
Result: {{ json value=$i }}

Test: json value=$obj
Expect: {'phpType':'Test','bar':'BAR'}
Result: {{ json value=$obj }}

Test: json value=$newlines
Expect: 'This\nhas\nnewlines!'
Result: {{ json value=$newlines }}
