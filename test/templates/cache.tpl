{% $title = 'Cache Tests' %}
{% include tpl= 'header' %}

<p>Reload the page.  The cache value should not change, but the nocache value should.</p>
<p>This test only works correctly if caching is turned on.</p>

<p>cache: {% random %}</p>

{% nocache %}
<p>nocache: {% random %}</p>
{% end %}

{% include tpl= 'footer' %}
