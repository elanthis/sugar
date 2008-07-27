
		<div id="hide_source">
			<a href="#" onClick="this.parentNode.style.display='none'; document.getElementById('show_source').style.display='block';">[view source]</a>
		</div>
		<div id="show_source" style="display: none;">
			{% echo value=$source %}
			<a href="#" onClick="this.parentNode.style.display='none'; document.getElementById('hide_source').style.display='block';">[hide source]</a>
		</div>
	</body>
</html>
