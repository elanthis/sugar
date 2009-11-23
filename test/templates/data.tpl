{% section title %}Plain Data Tests{% /section %}

<p>Expect: &lt;foo&gt;<br/>
Result: {% '<foo>' %}</p>

<p>Expect: checked="checked"<br/>
Result: {% ' checked="checked" ' %}</p>

<p>Expect: checked="checked"<br/>
Result: {% checked test=1 %}</p>
