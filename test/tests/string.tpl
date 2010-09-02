Test: ''
Expect: 
Result: {{ '' }}

Test: '\'\"\\'
Expect: '"\
Result: {{ '\'\"\\' }}

Test: "\'\"\\"
Expect: '"\
Result: {{ "\'\"\\" }}

Test: printf format='%04d' params=[42]
Expect: 0042
Result: {{ printf format='%04d' params=[42] }}
