{{ section title }}stdlib tests{{ /section }}

<p>Test: date 'Y-m-d'<br/>
Expect: <i>current date in YYYY-MM-DD format</i><br/>
Result: {{ date format='Y-m-d' }}

<p>Test: date format='Y-m-d' date='next Tuesday'<br/>
Expect: <i>date of next Tuesday in YYYY-MM-DD format</i><br/>
Result: {{ date format='Y-m-d' date='next Tuesday' }}

<p>Test: date<br/>
Expect: <i>current date in RFC XXXX format</i><br/>
Result: {{ date }}

<p>Test: json [12/4,['one'],2*3]<br/>
Expect: [3,['one'],6]<br/>
Result: {{ json value=[12/4,['one'],2*3] }}</p>

<p>Test: json 'sally sells sea shells'<br/>
Expect: 'sally sells sea shells'<br/>
Result: {{ json value='sally sells sea shells' }}

<p>Test: eval source='{{ ldelim }} $x = 4; $x*$x {{ rdelim }}'<br/>
Expect: 16<br/>
Result: {{ eval source='{{ $x = 4; $x*$x }}' }}

<p>Test: urlencode string='this+is%(illegal)in#url'<br/>
Expect: this%2Bis%25%28illegal%29in%23url<br/>
Result: {{ urlencode string='this+is%(illegal)in#url' }}</p>

<p>Test: urlencode string=(array a='foo' bar='baz+gar' boo='1%2 3')<br/>
Expect: a=foo&amp;bar=baz%2Bgar&amp;boo=1%252+3<br/>
Result: {{ urlencode array=(array a='foo' bar='baz+gar' boo='1%2 3') }}
