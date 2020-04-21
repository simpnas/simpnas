<?php 
    include("config.php");
    include("header.php");
    include("side_nav.php");
?>

 <main class="col-md-9 ml-sm-auto col-lg-10 pt-3 px-4">
  <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
    <h1 class="h2">SimpNAS <small>0.01 (2020-04-21)</small></h1>
    <p>A NAS for Grandpa!</p>
</div>

<h5>v0.01 (2020-04-21)</h5>
<ul>
	<li>Updated Apps UI with icons for the Apps</li>
	<li>changed version numbering reset to 0.01 for beta. Stable release will be based off Year Month ex 20.08 similar to ubuntu style</li>
</ul>

<h5>v0.8 (2020-04-20)</h5>
<ul>
	<li>Add DNS Settings for Transmission OpenVPN</li>
	<li>Fixed issue where Transmission would fail to satrt after reboot</li>
	<li>Updated UI in Transmission OpenVPN including hiding VPN Password. added VPN before Username and Passsword </li>
</ul>

<h5>v0.7 (2020-04-19)</h5>
<ul>
	<li>Added Transmission with OpenVPN support (PIA is currently the only VPN provider supported more will come...)</li>
	<li>share references under Volumes now works</li>
	<li>Bunch of other small UI and usability fixes</li>
</ul>

<h5>v0.6 (2020-04-18)</h5>
<ul>
	<li>Added Home Assistant App</li>
</ul>

<h5>v0.5 (2020-04-18)</h5>
<ul>
	<li>Added Unifi Video App</li>
</ul>

<h5>v0.4 (2020-04-18)</h5>
<ul>
	<li>Initial Setup now migrates network to networkd (systemd)</li>
	<li>Apps page update</li>
	<li>Network Add Delete functionality is working now</li>
</ul>

<h5>v0.3 (2020-04-17)</h5>
<ul>
	<li>Added a bunch of fixes to packages</li>
	<li>Fixed Transmission Container</li>
	<li>Fixed issues with changing hostname where samba host would not update</li>
	<li>Properly restart Samba</li>
	<li>Properly update hostname</li>
</ul>

<h5>v0.2 (2020-04-16)</h5>
<ul>
	<li>Implmented new add share method to app installs</li>
	<li>implmented uninstall for jellyfin</li>
</ul>

<h5>v0.1 (2020-04-16)</h5>
<ul>
	<li>Added Version and change log to index.php</li>
</ul>

</main>

<?php include("footer.php"); ?>