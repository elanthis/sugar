{{ inherit file='layout.tpl' }}

{{ section name='title' }}Method ACL Tests{{ /section }}

<p>Test: $obj-&gt;deny_acl()<br/>
Expect: <br/>
Result: {{ $obj->deny_acl() }}</p>
