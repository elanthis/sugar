Test: ''
Expect: 
Result: 

Test: '\'\"\\'
Expect: '&quot;\
Result: '&quot;\

Test: "\'\"\\"
Expect: '&quot;\
Result: '&quot;\

Test: printf format='%04d' params=[42]
Expect: 0042
Result: 0042