{% $title = 'stdlib tests' %}
{% include 'header' %}

<p>Test: date 'Y-m-d'<br/>
Expect: <i>current date in YYYY-MM-DD format</i><br/>
Result: {% date 'Y-m-d' %}

<p>Test: date format='Y-m-d' date='next Tuesday'<br/>
Expect: <i>date of next Tuesday in YYYY-MM-DD format</i><br/>
Result: {% date format='Y-m-d', date='next Tuesday' %}

<p>Test: date<br/>
Expect: <i>current date in RFC XXXX format</i><br/>
Result: {% date %}


<p>Test: jsValue array(12/4,array('one'),2*3)<br/>
Expect: [3,['one'],6]<br/>
Result: {% jsValue array(12/4,array('one'),2*3) %}</p>

<p>Test: jsValue 'sally sells sea shells'<br/>
Expect: 'sally sells sea shells'<br/>
Result: {% jsValue 'sally sells sea shells' %}


<p>Test: eval source='&lt;% $x = 4; $x*$x %&gt;'<br/>
Expect: 16<br/>
Result: {% eval source='{% $x = 4; $x*$x %}' %}


<p>Test: urlEncode 'this+is%(illegal)in#url'<br/>
Expect: this%2Bis%25%28illegal%29in%23url<br/>
Result: {% urlEncode 'this+is%(illegal)in#url' %}</p>

<p>Test: urlencode array(a='foo', bar='baz+gar', boo='1%2 3')<br/>
Expect: a=foo&amp;bar=baz%2Bgar&amp;boo=1%252+3<br/>
Result: {% urlencode array(a='foo', bar='baz+gar', boo='1%2 3') %}


<p>Test: escape '&lt;test&gt;' 'html'<br/>
Expect: &lt;test&gt;<br/>
Result: {% escape '<test>', 'html' %}

<p>Test: escape '&lt;test&gt;' 'xml'<br/>
Expect: &lt;test&gt;<br/>
Result: {% escape '<test>', 'xml' %}

<p>Test: escape '&lt;test&gt;' 'url'<br/>
Expect: %3Ctest%3E<br/>
Result: {% escape '<test>', 'url' %}

{% include 'footer' %}
