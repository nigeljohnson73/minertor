<?php include_once '_header.php';?>

	<script><?php
$str = "var payload = '".@$_GET["payload"]."';";
$packer = new JavaScriptPacker ( $str );
$str = $packer->pack ();
echo trim($str);
?></script>
<div id="page-loaded" class="container-fluid text-center" data-ng-controller="ValidateCtrl">
	<div data-ng-show="!account_validated && !account_not_validated">
		<h1>Validate your account</h1>
		<p>Coming soon.</p>
		<p>'{{payload}}'</p>
	</div>
	<div data-ng-show="account_validated">
		<div class="alert alert-success" role="alert">
			<p>Success: Coming soon</p>
		</div>
	</div>
	<div data-ng-show="account_not_validated">
		<div class="alert alert-danger" role="alert">
			<p>Failure: coming soon</p>
		</div>
	</div>
</div>

<?php include_once '_footer.php';?>