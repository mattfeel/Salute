<form method="post" action="/upload/medical_record/<?php echo $patient_id;?>" enctype="multipart/form-data" class="noajax">
<fieldset id="upload-medrec-fd">
<legend>Upload a medical record</legend>
<table>
	<tr>
		<td>Issue: <input type="text" name="issue" /><br /></td>
		<td>Info: <input type="text" name="info" /><br /></td>
	</tr>
	<tr>
		<td colspan="2"><textarea name="description" cols="70" rows="10" wrap="hard">Description...</textarea><br /></td>
	</tr>
	<tr>
		<td colspan="2"><input type="file" name="userfile" size="20" /></td>
	</tr>
</table>

<br /><br />

<input type="submit" value="Upload" />

</fieldset>
</form>
