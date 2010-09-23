Template Syntax
===============

The Sugar syntax is designed to be slightly reminiscent of HTML.

Delimiters
----------

All Sugar code is put within the delimiters `{{` and `}}`.  For example::

	{{ if $count > 10 }}
	  <ul>
	  {{ foreach $key,$value in $list }}
	    <li>{{ $key }}: <b>{{ $value }}</b></li>
	  {{ /foreach }}
	  </ul>
	{{ /if }}

Variables
---------

Variables are the dollar sign `$` followed by letters, numbers, and/or
underscores.  Variables can be assigned by the Sugar API or by using
the equal = operator in a template.  For example::

	{{ $myvar = "a test" }}
	My Var is {{ $myvar }}.

The above code will print::

	My Var is a test.

To print the value of a variable, simply place the name of the
variable in script tags.  You can also perform various mathematical
and logical operations on the variable in the tag, which is called
an expression.

::

	{{ $i = 7 }}
	{{ $i }} is 7
	{{ 1+$i }} is 8
	{{ $i*($i%5) }} is 14

	{{ $s = "test" }}
	{{ $s }} is test
	{{ $s+7 }} is test7
	{{ $s+" again" }} is test again

	{{ $test = $s+$i }}
	{{ $test }} is test7

Arrays
~~~~~~

Array keys can be accessed using the . operator or the [] array
subscript operator.

::

	{{ $array.key }}
	{{ $array."the key" }}
	{{ $array.0 }}
	{{ $array.8 }}
	{{ $array.$var }}
	{{ $array[$var] }}
	{{ $array[$var-6] }}
	{{ $array["key"] }}

Arrays can be constructed using the array initializer function
or using the [] initializer syntax.  The array function can
make map arrays (arrays with arbitrary keys) while the [] syntax
can make vectors (arrays with consecutive integer keys).


::

	{{ $vector = [1, 2, 5] }}
	{{ $map = (array first=1 second=2 third=5) }}

Objects
~~~~~~~

Object properties can be accessed using the . or [] operators, just
like arrays.  Object methods, if enabled, can be invoked using the
. operator followed by a method name, then a list of comma-separated
parameters within ().

::

	{{ $obj.name }}
	{{ $obj[$var] }}
	{{ $obj.method() }}
	{{ $obj.foobar(1, $var, "test") }}

HTML Escaping
-------------

The value of a variable is always HTML-escaped by default.  To
display the value of a variable with no escaping, use the raw
modifier.

::

	{{ $html = "<b>bold</b>" }}
	{{ $html }} = &lt;b&gt;bold&lt;/b&gt;
	{{ $html|raw }} = <b>bold</b>

Functions
---------

A function can be called by giving the name of the function, and
then listing the parameters.  Each function parameters is given as
a parameter name, an equals sign, and then the parameter value.


::

	{{ myFunc }}
	{{ myFunc value=$var }}
	{{ myFunc value=$var*2 other="test" last=4+($i*7) }}

Functions may also be called within an expression.  It is recommended
to put the whole function inside of parenthesis, although this is
not required.


::

	{{ "show "+getTime }}
	{{ "show "+getTime 'tomorrow' }}
	{{ 12*1+(foobar test="enable" time="now") }}

Modifiers
---------

Sugar supports a feature called modifiers, which allow for any
expression to be passed through a special function that modifies the
value of the expression.  Modifiers are used by putting a pipe (|)
followed by the modifier name.

::

	{{ 'some text'|upper }} becomes SOME TEXT
	{{ 'Niagara Falls'|lower }} becomes niagara falls

A modifier can be applied to a function by putting the modifier
directly after the function's name.  Putting the modifier after
function parameters will result in the modifier being applied to
the last parameter value, not the function itself!

::

	{{ myFunc|upper value=$var }} modifier applied to result of myFunc
	{{ myFunc value=$var|upper }} modifier applied to $var

Parameters can be passed to a modifier by using a colon (:) followed
by the parameter.  Any number of parameters can be used.

::

	{{ $var|modifier:1:2:'three':4 }}
	{{ myFunc|modifier:'parameter' value=$var }}

Modifiers can be chained together, allowing for multiple modifications
to a single expression.

::

	{{ $var|default:'string'|upper }}

Conditional Blocks
------------------

Conditional execution can be performed using the if, else if, and else
statements.

::

	{{ if $i > 7 }}
	  The value is greater than 7.
	{{ else if $i < -7 }}
	  The value is less than 7.
	{{ else if $i = $v }}
	  The value is equal to $v.
	{{ else }}
	  The value is {{ $i }}
	  {{ if $i < 0 }}
	    which is negative
	  {{ else if $i > 0 }}
	    which is positive
	  {{ else }}
	    which is zero
	  {{ /if }}
	{{ /if }}

Array Iteration
---------------

An array or PHP iterator can be looped over using the foreach
statement.  Either the array values or both the array keys and
values can be iterated over.

::

	Just the values:
	{{ foreach $i in $mylist }}
	  {{ $i }}
	{{ /foreach }}

	Keys and values:
	{{ foreach $k,$i in $mylist }}
	  {{ $k }}={{ $i }}
	{{ /foreach }}

	Inline array:
	{{ foreach $k in [1,2,3] }}
	  {{ $k }}
	{{ /foreach }}

Numeric Iteration
-----------------

A code block can be expected a specific number of times by using the
range loop statement.  The range loop is given a start number, an end
number, and an optional step value.  The follow example displays the
numbers 1 through 5.

::

	{{ loop $i in 1,5 }}
	  {{ $i }}
	{{ /loop }}

This example displays the numbers 6, 4, and 2.

::

	{{ loop $i in 3*2,1,-2 }}
	  {{ $i }}
	{{ /loop }}

Including Templates
-------------------

Other template files can be included using the include function.  Code
can be executed using the eval function.

::

	{{ include file="header.tpl" }}
	{{ include file='some/other/file.tpl' }}
	{{ eval source='var is {{ $var+1 }}' }}
	{{ eval source=getCode }}

Disabling Caching
-----------------

Caching can be suppressed for part of a template by using the nocache
block directive.

::

	{{ nocache }}
	  This value is not cached: {{ $value }}
	{{ /nocache }}

Multiple Statements
-------------------

The semi-colon (;) character can be used to separate statements within
Sugar tags.  The following two blocks of Sugar code function
identically:

::

	{{ if $value }}Value: {{ $value }}{{ /if }}

	{{ if $value ; 'Value: '; $value; /if }}

Sections & Inheritance
----------------------

Sections of content can be defined inside of templates using the
section block directive.  Sections are not immediately displayed in
the output.  A section can be instantiated in the output by using the
insert directive.

::

	{{ section name='title' }}My Title{{ /section }}

	Title is: {{ insert name='title' }}

Templates can also be nested inside of another template, called a
layout template.  This functionality is the primary use of sections.
The layout template can define one or more sections which are overriden
by content in the main template.  Layouts can use the default 'content'
section to insert the body of the main template.

Example page::

	{{ inherit file='layout.tpl' }}

	{{ section name='title' }}Page Title{{ /section }}

	{{ section name='content' }}
		<p>Page content here</p>
	{{ /section }}

Example inherited layout::

	<html>
		<head>
			<title>{{ insert name='title' }}</title>
		</head>
		<body>
			{{ insert name='content' }}
		</body>
	</html>
