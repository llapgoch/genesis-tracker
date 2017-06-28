;(function($){
	$(document).ready(function(){
		$('.datepicker').datepicker({
			dateFormat: "dd-mm-yy"
        });

		$('#your-profile').on('submit', function(ev){
			if(parseInt($('[name="account_active"]').val()) && !parseInt($('[name="start_date"]').val())){
				ev.preventDefault();
				alert('You must specify a start date when a user is activated');
			}
		});
	});
}(jQuery));