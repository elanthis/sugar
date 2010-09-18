Sugar API
=========

The Sugar engine is controlled mainly by the Sugar class.  To use
Sugar, first include the Sugar.php file and instantiate an object
of the Sugar class.

::

    require_once 'Sugar.php';

    $sugar = new Sugar();

To declare variables for use by template files, use the set() method.
The first parameter is the variable name (do not include the $) and the
second parameter is the value to assign to the variable.

::

    $sugar->set('life', 42);
    $sugar->set('results', getDatabaseResults());
    $sugar->set('name', $user->name);

The code delimiters ({{ and }}) can be changed by using the
setDelimiters() method.

::

    $sugar->setDelimiters('<!--{', '}-->');

Registering new function requires the `Sugar::addFunction()` method.
The first parameter is the name of the function as used within
templates.  The second optional parameter is the callback to use when
invoking the function; if ommitted, the PHP function of the same name
as the first argument will be invoked.  A third optional parameter
controls whether the function can be cached or not.  The fourth optional
parameter controls whether the function output is escaped by default or
not.

::

    $sugar->addFunction('myFunc');
    $sugar->addFunction('foo', 'some_function');
    $sugar->addFunction('getCost', array($cart, 'get_cost'));
    $sugar->addFunction('dynamic', 'my_dynamic', false);
    $sugar->addFunction('writeHtml', 'html_func', true, false);

Functions receive two arguments: the Sugar object and a keyed array
with the parameters.  Method calls will be called using the native
PHP approach.

::

    function sugar_function_printargs ($sugar, $params) {
      $arg1 = Sugar_Util_GetArg($params, 'arg1', 0);
      $arg2 = Sugar_Util_GetArg($params, 'arg2', 1);

      return "arg1=$arg1, arg2=$arg2";
    }
    $sugar->addFunction('printArgs', 'sugar_function_printargs');
 
It is not always necessary to use `Sugar::addFunction()` to expose a
function to Sugar.  Sugar will automatically look for functions
named sugar_function_foo, where foo is the name of the function
being called, if there is no registered function named foo.

Sugar will also search in the directory `$sugar->pluginDir` for
files named sugar_function_foo.php to attempt to load up unknown
function names.

The `Sugar_Util_GetArg()` function is a utility function to help make
writing Sugar function handlers easier.  The first parameter is the
$params array received by the Sugar function handler, the second
parameter is the name of the parameter (when named parameters are
used), and the third parameter is the default value to return if the
argument was not specified.  This provides behavior equivalent to
PHP 6's ?: short-hand operator.

Function return values will be passed back into the calling
expression.  As with all expressions, the result of a function call
that is to be displayed will be escaped by default.  To negate this
behavior, use the ||raw modifier on the function call.

Exposing objects to Sugar can introduce a potential security hazard
if Sugar templates come from untrusted sources.  By default, any
method on an object can be invoked by the Sugar template.  This
behavior can be overriden by setting `$sugar->methodAcl` to a
callback that controls method access.  The callback is passed
the Sugar object, the target object, the target method name, and
the method parameters.  If the callback returns true, the method
call is allowed; otherwise, the method call is blocked and an error
is raised.

The character set used by the escape routines is ISO-8859-1 (latin1)
by default.  This can be changed by setting the $charset member of
the `$sugar` object.

::

    $sugar->charset = 'UTF-8';

To render a template, use either the `Sugar::display()` or the
`Sugar::displayString()` methods, or the displayCache() method described
below.  The `Sugar::display()` method will look up the file given it
using the storage engine and render the result.  The
`Sugar::displayString()` method takes a string containing the template
source to display.  The default storage engine loads the path
`templates/$file.tpl`, where `$file` is the name passed to
`Sugar::display()`.

::

    $sugar->display('myTemplate'); // loads templates/myTemplate.tpl
    $sugar->displayString('Var = {{ $var }}');

Extra variables can be passed to a template by using the second
parameter to the `Sugar::display()`, `Sugar::displayString()`,
`Sugar::fetch()`, or `Sugar::fetchString()`, or by using the third
parameter to the `Sugar::displayCache()` or `Sugar::fetchCache()`
methods.  This parameter is an associative array of name/value
pairs.

Layout templates can be specified by passing the template name as
the third parameter to the `Sugar::display()`, `Sugar::displayString()`,
`Sugar::fetch()`, or `Sugar::fetchString()`, or by using the fourth
parameter to the `Sugar::displayCache()` or `Sugar::fetchCache()`
methods.

By default, compiled templates and caches are stored in
templates/cache/.

The template directory can be by setting the $templateDir
property of the Sugar object.  The cache directory can be changed
by setting the $cacheDir property.

::

    $sugar->templateDir = '/var/myapp/tpl';
    $sugar->cacheDir = '/var/myapp/ctpl';
    $sugar->debug = true; // force recompilation and disable caching

The plugins directry can be changed by setting the $pluginDir
property.

::

    $sugar->pluginDir = '/var/myapp/plugins';

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

The cache lifetime can be changed by setting the $cacheLimit property
to the number of seconds desired.  The cache lifetime is the number
of seconds a cache will exist before being forced to re-cache.

::

    $sugar->cacheLimit = 60*5; // five minutes

It is possible to check if a valid cache exists for a given template
and cache identifier using the `Sugar::isCached()` method.  This allows
the application to avoid expensive database queries or other
operations when the results are already cached.

::

    if (!$sugar->isCached('life', 42))
      $sugar->set('results', $db->queryData());
    $sugar->displayCache('life', 42);

A template can be removed from the cache by using the `Sugar::uncache()`
method.  The same parameters that are passed to `Sugar::isCached()`
must be passed to `Sugar::uncache()` to remove the specific cache
entry desired.

::

    $sugar->uncache('template');

All cache entries can be cleared using `Sugar::clearCache()`.
