<?xml version="1.0"?>
<html>
	<head>
		<title>{{ section|insert name='title' }}NO TITLE{{ /section }}</title>
		<style type="text/css">
			tr.rs0 { background: #eee; }
			tr.rs0 td.result { color: #C00; font-weight: bold; }
			div.code {
				white-space: pre;
				background: #eee;
				padding: 4px;
				border: 1px solid #000;
			}
		</style>
	</head>
	<body style="font-family: monospace;">
		<p style="font-size: large; font-weight: bold;">{{ insert name='title' }}</p>

		<div id="content">
			{{ insert name='content' }}
		</div>
	</body>
</html>
