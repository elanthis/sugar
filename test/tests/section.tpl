Expect: 
Result: {{ section name='test' }}
FAILURE
{{ /section }}

Expect: TEST2
Result: {{ section|insert name='test2' }}TEST2{{ /section }}

Expect: TEST3
Result:	{{ section name='test3' }}TEST3{{ /section }}{{ insert name='test3' }}
