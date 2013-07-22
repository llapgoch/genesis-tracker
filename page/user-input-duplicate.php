<form name="user-inputs" method="post" action="">
	<div class="alert notice">
		<div class="msg">
			<h2>You have previously recorded measurements for the date you've chosen</h2>
			<p>Would you like to overwrite your previously entered data?</p>

			<button type="submit" name="action" value="duplicate-change" class="button large blue">No, let me change the date</button>
			<button type="submit" name="action" value="duplicate-overwrite" class="button large green">Yes, overwrite my measurements</button>
		</div>
	</div>

	<?php
	echo $form->valuesAsHiddenInputs(
		array(
			'action'
		)
	);
	?>
</form>