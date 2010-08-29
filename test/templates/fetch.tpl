{{ inherit 'layout.tpl' }}

{{ section title }}Fetch Tests{{ /section }}

<p>Expect: 1+10=11<br>
Result: {{ $fetch_string }}

<p>Expect: 1+10=11<br>
Result: {{ $fetch_file }}

<p>Expect: 1+10=11<br>
Result: {{ $fetch_cfile }}
