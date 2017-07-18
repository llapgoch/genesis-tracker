<div class="wrap">
    <h2>2 Day Wythenshawe Admin</h2>
    <ul>
        <li>Red dates in the <em>Last Measurement Date</em> column haven't logged any values for two weeks or more</li>
        <li>Red in the <em>Active</em> column shows a user's account has not yet been activated</li>
        <li>Red in the <em>Weight Change</em> column shows whether a user hasn't lost, or has gained weight</li>
    </ul>
    
	<?php

	$tbl->prepare_items();
	$tbl->display();
	?>
</div>