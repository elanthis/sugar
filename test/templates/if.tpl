{{ inherit file='layout.tpl' }}

{{ section title }}If Tests{{ /section }}

{{ $test = 24 }}

<p>Expect: A<br/>
Result:	{{ if $test>10 ; if $test>20; 'A'; else; 'B'; /if; else; 'C'; /if }}</p>

<p>Expect: B<br/>
Result: {{ if $test%4=1 ; 'A'; elif $test%4==0; 'B'; else ; 'C' ; /if }}</p>
