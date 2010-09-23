Extending Sugar
===============

Custom Functions
----------------

Creating Custom Functions
~~~~~~~~~~~~~~~~~~~~~~~~~

Sugar template functions are created by defining a class derived from
:class:`Sugar_Function`.  The name of the class must be a specific form,
namely: :class:`Sugar_Function_foo` where `foo` is the name of the function.

If a function is called that does not yet exist, Sugar will look in the
configured plugin directories for a file named :file:`sugar_function_foo.php`.
If the application has a class autoloader defined, it may also load classes
on demand on the behalf of Sugar.

Defining Function Behavior
~~~~~~~~~~~~~~~~~~~~~~~~~~

The :func:`Sugar_Function::invoke()` method must be overridden to implement
the function behavior.

Simple example::

	final class Sugar_Function_foo extends Sugar_Function {
		public function invoke(array $params, Sugar_Context $ctx) {
			return 'foo result';
		}
	}

The :func:`Sugar_Function::invoke()` method receives two arguments.  The first
is the array of parameters the function was called with.  The second is a
context object which provides access to the currently executing Template,
variables, and the runtime object.

The :func:`Sugar_Function::invoke()` return value is the return value of the
function itself.

Result Escaping
~~~~~~~~~~~~~~~

When a function is called directly in a template statement, such as::

	{{ foo }}

The result is printed out as a string and HTML-escaped by default.  To
suppress the HTML escaping, the function's escape flag must be disabled.
This can be done by calling the :func:`Sugar_Function::setEscape()`,
usually inside the function class's constructor.

::

	class Sugar_Function_foo extends Sugar_Function {
		public function __construct() {
			$this->setEscape(false);
		}
	}

Function Cacheability
~~~~~~~~~~~~~~~~~~~~~

Functions are normally run and their output cached as with any other
variable or expression.  To avoid caching the result of a function, the
function call must normally be wrapped with `nocache` tags::

	{{ nocache }}
		{{ foo }}
	{{ /nocache }}

It is also possible to make a function uncacheable by default.  This
feature only works for functions call as a statement, not inside a
more complicated expression.

To set the cacheability, use the :func:`Sugar_Function::setCacheable()`
method.

::

	class Sugar_Function_foo extends Sugar_Function {
		public function __construct() {
			$this->setCacheable(false);
		}
	}

Custom Modifiers
----------------

Creating Custom Modifiers
~~~~~~~~~~~~~~~~~~~~~~~~~

Sugar template modifiers are created by defining a class derived from
:class:`Sugar_Modifier`.  The name of the class must be a specific form,
namely: :class:`Sugar_Modifier_foo` where `foo` is the name of the modifier.

If a modifier is called that does not yet exist, Sugar will look in the
configured plugin directories for a file named :file:`sugar_modifier_foo.php`.
If the application has a class autoloader defined, it may also load classes
on demand on the behalf of Sugar.

Defining Function Behavior
~~~~~~~~~~~~~~~~~~~~~~~~~~

The :func:`Sugar_Modifier::invoke()` method must be overridden to implement
the modifier behavior.

Simple example::

	final class Sugar_Modifier_foo extends Sugar_Modifier {
		public modifier invoke($value, array $params, Sugar_Context $ctx) {
			return $value;
		}
	}

The :func:`Sugar_Modifier::invoke()` method receives three arguments.  The
first is the value that the modifier is being applied to.  The second is an
array of zero or more additional parameters to the modifier.  The third is a
context object which provides access to the currently executing Template,
variables, and the runtime object.

The :func:`Sugar_Modifier::invoke()` return value is the return value of the
modifier itself.

Result Escaping
~~~~~~~~~~~~~~~

When a modifier is the last one used in an expression, the result will be
printed and HTML-escaped by default.

	{{ $some_value|foo }}

To suppress the HTML escaping, the modifier's escape flag must be disabled.
This can be done by calling the :func:`Sugar_Modifier::setEscape()`, usually
inside the modifier class's constructor.

::

	class Sugar_Modifier_foo extends Sugar_Modifier {
		public modifier __construct() {
			$this->setEscape(false);
		}
	}

Sugar Context Object
--------------------

.. note:: FIXME describe Sugar_Context

Storage Drivers
---------------

.. warning:: this section out of date

Sugar offers two core means of extending its functionality.  First,
users may register new functions to be used by templates.  Second,
users may over-ride the storage and cache drivers used by the Sugar
engine.

When loading a template, the template name may be prefixed by a
storage driver name.

    $sugar->display('db:homepage');

If not storage driver is specified, the value of the
defaultStorage member variable is used.  By default this is set to
'file' which is the built-in file-based storage driver that comes
with Sugar.  This can be changed.

    $sugar->defaultStorage = 'db';

Cache Drivers
-------------

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

Object Security ACL
-------------------

Exposing objects to Sugar can introduce a potential security hazard
if Sugar templates come from untrusted sources.  By default, any
method on an object can be invoked by the Sugar template.  This
behavior can be overriden by setting `$sugar->methodAcl` to a
callback that controls method access.  The callback is passed
the Sugar object, the target object, the target method name, and
the method parameters.  If the callback returns true, the method
call is allowed; otherwise, the method call is blocked and an error
is raised.
