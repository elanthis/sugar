{{ $test = 'dancing mice' }}

Test: one str='test'|upper
Expect: UnoTEST
Result: {{ one str='test'|upper }}

Test: one|upper str='test'
Expect: UNOTEST
Result: {{ one|upper str='test' }}

Test: $test|upper
Expect: DANCING MICE
Result: {{ $test|upper }}

Test: $undefined|default:'Not Defined'
Expect: Not Defined
Result: {{ $undefined|default:'Not Defined' }}

Test: $undefined|default:'Not Defined'|upper
Expect: NOT DEFINED
Result: {{ $undefined|default:'Not Defined'|upper }}

Test: $undefined|default:(one)|upper
Expect: UNO
Result: {{ $undefined|default:(one)|upper }}

Test: $undefined.foo|default:'BaR'|lower
Expect: bar
Result: {{ $undefined.foo|default:'BaR'|lower }}

Test: 'BaR'|default:3|lower
Expect: bar
Result: {{ 'BaR'|default:3|lower }}

Test: '<b>bold</b>'|raw
Expect: <b>bold</b>
Result: {{ '<b>bold</b>'|raw }}
