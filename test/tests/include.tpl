{{ $top_var = 'baz' }}

Expect: foo
Result: {{ $include_var = 'foo'; include file='include-sub.tpl' }}

Expect: bar
Result: {{ include file='include-sub.tpl' include_var='bar' }}

Expect: baz
Result: {{ $top_var }}
