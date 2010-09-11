Test: json value='dancing mice'
Expect: "dancing mice"
Result: {{ json|raw value='dancing mice' }}

Test: json value=['one','two','three','bar']
Expect: ["one","two","three","bar"]
Result: {{ json|raw value=['one','two','three','bar'] }}

Test: json value=10
Expect: 10
Result: {{ json value=10 }}

Test: json value="This\nhas\nnewlines!"
Expect: "This\nhas\nnewlines!"
Result: {{ json|raw value="This\nhas\nnewlines!" }}
