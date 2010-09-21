Expect: foo
Result: {{ $include_var = 'foo'; include file='include-sub.tpl' }}

Expect: bar
Result: {{ include file='include-sub.tpl' include_var='bar' }}
