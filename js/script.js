(function($){
	$(document).ready(function(){
		
		$('.js-hide').hide();
		
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
				updateWeightVisibilities(this);
			})
			
			updateWeightVisibilities($('.weight-unit'));
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
		
		function updateWeightVisibilities(formElement){
			// possibly change this so it only acts upon the form the dropdown is in.
			if($(formElement).val() == 1){
				$('.metric').addClass('hidden');
				$('.imperial').removeClass('hidden');
			}else{
				$('.metric').removeClass('hidden');
				$('.imperial').addClass('hidden');
			}
		}
	});
})(jQuery);