Sugar API
=========

Basic Usage
-----------

Including
~~~~~~~~~

If Sugar has been installed with PEAR, then including Sugar is as
easy as including :file:`Sugar.php`.

::

	require_once 'Sugar.php';

Initializing
~~~~~~~~~~~~

The Sugar engine is controlled mainly by the Sugar class.  To use
Sugar, first include the :file:`Sugar.php` file and instantiate an object of
the Sugar class.

::

	$sugar = new Sugar();

Display a Template
------------------

Loading Templates
~~~~~~~~~~~~~~~~~

To load a template, use the :func:`Sugar::getTemplate` method.

::

	$tpl = $sugar->getTemplate('mytemplate.tpl');

Setting Template Variables
~~~~~~~~~~~~~~~~~~~~~~~~~~

Any PHP value may be used as a Sugar variable inside of a template.

To set a variable, use the :func:`Sugar_Template::set` method.

::

	$tpl->set('life', 42);
	$tpl->set('results', getDatabaseResults());
	$tpl->set('name', $user->name);

Displaying
~~~~~~~~~~

To display a template's output directly to the browser, use the
:func:`Sugar_Template::display` method.

::

	$tpl->display();

Fetching
~~~~~~~~

To get the output of a template into a string, use the
:func:`Sugar_Template::fetch` method.

::

	$output = $tpl->fetch();

Configuration
-------------

Code Delimiters
~~~~~~~~~~~~~~~

The code delimiters ({{ and }}) can be changed by using the
:func:`Sugar::setDelimiters` method.

::

	$sugar->setDelimiters('<!--{', '}-->');

Character Encoding
~~~~~~~~~~~~~~~~~~

The character set used by the escape routines is ISO-8859-1 (latin1)
by default.  This can be changed by setting the $charset member of
the `$sugar` object.

::

	$sugar->charset = 'UTF-8';

Paths & Directories
~~~~~~~~~~~~~~~~~~~

By default, compiled templates and caches are stored in
templates/cache/.

The template directory can be by setting the $templateDir
property of the Sugar object.  The cache directory can be changed
by setting the $cacheDir property.

::

	$sugar->templateDir = '/var/myapp/tpl';
	$sugar->cacheDir = '/var/myapp/ctpl';

The plugins directry can be changed by setting the $pluginDir
property.

::

	$sugar->pluginDir = '/var/myapp/plugins';

Output Caching
~~~~~~~~~~~~~~

The cache lifetime can be changed by setting the $cacheLimit property
to the number of seconds desired.  The cache lifetime is the number
of seconds a cache will exist before being forced to re-cache.

::

	$sugar->cacheLimit = 60*5; // five minutes

Debugging
~~~~~~~~~

When debugging is enabled, templates will always be recompiled and
output caches will be ignored.

::

	$sugar->debug = true; // force recompilation and disable caching

Output Caching
--------------

Enabling Caching
~~~~~~~~~~~~~~~~

.. warning:: this section out of date

Caching can be performed on a template by using the
`Sugar::displayCache()` method.  This method takes a second optional
parameter, which is a cache identifier, which is used to differentiate
between multiple instances of the same template.  For example, a
product template in an eCommerce application would use a different
cache identifier for each product.  The second parameter can be
ommitted if desired.

::

	$sugar->displayCache('homepage');
	$sugar->displayCache('product', $product->id);

Cache Querying
~~~~~~~~~~~~~~

It is possible to check if a valid cache exists for a given template
and cache identifier using the `Sugar::isCached()` method.  This allows
the application to avoid expensive database queries or other
operations when the results are already cached.

::

	if (!$sugar->isCached('life', 42))
	  $sugar->set('results', $db->queryData());
	$sugar->displayCache('life', 42);

Cache Clearing
~~~~~~~~~~~~~~

A template can be removed from the cache by using the `Sugar::uncache()`
method.  The same parameters that are passed to `Sugar::isCached()`
must be passed to `Sugar::uncache()` to remove the specific cache
entry desired.

::

	$sugar->uncache('template');

All cache entries can be cleared using `Sugar::clearCache()`.
