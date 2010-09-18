Porting from Smarty 2.x
=======================

Introduction
------------

A significant number of early Sugar adopters were refugees from the Smarty
template engine.  While the new Smarty 3.x engine is significantly better than
the old Smarty 2.x engine, there are still a serious of design decisions in
Smarty that lead to problems for many users.  There are also fundamental
security issues in the design, such as the hard requirement of compiling to PHP
scripts which must be loaded from the file systems, making PHP code injection
on a shared host extremely trivial.

While Sugar has a basic API reminiscent of Smarty 2.x, it differs in a number
of ways that a developer attempting to port an application should be aware of.

Syntax Differences
------------------

Delimiters
~~~~~~~~~~

The default code block delimiters in Smarty are a pair of single curly
brackets, { and }.  In Sugar, the delimiters are a pair of double curly
brackets, {{ and }}.  Sugar's behavior makes it easier to mix template code
with inline CSS and inline JavaScript, both of which use the single curly
bracket extensively but for which the double left curly bracket is never a
legal construct.

Output Escaping
~~~~~~~~~~~~~~~

By default, all variable and function output is HTML-escaped by default.  This
is different than Smarty which does no escaping by default.  The Smarty system
makes it too easy to accidentally leave off an escape modifier and allow
HTML/JavaScript injection from user-supplied data, which is why Sugar has the
behavior it does.

When porting an application that expects functions to output raw HTML, the
developer must remember to register the function with output escaping disabled.
The fourth parameter to :func:`Sugar::addFunction` is the escape flag, which is
true if the output should be escaped by Sugar or false if it should not be
escaped.

::

	$sugar->addFunction('foo', 'my_foo', true, false);

The raw modifier can also be applied to any function to override the default
escaping at the template level.

::

	{{ foo|raw }}

The raw modifier is also necessary for displaying a variable which is intended
to contain pre-processed or raw HTML that should not be escaped.

::

	{{ $html_content|raw }}

Iteration
~~~~~~~~~

Sugar does not have Smarty's section syntax.  Sugar sections are part of the
template inheritance and modularization infrastructure.  Porting Smarty code
using a section tag will require switching to the foreach syntax or the loop
syntax.

Sugar's foreach syntax is different than that of Smarty (or PHP).  It uses a
syntax similar to JavaScript.

::

	<!-- smarty code -->
	{foreach item='i' from=$list} ... {/foreach}

	<!-- sugar code -->
	{{ foreach $i in $list }} ... {{ /foreach }}

String Interpolation
~~~~~~~~~~~~~~~~~~~~

Smarty's string interpolation feature is not supported in Sugar.  However,
Sugar has a stirng concatenation operator that works just about anywhere.

::

	<!-- smarty code -->
	{func arg="foo=`$foo`"}

	<!-- sugar code -->
	{{ func arg="foo="..$foo }}

Inline PHP
~~~~~~~~~~

Sugar does not support inline PHP code in any form.  Any custom PHP code that
the developer wishes to use in a template must be exposed through a function,
modifier, or object method.

Core API Differences
--------------------

While it is highly recommended that the regular OOP-style template API be used
in all Sugar code, Sugar also supports a simpler API that is similar to the
Smarty 2.x API.

Setting Variables
~~~~~~~~~~~~~~~~~

Sugar variables can be assigned globally, like how Smarty variables are
assigned.  The :func:`Sugar::set` method works identically to the
:func:`Sugar_Template::set` method, except that variables set with the former
method will be global to all templates.

This is identical to how Smarty works, except that Sugar's method is called set
instead of assign.

::

	// Smarty code
	$smarty->assign('foo', 'bar');

	// Sugar code
	$sugar->set('foo', 'bar');

Displaying & Fetching Templates
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Sugar supports the :func:`Sugar::display` and :func:`Sugar::fetch` methods
which behave nearly identically to the Smarty equivalents.
