{% if !$light %}
<html><head><title>Sugar Reference</title>
<link rel="stylesheet" type="text/css" href="sugardoc.css" />
</head><body>
{% end %}

<table width="100%"><tr><td valign="top">

	<a name="top"></a>
	<ul>
		{% foreach $block in $blocks %}
		<li><a href="#sugardoc_block_{% $block.name %}">{% $block.name %}</a></li>
		{% end %}
	</ul>

</td><td>

	{% foreach $block in $blocks %}
	<div class="sugardoc_block"><a name="sugardoc_block_{% $block.name %}"></a>
		<div class="sugardoc_name"><div style="float:right;font-size:50%;font-weight:normal;"><a href="#top">[top]</a></div>{% $block.name %}</div>
		<div class="sugardoc_body">
			{% if $block.alias %}
				<div class="sugardoc_heading">Also Known As:</div>
				<div class="sugardoc_alias">
					{% join separator=', ' array=$block.alias %}
				</div>
			{% end %}
			<div class="sugardoc_heading">Call Prototype:</div>
			<div class="sugardoc_call">
				{% foreach $name in merge one=[$block.name] two=$block.alias %}
					<span class="sugardoc_call_name">{% $name %}</span>
					{% foreach $param in $block.param %}
						{% if $param.optional ; '[' ; end %}
						<span class="sugardoc_call_param">{% $param.name %}</span>=<span class="sugardoc_call_type">{% $param.type %}</span>
						{% if $param.optional ; ']' ; end %}
					{% end %}
					{% if $block.varargs %}
						[ <span class="sugardoc_call_type">{% $block.varargs %}</span> ... ]
					{% end %}
					<br />
				{% end %}
			</div>
			{% if $block.param %}
			<div class="sugardoc_heading">Parameters:</div>
			<div class="sugardoc_params">
				{% foreach $param in $block.param %}
					<div class="sugardoc_heading">{% $param.name %}</div>
					<div class="sugardoc_doc">{% $param.doc %}</div>
				{% end %}
			</div>
			{% end %}
			{% if $block.return %}
			<div class="sugardoc_heading">Return Value:</div>
			<div class="sugardoc_return">
				return &rarr; <span class="sugardoc_call_type">{% $block.return.type %}</span><br /><br />
				{% $block.return.doc %}
			</div>
			{% end %}
			<div class="sugardoc_heading">Description:</div>
			<div class="sugardoc_doc">
				{%
				// mode: r for regular text, c for code blocks
				$lmode = 'r';
				// set to true after encountering an empty line
				$empty = false;
				// iterator over each line
				foreach $line in $block.doc;
					// if we have an empty line, remember that,
					// but don't display anything just yet
					if $line == '';
						$empty = true;
					// this line is part of a code block
					elif (substr string=$line length=2) == '  ';
						// if we're not currently in code block mode, switch
						if $lmode != 'c';
							$lmode = 'c';
							// clear any blank lines
							$empty = false;
							// start dic
							echo value='<div class="sugardoc_code">';
						// already in code mode
						else;
							// handle empty line
							if $empty;
								$empty = false;
								echo value='<br/>';
							end;
						end;
						// display line
						$line; echo value='<br />';
					// regular line
					else;
						// if we're in code block mode, end it
						if $lmode == 'c';
							$lmode = 'r';
							// clear empty line flag
							$empty = false;
							// end the code block
							echo value='</div>';
						// not in code block mode
						else;
							// handle empty line
							if $empty;
								$empty = false;
								echo value='<br/><br/>';
							end;
						end;
						// display the line
						$line;
						// put a space between end of this line and beginning of next
						' ';
					end;
				end;
				// terminate code block if we're in it
				if $lmode == 'c';
					echo value='</div>';
				end;
				%}
			</div>
		</div>
	</div>
	{% end %}

</td></tr></table>

{% if !$light %}
</body></html>
{% end %}
{% // vim: set ts=4 sw=4 : %}
