<?php /*
	Copyright 2016 Cédric Levieux, Parti Pirate

	This file is part of Personae.

    Personae is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    Personae is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with Personae.  If not, see <http://www.gnu.org/licenses/>.
*/
include_once("header.php");
require_once("engine/utils/SessionUtils.php");

?>
<div class="container theme-showcase" role="main">
	<ol class="breadcrumb">
		<li><a href="index.php"><?php echo lang("breadcrumb_index"); ?> </a></li>
		<li class="active"><?php echo lang("breadcrumb_connect"); ?></li>
	</ol>

	<div class="well well-sm">
		<p>
			<?php echo lang("connect_guide"); ?>
		</p>
	</div>

	<form id="formPanel" class="form-horizontal" action="gamifier_api.php" method="post">
		<fieldset>

			<!-- Text input-->
			<div class="form-group has-feedback">
				<label class="col-md-4 control-label" for="methodInput"><?php echo lang("test_method"); ?></label>
				<div class="col-md-6">
					<input id="methodInput" name="method" value="" type="text"
						placeholder="" class="form-control input-md">
				</div>
			</div>

			<!-- Textarea input-->
			<div class="form-group has-feedback">
				<label class="col-md-4 control-label" for="requestInput"><?php echo lang("test_request"); ?></label>
				<div class="col-md-6">
					<textarea id="requestInput" name="request" rows=10
						placeholder="" class="form-control input-md"></textarea>
				</div>
			</div>

			<!-- Button (Double) -->
			<div class="form-group">
				<div class="col-md-12 text-center">
					<button id="connectButton" name="connectButton" class="btn btn-primary"><?php echo lang("common_connect"); ?></button>
				</div>
			</div>
		</fieldset>
	</form>

</div>

<div class="lastDiv"></div>

<script type="text/javascript">
</script>
<?php include("footer.php");?>
</body>
</html>