			</div>
		</div>
		
		<script src="js/jquery.min.js"></script>
		<script src="js/bootstrap.bundle.min.js"></script>
		<script src="plugins/Inputmask/dist/jquery.inputmask.min.js"></script>
		<script src="plugins/Inputmask/dist/bindings/inputmask.binding.js"></script>
		<script src="plugins/jquery.pwstrength.bootstrap/dist/pwstrength-bootstrap.min.js"></script>
		<script src="plugins/bootstrap-select/js/bootstrap-select.min.js"></script>
		<script src="js/datatables.min.js"></script>
		<script src="js/feather.min.js"></script>
    <script>
      feather.replace()
    </script>
		

		<script>
			$( "form" ).submit(function( event ) {
			  $('#cover-spin').show(0);
			});

		</script>
		
		<script>
		$(document).ready(function() {
		    $('#dt').DataTable();
		} );
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
			$("#alert").fadeTo(6000, 500).slideUp(500, function(){
				
			});

		</script>

		<script>

		$(':password').pwstrength();
		
		jQuery("#checkAll").click(function() {

		  jQuery(':checkbox').each(function() {
		    if(this.checked == true) {
		      this.checked = false;                        
		    } else {
		      this.checked = true;                        
		    }      
		  });

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