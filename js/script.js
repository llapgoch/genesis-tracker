(function($){	
	$(document).ready(function(){
		$('.js-hide').hide();
		$('.js-show').show();
		
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
			
			// User graph page
			if($('.genesis-progress-graph').size()){
				var userGraph = new UserGraph();
				userGraph.userGraphData = window.userGraphData;
				userGraph.averageUserGraphData = window.averageUserGraphData;

				var switcherCompat = ['weight', 'weight_imperial', 'weight_loss', 'weight_loss_imperial'];
				
				// Add events
				$('.progress-graph-switcher > button').on('click', function(e){
					e.preventDefault();
					var mode = $(this).data('mode');
					
					userGraph.initialise(mode, $('.mode-switcher').val());
					selectModeButton(mode);
					
					$('.mode-switcher').attr('disabled', 'disabled');
					
					if(mode == 'weight' || mode == 'weight_loss'){
						$('.mode-switcher').removeAttr('disabled');
					}
				});
				
				$('.mode-switcher').on('change', function(){
					if(!$.inArray(switcherCompat, userGraph.getMode())){
						return;
					}
					
					userGraph.changeUnits($(this).val());
				});
			}
			
			userGraph.initialise('weight', $('.mode-switcher').val());
			selectModeButton('weight');
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
		
		function selectModeButton(mode){
			$('.progress-graph-switcher > button').removeClass('selected');
			$('.progress-graph-switcher > button[data-mode="' + mode + '"]').addClass('selected');
			console.log('.progress-graph-switcher > button[data-mode="' + mode + '"]');
		}
		
		
		// $('#add-progress').on('submit', function(ev){
// 			ev.preventDefault();
// 			
// 			$.ajax(myAjax.ajaxurl, {
// 				'method':'post',
// 				'dataType':'xml',
// 				'complete':function(){
// 					alert('done');
// 				},
// 				'data':{
// 					'action':'moose',
// 					'chimp':'bobble'
// 				}
// 			});
// 		});
		
	
		
		
	});

})(jQuery);