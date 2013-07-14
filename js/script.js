(function($){
	$(document).ready(function(){
		
		// User Input Page
		$(document).ready(function(){
			if($('.user-tracking-input').size()){
				$('.date-measure').datepicker({
					dateFormat: "dd-mm-yy"
				});
			}
			
			$('.changeunits').hide();
			
			$('.weight-unit').on('change', function(){
				if($(this).val() == 1){
					$('.metric').addClass('hidden');
					$('.imperial').removeClass('hidden');
				}else{
					$('.metric').removeClass('hidden');
					$('.imperial').addClass('hidden');
				}
			})
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