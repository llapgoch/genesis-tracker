(function($){
	$(document).ready(function(){
		// User Input Page
		
		$(document).ready(function(){
			$('.date-measure').datepicker({
				dateFormat: "dd-mm-yy"
			});
		});
		
		
		$('#add-progress').on('submit', function(ev){
			ev.preventDefault();
			
			$.ajax(myAjax.ajaxurl, {
				'method':'post',
				'dataType':'xml',
				'complete':function(){
					alert('done');
				},
				'data':{
					'action':'moose',
					'chimp':'bobble'
				}
			});
		});
	});
})(jQuery);