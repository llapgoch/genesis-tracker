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
					updateFormValues($(this));
				});
			}
            
            if($('.date-input').val()){
                showUserMeasurements();
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
			
			$('.question-chooser').on('change', function(e){
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
				$('.progress-graph-switcher .button-group button').on('click', function(e){
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
					
					userGraph.changeUnits($(this).val() == 1 ? "imperial" : "");
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
        
        function showUserMeasurements(animate){
            var $removeVal = $('#wpadminbar').size() ? $('#wpadminbar').outerHeight() : 0;
            $('.user-tracking-input .user-measurements').css('display', 'block');
            
            if(animate){
                $('body').animate({
                    scrollTop:(jQuery('.user-tracking-input').offset().top - ($removeVal + 20))
                }, 500);
            }
        }
		
		function updateFormValues(pickField){
			var selDate = pickField.datepicker('getDate');
			showPreloader();
			
			$.ajax(myAjax.ajaxurl, {
				'type':'post',
				'dataType':'json',
				'complete':function(){
					removePreloader();
				},
				'success':function(data){
                    var $form = $('.user-tracking-input');
                    var $weightUnit = $form.find('.weight-unit');
                    var measures = data.measure_details;
                    
                    // Close all forms
                    $form.find('.question-chooser').prop('checked', false).trigger('change');
                    
                    // Set the value to the saved unit with this measurement
                    // Otherwise set the form's weight default back to the user's initial selection if we've got it
                    if(measures.weight_unit){
                        $weightUnit.val(measures.weight_unit);
                    }else{
                        if(window.initialUserUnit){
                            $weightUnit.val(window.initialUserUnit);
                        }
                    }
                    $weightUnit.trigger('change');
                    
                    // Clear the weight form
                    $('.weight-container input[type="text"]').val('');
                    // Clear the exercise form
                    $('.exercise-container input[type="text"]').val('');
                    // Clear the food form
                    $('.food-container input[type="text"]').val(0);
                   
                    var unit = $weightUnit.val() == 1 ? "imperial" : "metric";
                    
                    // See if there were any diet days in the saved data
                    if($(data.date_picker).find('input[checked="checked"]').size()){
                        $form.find('input[name="diet-days"]').prop('checked', true).trigger('change');
                    }
                    

                    // Set the diet days
					$('.diet-days').html(data.date_picker);

                    // Set the exercise values
                    if(measures.exercise_minutes){
                        $form.find('input[name="exercise_minutes"]').val(measures.exercise_minutes);
                        $form.find('input[name="record-exercise"]').prop('checked', true).trigger('change');
                    }
                    
                    var $weightMain = $form.find('input[name="weight_main"]');
                    var $weightPounds = $form.find('input[name="weight_pounds"]');
                    
                    // Set the weight values
                    if(measures.weight){
                        var $weightMain = $form.find('input[name="weight_main"]');
                        var $weightPounds = $form.find('input[name="weight_pounds"]');
                        
                        if(unit == "metric"){
                            $weightMain.val(measures.weight)
                        }else{
                            $weightMain.val(measures.weight_imperial.stone);
                            $weightPounds.val(measures.weight_imperial.pounds);
                        }
                        
                        // Open the weight form field
                        $form.find('input[name="record-weight"]').prop('checked', true).trigger('change');
                    }
                    
                    showUserMeasurements(true);
				},
                'error':function(data){
                    alert('Sorry, we\'re experiencing technical difficulties at the moment.  Please try again later');
                },
				'data':{
					'action':'genesis_get_form_values',
					'day':selDate.getDate(),
					'month':selDate.getMonth() + 1,
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
			$('.progress-graph-switcher .button-group button').removeClass('selected');
			$('.progress-graph-switcher .button-group button[data-mode="' + mode + '"]').addClass('selected');
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