Extending Sugar
===============

Custom Functions
----------------

Registering new function requires the :func:`Sugar::addFunction` method.
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
 
It is not always necessary to use :func:`Sugar::addFunction` to expose a
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

Storage Drivers
---------------

.. warning:: this section out of date

Sugar offers two core means of extending its functionality.  First,
users may register new functions to be used by templates.  Second,
users may over-ride the storage and cache drivers used by the Sugar
engine.

Storage drivers are classes derived from `Sugar_StorageDriver`.  The following
methods must be implemented.  All methods return true on success or
false on error, unless stated otherwise.

+ `Sugar_StorageDriver::stamp(Sugar_Ref $name)`

  Returns the template's timestamp, or false if the specified template
  does not exist.

+ `Sugar_StorageDriver::load(Sugar_Ref $name)`

  Returns the template source.

+ `Sugar_StorageDriver::path(Sugar_Ref $name)`

  Returns a user-friendly name for the template.

The `Sugar_Ref` class describes the requested template name.  It has the
following member variables which are used to distinguish templates:

+ `Sugar_Ref::$full`

  The full path name.

+ `Sugar_Ref::$storageName`

  The name of the storage driver.

+ `Sugar_Ref::$storage`

  The Sugar_StorageDriver object associated with the driver name.

+ `Sugar_Ref::$name`

  The base name of the template.

+ `Sugar_Ref::$cacheId`

  An optional cache identifier (only used for caching).

To register a new storage driver, use the `Sugar::addStorage()` method
of the Sugar object, passing in the desired name and an instance of
the new driver.

    $sugar->addStorage('db', new SugarDatabaseStorage($sugar));

When loading a template, the template name may be prefixed by a
storage driver name.

    $sugar->display('db:homepage');

If not storage driver is specified, the value of the
defaultStorage member variable is used.  By default this is set to
'file' which is the built-in file-based storage driver that comes
with Sugar.  This can be changed.

    $sugar->defaultStorage = 'db';

Cache Drivers
~~~~~~~~~~~~~

.. warning:: this section out of date

Cache drivers are classes derived from `Sugar_CacheDriver`.  The following
methods must be implemented.  All methods return true on success or
false on error, unless stated otherwise.

.. function:: Sugar_CacheDriver::stamp(Sugar_Ref $name, $type)

  Returns the cache timestamp, or false if the specified cache does not exist.

.. function:: Sugar_CacheDriver::load(Sugar_Ref $name, $type)

  Loads the specified cache data.

.. function:: Sugar_CacheDriver::store(Sugar_Ref $name, $type, array $data)

  Stores the specified cache, or throw  a Sugar_Exception on failure.

.. function:: Sugar_CacheDriver::erase(Sugar_Ref $name, $type)

  Erases the specified cache.

.. function:: Sugar_CacheDriver::clear()

  Erases all caches.

The `$type` parameter is a string, which will either be `'ctpl'` for
compiled templates or `'chtml'` or template caches.

To change the cache driver, set the $cache property of the Sugar
object to an instance of the new driver.

::

    $sugar->cache = new SugarCustomCache($sugar);
