(function($){
	$(document).ready(function(){
		
		// User Input Page
		$(document).ready(function(){
			if($('.user-tracking-input').size()){
				$('.date-input').datepicker({
					dateFormat: "dd-mm-yy"
				});
			}
			
			$('.changeunits').hide();
			
			// So the error gets turned off when we switch
			$('.error-weight_pounds').addClass('imperial');
			
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