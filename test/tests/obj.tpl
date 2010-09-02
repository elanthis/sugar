cause exception (will see [[ Exception: fail() called ]] at the top of the page)
{{ $obj->fail() }}

Test: $obj->bar
Expect: BAR
Result: {{ $obj->bar }}

Test: $obj.bar
Expect: BAR
Result: {{ $obj.bar }}

Test: $obj['bar']
Expect: BAR
Result: {{ $obj['bar'] }}

Test: $obj->notdefined
Expect: 
Result: {{ $obj->notdefined }}

Test: $obj->doit(1,2,3)
Expect: [[1,2,3]]
Result: {{ $obj->doit(1,2,3) }}

Test: $obj->foo()
Expect: 3
Result: {{ $obj->foo() }}

Test: 1+$obj->foo()*5
Expect: 16
Result: {{ 1+$obj->foo()*5 }}
