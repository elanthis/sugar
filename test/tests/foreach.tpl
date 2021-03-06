{{ $list = ['one','two','three','bar'] }}

Test: foreach $i in $list; $i..','; /foreach
Expect: one,two,three,bar,
Result: {{ foreach $i in $list; $i..','; /foreach }}

Test: foreach $k,$i in $list; $k .. '=' .. $i .. ','; /foreach
Expect: 0=one,1=two,2=three,3=bar,
Result: {{ foreach $k,$i in $list; $k .. '=' .. $i .. ','; /foreach }}

Test: foreach $i in [1, 'one', 'bar', 42]; $i..','; /foreach
Expect: 1,one,bar,42,
Result: {{ foreach $i in [1, 'one', 'bar', 42]; $i..','; /foreach }}
