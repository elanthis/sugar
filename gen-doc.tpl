<html><head><title>Sugar Reference</title><style type="text/css">
.sugardoc_block {
	border: 1px solid #000;
	margin: 15px;
}
.sugardoc_name {
	border-bottom: 1px solid #000;
	padding: 10px;
	font-size: 140%;
	font-weight: bold;
	background: #ccc;
}
.sugardoc_body {
	padding: 10px;
}
.sugardoc_call {
	border: 1px solid #00c;
	background: #ccf;
	padding: 10px;
	margin: 0 0 10px 0;
}
.sugardoc_call_type {
	font-family: mono;
	font-size: 80%;
}
.sugardoc_call_name {
	font-weight: bold;
}
.sugardoc_call_param {
	color: #009;
}
.sugardoc_call_definitions {
}
.sugardoc_heading {
	font-weight: bold;
}
.sugardoc_params {
	border: 1px solid #00c;
	background: #ccf;
	padding: 10px;
	margin: 0 0 10px 0;
}
.sugardoc_return {
	border: 1px solid #00c;
	background: #ccf;
	padding: 10px;
	margin: 0 0 10px 0;
}
.sugardoc_doc {
	margin: 0 0 10px 0;
}
</style></head><body>

<a name="top"></a><ul>
	<% foreach $block in $blocks %>
	<li><a href="#sugardoc_block_<% $block.name %>"><% $block.name %></a></li>
	<% end %>
</ul>

<% foreach $block in $blocks %>
<div class="sugardoc_block"><a name="sugardoc_block_<% $block.name %>"></a>
	<div class="sugardoc_name"><div style="float:right;font-size:50%;font-weight:normal;"><a href="#top">[top]</a></div><% $block.name %></div>
	<div class="sugardoc_body">
		<div class="sugardoc_heading">Call Prototype:</div>
		<div class="sugardoc_call">
			<span class="sugardoc_call_type"><% $block.return.type %></span>
			<span class="sugardoc_call_name"><% $block.name %></span>
			(
			<% foreach $i,$param in $block.param %>
				<% if $i != 0 ; ',' ; end %>
				<% if $param.optional ; '[' ; end %>
				<span class="sugardoc_call_type"><% $param.type %></span>
				<span class="sugardoc_call_param">$<% $param.name %></span>
				<% if $param.optional ; ']' ; end %>
			<% end %>
			<% if $block.varargs %>
				<% if $i != 0 ; ',' ; end %>
				<span class="sugardoc_call_type"><% $block.varargs %></span>
				...
			<% end %>
			);
		</div>
		<% if $block.param %>
		<div class="sugardoc_heading">Parameters:</div>
		<div class="sugardoc_params">
			<% foreach $param in $block.param %>
				<div class="sugardoc_heading"><% $param.name %></div>
				<div class="sugardoc_doc"><% $param.doc %></div>
			<% end %>
		</div>
		<% end %>
		<% if $block.return %>
		<div class="sugardoc_heading">Return Value:</div>
		<div class="sugardoc_return"><% $block.return.doc %></div>
		<% end %>
		<div class="sugardoc_heading">Description:</div>
		<div class="sugardoc_doc">
			<% foreach $line in $block.doc %>
				<% if substr($line, 0, 2) == '  ' %>
					<div style="white-space: pre; font-family: mono;"><% $line %></div>
				<% else %>
					<div><% $line %>&nbsp;</div>
				<% end %>
			<% end %>
		</div>
	</div>
</div>
<% end %>

</body></html>
