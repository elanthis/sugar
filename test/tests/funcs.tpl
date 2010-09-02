Test: showHtml html='&lt;b&gt;bold&lt;/b&gt;'
Expect: &lt;b&gt;bold&lt;b&gt;
Result: {{ showHtml html='bold' }}

Test: showHtml|raw html='&lt;b&gt;bold&lt;/b&gt;'
Expect: bold
Result: {{ showHtml|raw html='bold' }}

Test: one str='test'
Expect: Unotest
Result: {{ one str='test' }}

Test: showText text=1
Expect: 1
Result: {{ showText text=1 }}

Test: showText text=one
Expect: Uno
Result: {{ showText text=one }}

Test: array foo='a string' bar=['an', 'array'] number=42
Expect: {'foo':'a string','bar':['an','array'],'number':42}
Result: {{ array foo='a string' bar=['an', 'array'] number=42 }}
