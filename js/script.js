window.GenesisTracker = window.GenesisTracker || {};

GenesisTracker.calculateBmi = function(weight, height){
    weight = Math.max(0, parseFloat(weight));
    height = Math.max(0, parseFloat(height));
    
    if(isNaN(weight) || isNaN(height)){
        return 0;
    }
    if(weight <= 0 || height <= 0){
        return 0;
    }
    
    var bmi = weight / (height * height);
    return Math.round(bmi * 100) / 100; 
};

GenesisTracker.distToMetric = function(feet, inches){
    feet = Math.max(0, isNaN(parseFloat(feet)) ? 0 : parseFloat(feet));
    inches = Math.max(0, isNaN(parseFloat(inches)) ? 0 : parseFloat(inches));
    
    feet += (inches / 12);
    return feet * 0.3048;
};

GenesisTracker.weightToMetric = function(stone, pounds){
    stone = Math.max(0, isNaN(parseFloat(stone)) ? 0 : parseFloat(stone));
    pounds = Math.max(0, isNaN(parseFloat(pounds)) ? 0 : parseFloat(pounds));

    return ((stone * 14) + pounds) * 0.453592;
};

(function($){	
	$(document).ready(function(){
		$('.js-hide').hide();
		$('.js-show').show();
        
        // Add popups for help
        $.widget("ui.tooltip", $.ui.tooltip, {
              options: {
                  content: function () {
                      return $(this).prop('title');
                  },
                  tooltipClass:'tooltipPopup',
                  position:{ my: "left+15 center", at: "right center" }
              }
          });

          $('.help-icon').tooltip();
		
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


                // Add exercise events
                $('.user-tracking-input').on('click', '.js-add-new-exercise', function(ev){
                    addExerciseType($(this).closest('.js-exercise-type-container'));
                });

                $('.user-tracking-input').on('click', '.js-remove-exercise', function(ev){
                    removeExercise($(this).closest('.js-question-row'));
                });

                updateAllExerciseRows();
			}
            
            if($('.date-input').val()){
                showUserMeasurements();
            }
			
			$('.changeunits').hide();
			
			// So the error gets turned off when we switch
			$('.error-weight_pounds').addClass('imperial');
			
			$('.weight-unit').on('change', function(){
				updateWeightVisibilities(this);
			});
            
			$('.height-unit').on('change', function(){
				updateHeightVisibilities(this);
			});
            
            if(window.initialUserUnit){
                // Set any selects to show the correct units the user originally selected
                $('.weight-unit').prop(window.initialUserUnit);
            }
			
			updateWeightVisibilities($('.weight-unit'));
            updateHeightVisibilities($('.height-unit'));
			
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
                    selectModeButton(mode);
                    
                    if(mode == 'unrestricted-days'){
                        $('.genesis-graph-container').hide();
                        $('.genesis-food-table-container').show(); 
                    }else{
                        $('.genesis-food-table-container').hide(); 
                        $('.genesis-graph-container').show();
                        var averages = $('.extended-button input').is(":checked");
    					userGraph.initialise(mode, $('.mode-switcher').val() == 1 ? "imperial" : "", averages);
                    }
                   
				});
            
                
                $('.extended-button input').on('click', function(e){
                   var showAverages = $(this).is(":checked");
                    
                   userGraph.switchAverages(showAverages);
                });
				
				$('.mode-switcher').on('change', function(){
                    if($.inArray(userGraph.getMode(), switcherCompat) === false){
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


              $('.food-input:not([readonly="readonly"]').on('click, focus', function(){
                 var $this = $(this);

                  if(!isNaN(parseFloat($this.val())) && parseFloat($this.val()) == 0){
                      $this.val('');
                  }
              });

            $('.food-input').on('blur', function(){
                var $this = $(this);

                if($this.val() == ''){
                    $this.val('0');
                }
            });
              
              $('.food-input').on('change, keyup', function(){
                  calculateFoodTotals();
              });
              
              calculateFoodTotals();
			
		});

        var questionRowSelector = '.js-question-row';

        function addExerciseType($container, answers){
            var $clone = $(questionRowSelector, $container).clone();
            var $type = $('.js-exercise-type', $clone);

            $('.js-exercise-minutes, .js-exercise-description', $clone).val("");
            $('.js-exercise-type', $clone).val($('option:first', $type).val());

            $(questionRowSelector, $container).parent().append($clone);

            updateExerciseRows($container);
        }

        function removeExercise($row){
            $container = $row.closest('.js-exercise-type-container');

            if($(questionRowSelector, $container).size() <= 1){
                return;
            }

            $row.remove();
            updateExerciseRows($container);
        }

        function updateExerciseRows($container){
            if($(questionRowSelector, $container).size() <= 1){
                $('.js-remove-exercise', $container).addClass('hidden');
            }else{
                $('.js-remove-exercise', $container).removeClass('hidden');
            }


            var renameFields = [
                {selector:'.js-exercise-minutes', suffix: 'minutes'},
                {selector:'.js-exercise-type', suffix: 'type'},
                {selector: '.js-exercise-description', suffix: 'description'}
            ];

            var count = 0;

            // Rename rows - the validation doesn't work with multidimensional arrays
            $(questionRowSelector, $container).each(function(){
                var type = $container.data('exercise-type');
                var $questionRow = $(this);

                $(renameFields).each(function(i, val){
                    $(val.selector, $questionRow).attr('name', 'exercise_' + type + "_" + val.suffix + "_" + count);
                });

                count++;
            });

            $('.js-exercise-count', $container).val(count);
        }

        function updateAllExerciseRows(){
            $('.js-exercise-type-container').each(function(){
               updateExerciseRows($(this));
            });
        }
        
        function calculateFoodTotals(){
            $('.total-box').each(function(){
               var type = $(this).data('total-type');
               var total = 0;
               
               $('.user-measurements input[data-input-food="' + type + '"]').each(function(){
                   var val = parseFloat($(this).val());
                   
                   if(!isNaN(val) && val >= 0){
                       total += val;
                   }
               });
               
               total = Math.round(total * 100) / 100;

               $(this).find('.value').html(total);
            });
        }
		
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
                    // Clear the food form - not the example readonly ones though
                    $('.food-container input.food-input[type="text"]:not([readonly="readonly"])').val(0);
                    // Clear the food descriptions
                    $('.food-container .food-description').val('');


                    $('.food-container .food-description').each(function(){
                        var $this = $(this),
                            time = $this.data('time');

                        if(data.autofill_foods[time]) {
                            $this.autocomplete({
                                source: data.autofill_foods[time],
                                select: function( event, data ) {
                                    var time = $(event.target).data('time');
                                    var floatVal;

                                    for(var i in data.item) {
                                        if(!data.item.hasOwnProperty(i)){
                                            continue;
                                        }

                                        floatVal = parseFloat(data.item[i]);
                                        $("#" + time + "_" + i).val(isNaN(floatVal) ? 0 : floatVal);
                                    }
                                }
                            });
                        }
                    });
                   
                    var unit = $weightUnit.val() == 1 ? "imperial" : "metric";
                    
                    // See if there were any diet days in the saved data
                    if($(data.date_picker).find('input[checked="checked"]').size()){
                        $form.find('input[name="diet-days"]').prop('checked', true).trigger('change');
                    }
                    

                    // Set the diet days
					$('.diet-days').html(data.date_picker);

                    if(isNaN(parseFloat(measures.exercise_minutes))){
                        measures.exercise_minutes = 0;
                    }
                    
                    if(isNaN(parseFloat(measures.exercise_minutes_resistance))){
                        measures.exercise_minutes_resistance = 0;
                    }
                    
                    $form.find('input[name="exercise_minutes"]').val(measures.exercise_minutes);
                    $form.find('input[name="exercise_minutes_resistance"]').val(measures.exercise_minutes_resistance);

                    var $selectType = $form.find('select[name="exercise_type"]'),
                        $selectResistanceType = $form.find('select[name="exercise_type_resistance"]');

                    if(measures.exercise_type) {
                        $selectType.val(measures.exercise_type);
                    }else{
                        $selectType.val($('option', $selectType).first().val());
                    }

                    if(measures.exercise_type_resistance) {
                        $selectResistanceType.val(measures.exercise_type_resistance);
                    }else{
                        $selectResistanceType.val($('option', $selectResistanceType).first().val());
                    }

                    $form.find('textarea[name="exercise_description"]').val(measures.exercise_description);
                    $form.find('textarea[name="exercise_description_resistance"]').val(measures.exercise_description_resistance);

                    // Set the exercise values
                    if(measures.exercise_minutes || measures.exercise_minutes_resistance){
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
                    
                    // Food log
                    if(data.food_log && data.food_log.length){
                        $form.find('input[name="record-food"]').prop('checked', true).trigger('change');
                        
                        $(data.food_log).each(function(i, val){
                            if(val.food_type && val.time){
                                $form.find("input[name=" + val.time + "_" + val.food_type + "]").val(val.value);
                            }
                        });
                    }
                    
                    // Food descriptions
                    if(data.food_descriptions && data.food_descriptions.length){
                        $form.find('input[name="record-food"]').prop('checked', true).trigger('change');
                        
                        $(data.food_descriptions).each(function(i, val){
                            $form.find("input[name=" + val.time + "_description]").val(val.description);
                        });
                    }

                    $form.find('[name="food_log_explanation"]').val(measures.food_log_explanation);

                    showUserMeasurements(true);
                    calculateFoodTotals();
                    $('.form-input-error-container').remove();
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
				$('.metric.weight').addClass('hidden');
				$('.imperial.weight').removeClass('hidden');
			}else{
				$('.metric.weight').removeClass('hidden');
				$('.imperial.weight').addClass('hidden');
			}
		}
        
		function updateHeightVisibilities(formElement){
			// possibly change this so it only acts upon the form the dropdown is in.
			if($(formElement).val() == 1){
				$('.metric.height').addClass('hidden');
				$('.imperial.height').removeClass('hidden');
			}else{
				$('.metric.height').removeClass('hidden');
				$('.imperial.height').addClass('hidden');
			}
		}
		
		function selectModeButton(mode){
			$('.progress-graph-switcher .button-group button').removeClass('selected');
			$('.progress-graph-switcher .button-group button[data-mode="' + mode + '"]').addClass('selected');
        
            
            if(mode == 'weight_loss'){
                $('.extended-button .averages').removeClass('disabled');
                $('.extended-button .averages input').prop('disabled', false);
            }else{
                $('.extended-button .averages').addClass('disabled');
                $('.extended-button .averages input').prop('disabled', true);
            }
            
			$('.mode-switcher').attr('disabled', 'disabled');
			
			if(mode == 'weight' || mode == 'weight_loss'){
				$('.mode-switcher').removeAttr('disabled');
			}
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