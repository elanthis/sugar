Expect: foo
Result: {{ $object->foo }}

Expect: foo
Result: {{ $object.foo }}

Expect: foo
Result: {{ $object['foo'] }}

Expect: [1,2,3]
Result: {{ $object->bar|raw }}

Expect: p1=foo p2= p3=
Result: {{ $object->method('foo') }}

Expect: p1=abc p2=def p3=ghi
Result: {{ $object->method('abc', 'def', 'ghi') }}
