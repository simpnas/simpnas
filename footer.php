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
	</body>
</html>