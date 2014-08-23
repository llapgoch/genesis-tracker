(function($){	
	$(document).ready(function(){
		$('.js-hide').hide();
		$('.js-show').show();
		
		// User Input Page
		$(document).ready(function(){
			if($('.user-tracking-input').size()){
                var setup = {
					dateFormat: "dd-mm-yy",
					maxDate:0    
                };
                
                if(window.datePickerMin){
                     setup.minDate = new Date(parseInt(datePickerMin.year, 10), parseInt(datePickerMin.month, 10) - 1, parseInt(datePickerMin.day, 10));
                }
                
				$('.date-input').datepicker(setup);
				
				$('.date-input').on('change', function(){
					updateDateList($(this));
				});
			}
			
			$('.changeunits').hide();
			
			// So the error gets turned off when we switch
			$('.error-weight_pounds').addClass('imperial');
			
			$('.weight-unit').on('change', function(){
				updateWeightVisibilities(this);
			})
            
            if(window.initialUserUnit){
                // Set any selects to show the correct units the user originally selected
                $('.weight-unit').val(window.initialUserUnit);
            }
			
			updateWeightVisibilities($('.weight-unit'));
			
			$('.question-chooser').each(function(){
				if($(this).is(':checked')){
					$(this).parent().find('.inner-question-container').show();
				}
			});
			
			$('.question-chooser').on('click', function(e){
				if($(this).is(':checked')){
					$(this).parent().find('.inner-question-container').show();
				}else{
					$(this).parent().find('.inner-question-container').hide();
				}
			});
			
			// User graph page
			if($('.genesis-progress-graph').size() > 0){
				var userGraph = window.userGraph = new UserGraph();
				userGraph.userGraphData = window.userGraphData;
				userGraph.averageUserGraphData = window.averageUserGraphData;

				var switcherCompat = ['weight', 'weight_imperial', 'weight_loss', 'weight_loss_imperial'];
				
				// Add events
				$('.progress-graph-switcher > button').on('click', function(e){
					e.preventDefault();
					var mode = $(this).data('mode');
					
					userGraph.initialise(mode, $('.mode-switcher').val() == 1 ? "imperial" : "");
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
				
				$('.zoomer .in').on('click', function(e){
					userGraph.zoomIn();
				});
				
				$('.zoomer .out').on('click', function(e){
					userGraph.zoomOut();
				});
				
				userGraph.initialise('weight', $('.mode-switcher').val() == 1 ? "imperial" : "");
				selectModeButton('weight');
			}
			
			
		});
		
		function showPreloader(){
			removePreloader();
			$(document.body).append('<div class="preload-cover"><div class="preload-bg"></div><div class="loader"></div></div>')
		}
		
		function removePreloader(){
			$('.preload-cover').remove();
		}
		
		function updateDateList(pickField){
			var selDate = pickField.datepicker('getDate');
			showPreloader();
			
			$.ajax(myAjax.ajaxurl, {
				'type':'post',
				'dataType':'html',
				'complete':function(){
					removePreloader();
				},
				'success':function(data){
					$('.diet-days').html(data);
				},
				'data':{
					'action':'genesis_getdatepicker',
					'day':selDate.getDate(),
					'month':selDate.getMonth(),
					'year':selDate.getFullYear()
				}
			});
		}
		
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
		}
		
		
		window.ajaxTest = function(){
			
			
			$.ajax(myAjax.ajaxurl, {
				'method':'post',
				'dataType':'xml',
				'complete':function(){
					alert('done');
				},
				'data':{
					'action':'genesis_getdatepicker',
					'day':1,
					'month':6,
					'year':2013
				}
			});
		};
		
	
		
		
	});

})(jQuery);