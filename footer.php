			</div>
		</div>
		
		<script src="plugins/jQuery/jquery.min.js"></script>
		<script src="plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
		<script src="plugins/Inputmask/jquery.inputmask.min.js"></script>
		<script src="plugins/Inputmask/inputmask.binding.js"></script>
		<script src="plugins/bootstrap-show-password/bootstrap-show-password.esm.min.js"></script>
		<script src="plugins/feather-icons/feather.min.js"></script>
    
    <script>
      feather.replace()

			$( "form" ).submit(function( event ) {
			  $('#cover-spin').show(0);
			});

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
			$("#alert").fadeTo(6000, 500).slideUp(500, function(){
				
			});

			jQuery("#checkAll").click(function() {

			  jQuery(':checkbox').each(function() {
			    if(this.checked == true) {
			      this.checked = false;                        
			    } else {
			      this.checked = true;                        
			    }      
			  });

			});

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

			if($("#standAlone").is(":selected")){
					$("#activeDirectorySettings").hide();
				}else{
					$("#activeDirectorySettings").show();
				}
			$(function(){
				$("#serverType").click(function(){
				if($("#activeDirectory").is(":selected")){
					$("#activeDirectorySettings").show();
				}else{
					$("#activeDirectorySettings").hide();
				}
				});
			});

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