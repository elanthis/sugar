<?xml version="1.0"?>
<html>
	<head>
		<title>{{ section|insert name='title' }}NO TITLE{{ /section }}</title>
	</head>
	<body style="font-family: monospace;">
		<p style="font-size: large; font-weight: bold;">{{ insert name='title' }}</p>
		{{ if $t != 'index' }}<p><a href="index.php">[index]</a>{{ /if }}

		<div id="content">
			{{ insert name='content' }}
		</div>

		<div id="hide_source">
			<a href="#" onClick="this.parentNode.style.display='none'; document.getElementById('show_source').style.display='block';">[view source]</a>
		</div>
		<div id="show_source" style="display: none;">
			<a href="#" onClick="this.parentNode.style.display='none'; document.getElementById('hide_source').style.display='block';">[hide source]</a>
			<div style="border: 1px solid #000; padding: 4px; background: #eee;">
				<b>Source</b><br/>
				<pre>{{ $source }}</pre>
			</div>
		</div>
	</body>
</html>
