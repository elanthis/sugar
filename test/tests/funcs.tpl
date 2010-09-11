Test: one str='test'
Expect: Unotest
Result: {{ one str='test' }}

Test: array|raw foo='a string' bar=['an', 'array'] number=42
Expect: {"foo":"a string","bar":["an","array"],"number":42}
Result: {{ array|raw foo='a string' bar=['an', 'array'] number=42 }}
