Test: [1,2,3]
Expect: [1,2,3]
Result: {{ [1,2,3] }}

Test: ['foo','bar','baz']
Expect: ['foo','bar','baz']
Result: {{ ['foo','bar','baz'] }}

Test: explode ' ', 'one two three'
Expect: ['one','two','three']
Result: {{ explode separator=' ' string='one two three' }}

Test: $list
Expect: {0:'one',1:'two',2:'three','foo':'bar'}
Result: {{ $list }}

Test: $list.1
Expect: two
Result: {{ $list.1 }}

Test: $list[1]
Expect: two
Result: {{ $list[1] }}

Test: $list->1
Expect: two
Result: {{ $list->1 }}

Test: $list.notdefined
Expect: 
Result: {{ $list.notdefined }}

Test: $list.foo
Expect: bar
Result: {{ $list.foo }}

Test: $list['foo']
Expect: bar
Result: {{ $list['foo'] }}

Test: $list->foo
Expect: bar
Result: {{ $list->foo }}

Test: "one" in $list
Expect: true
Result: {{ "one" in $list }}

Test: "nope" in $list
Expect: false
Result: {{ "nope" in $list }}

Test: "nope" !in $list
Expect: true
Result: {{ "nope" !in $list }}

Test: $c=2 ; $list.$c
Expect: three
Result: {{ $c=2 ; $list.$c }}

Test: $c=2 ; $list[$c]
Expect: three
Result: {{ $c=2 ; $list[$c] }}
