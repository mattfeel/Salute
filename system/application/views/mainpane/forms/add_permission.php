<form method="post" action="/medical_records/add_permission_do/<?php echo $medrec_id;?>" id="add-permission">
	<fieldset id="add-permission-fieldset">
		<legend>Grant access to this medical record to an HCP</legend>
		<table>
			<tr>
				<td><label for="account_id">HCP id</label></td>
				<td><input type="text" name="account_id" class="input-field" /></td>
			</tr>
		</table>
		
		<p>
			<input type="submit" name="submit" value="Submit" class="submit-button" />
			<input type="reset" />
		</p>
	</fieldset>
</form>
