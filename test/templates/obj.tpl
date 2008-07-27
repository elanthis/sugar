{% $title = 'Object Tests' %}
{% include 'header' %}

<p>cause exception (will see <i>[[ Exception: fail() called ]]</i> at the top of the page)</p>
{% $obj->fail() %}

<p>Test: $obj->bar<br/>
Expect: BAR<br/>
Result: {% $obj->bar %}</p>

<p>Test: $obj.bar<br/>
Expect: BAR<br/>
Result: {% $obj.bar %}</p>

<p>Test: $obj['bar']<br/>
Expect: BAR<br/>
Result: {% $obj['bar'] %}</p>

<p>Test: $obj->notdefined<br/>
Expect: <br/>
Result: {% $obj->notdefined %}</p>

<p>Test: $obj->doit(1,2,3)<br/>
Expect: [[1,2,3]]<br/>
Result: {% $obj->doit(1,2,3) %}</p>

<p>Test: $obj->foo()<br/>
Expect: 3<br/>
Result: {% $obj->foo() %}</p>

<p>Test: 1+$obj->foo()*5<br/>
Expect: 16<br/>
Result: {% 1+$obj->foo()*5 %}</p>

{% include 'footer' %}
