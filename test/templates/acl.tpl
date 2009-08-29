{% $title = 'Method ACL Tests' %}
{% include tpl= 'header' %}

<p>Test: $obj-&gt;deny_acl()<br/>
Expect: <br/>
Result: {% $obj->deny_acl() %}</p>

{% include tpl= 'footer' %}
