{{ inherit file='layout.tpl' }}

{{ section name='title' }}Sugar Template Test Cases{{ /section }}

{{ section name='content' }}
	<table>
		<thead>
			<tr>
				<th>Test</th><th>Result</th>
			</tr>
		</thead>
		<tbody>
{{ foreach $test in $tests }}
			<tr class="rs{{ if $test.status }}1{{ else }}0{{ /if }}"><td><a href="javascript:showTest('{{ $test.test|escape}}');">{{ $test.test }}</a></td><td class="result">{{ if $test.status }}ok{{ else }}FAIL{{ /if }}</td></tr>
{{ /foreach }}
		</tbody>
	</table>

	{{ foreach $test in $tests }}
	<div id="test_output_{{ $test.test }}" class="code" style="display:none">{{ $test.output }}</div>
	{{ /foreach }}

	<script type="text/javascript">
	var curTest = '';
	function showTest(test) {
		if (curTest) {
			document.getElementById('test_output_'+curTest).style.display='none';
		}
		document.getElementById('test_output_'+test).style.display='block';
		curTest = test;
	}
	</script>
{{ /section }}
