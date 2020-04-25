			</div>
		</div>

		<script src="js/jquery.min.js"></script>
		<script src="dist/js/bootstrap.min.js"></script>
		<script src="https://unpkg.com/feather-icons/dist/feather.min.js"></script>
		<script>
			feather.replace()
		</script>

		<script>
			$(function(){
				// Highlight the active nav link.
				var url = window.location.pathname;
				var filename = url.substr(url.lastIndexOf('/') + 1);
				$('.nav-item a[href$="' + filename + '"]').addClass("active");
			});

			//Prevents resubmit on forms
			if(window.history.replaceState){
			  window.history.replaceState(null, null, window.location.href);
			}

			//Slide alert up after 2 secs
			$("#alert").fadeTo(2000, 500).slideUp(500, function(){
				
			});

		</script>

		<script type="text/javascript">
			$("#passwordbox").hide();
			$(function(){
				$("#encrypt").click(function(){
				if($(this).is(":checked")){
					$("#passwordbox").show();
				}else{
					$("#passwordbox").hide();
				}
				});
			});
		</script>

		<script type="text/javascript">
			if($("#static").is(":selected")){
					$("#staticSettings").show();
				}else{
					$("#staticSettings").hide();
				}
			$(function(){
				$("#method").click(function(){
				if($("#static").is(":selected")){
					$("#staticSettings").show();
				}else{
					$("#staticSettings").hide();
				}
				});
			});
		</script>

		<script type="text/javascript">
			$("#vpnSettings").hide();
			$(function(){
				$("#configVpn").click(function(){
				if($(this).is(":checked")){
					$("#vpnSettings").show();
				}else{
					$("#vpnSettings").hide();
				}
				});
			});
		</script>

	</body>
</html>