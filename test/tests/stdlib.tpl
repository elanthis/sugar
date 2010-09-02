Test: date 'Y-m-d'
Expect: current date in YYYY-MM-DD format
Result: {{ date format='Y-m-d' }}

Test: date format='Y-m-d' date='next Tuesday'
Expect: date of next Tuesday in YYYY-MM-DD format
Result: {{ date format='Y-m-d' date='next Tuesday' }}

Test: date
Expect: current date in RFC XXXX format
Result: {{ date }}

Test: json [12/4,['one'],2*3]
Expect: [3,['one'],6]
Result: {{ json value=[12/4,['one'],2*3] }}

Test: json 'sally sells sea shells'
Expect: 'sally sells sea shells'
Result: {{ json value='sally sells sea shells' }}

Test: eval source='{{ ldelim }} $x = 4; $x*$x {{ rdelim }}'
Expect: 16
Result: {{ eval source='{{ $x = 4; $x*$x }}' }}

Test: urlencode string='this+is%(illegal)in#url'
Expect: this%2Bis%25%28illegal%29in%23url
Result: {{ urlencode string='this+is%(illegal)in#url' }}

Test: urlencode string=(array a='foo' bar='baz+gar' boo='1%2 3')
Expect: a=foo&amp;bar=baz%2Bgar&amp;boo=1%252+3
Result: {{ urlencode array=(array a='foo' bar='baz+gar' boo='1%2 3') }}
