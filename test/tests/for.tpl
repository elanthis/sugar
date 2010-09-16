{{ $list = ['one','two','three','bar'] }}

Test: for $i in $list; $i..','; /for
Expect: one,two,three,bar,
Result: {{ for $i in $list; $i..','; /for }}

Test: for $k,$i in $list; $k .. '=' .. $i .. ','; /for
Expect: 0=one,1=two,2=three,3=bar,
Result: {{ for $k,$i in $list; $k .. '=' .. $i .. ','; /for }}

Test: for $i in [1, 'one', 'bar', 42]; $i..','; /for
Expect: 1,one,bar,42,
Result: {{ for $i in [1, 'one', 'bar', 42]; $i..','; /for }}
