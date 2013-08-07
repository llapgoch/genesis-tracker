(function($){
	var $plot = null;
	
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
				// Add events
				$('.progress-graph-switcher > button').on('click', function(e){
					e.preventDefault();
					var mode = $(this).data('mode');
					
					initialiseUserGraph(mode);
					selectModeButton(mode);
				});
			}
			
			initialiseUserGraph('weight_' + $('.mode-switcher').val());
			selectModeButton('weight');
		});
		
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
		
		function initialiseUserGraph(mode) {
			if(!mode){
				mode = 'weight';
			}
		
		if (!window.userGraphData |! window.userGraphData[mode]) {
			return;
		}
		
		if($plot){
			$plot.shutdown();
			$(".genesis-progress-graph").empty();
		}
		
		var xTicks = [];
		
		for(var i = 0; i < userGraphData.allDates.length; i++){
			xTicks.push(userGraphData.allDates[i], userGraphData.allDates[i]);
		}
		
		var yMin = parseFloat(userGraphData[mode].yMin);
		var yMax = parseFloat(userGraphData[mode].yMax);
		var minDate = parseFloat(userGraphData.minDate);
		var maxDate = parseFloat(userGraphData.maxDate);
		
		if(window.averageUserGraphData[mode]){
			yMin = Math.min(yMin, parseFloat(window.averageUserGraphData[mode].yMin));
			yMax = Math.max(yMax, parseFloat(window.averageUserGraphData[mode].yMax));
			
			minDate = Math.min(minDate, window.averageUserGraphData.minDate);
			maxDate = Math.max(maxDate, window.averageUserGraphData.maxDate);
		}
		
		
		var yDiff = yMax - yMin;
		yTick = Math.round(yDiff / 10);
		
		yMax += yTick;
		
		
		var settings = {
			'weight':{
				'tickSize':yTick,
				'label':'Your Weight (metric)',
				'avgLabel':'Average User Weight',
				'color':'rgb(231,5,144)'
			},
			'weight_imperial':{
				'tickSize':7,
				'label':'Your Weight (imperial)',
				'avgLabel':'Average User Weight',
				'color':'rgb(231,5,144)'
			},
			'calories':{
				'tickSize':yTick,
				'label':'Calories You\'ve Consumed',
				'avgLabel':'Average Calories Consumed',
				'color':'rgb(92,178,208)'
			},
			'exercise_minutes':{
				'tickSize':yTick,
				'label':'Minutes You\'ve Exercised',
				'avgLabel':'Average Minutes Exercised',
				'color':'rgb(255,201,107)'
			},
			'weight_loss':{
				'tickSize':yTick,
				'label':'Your Weight Loss (metric)',
				'avgLabel':'Average Weight Loss',
				'color':'rgb(255,134,134)'
			},
			'weight_loss_imperial':{
				'tickSize':7,
				'label':'Your Weight Loss (imperial)',
				'avgLabel':'Average Weight Loss',
				'color':'rgb(255,134,134)'
			}
		};
		
		
		var options = {
			lines: {
				show: true,
				fill: true
			},
			points: {
				show: true,
				fill: true,
			},
			xaxis: {
				mode: 'time',
				timeformat: "%b %d",
				tickSize: [1, "day"],
				tickLength: 10,
				panRange:[minDate, maxDate]
		
			},
			yaxis: {
				autoscaleMargin: 0.5,
				min: yMin,
				max:yMax,
				panRange: [yMin, yMax],
				tickSize:settings[mode].tickSize,
				tickLength: null,
				tickFormatter:function(val){
					switch(mode){
						case 'weight_loss' :
						case 'weight' : 
						return val + " kg";
						
						case 'weight_loss_imperial' :
						case 'weight_imperial' :
							var st = Math.floor(val / 14);
							var p = val - (st * 14);
							return st + " st " + (p ? p + " lb" : ""); 

						case 'exercise_minutes' :
							return val + " minutes";
						case 'calories' :
							return val + " kcals";
					}
					
					return val;
				}
			},
			grid: {
				show: true,
				margin: 10,
				borderWidth:1,
				borderColor:0xCCCCCC,
				hoverable:true,
				clickable:true
			},
			zoom: {
				interactive: false
			},
			pan: {
				interactive: true
			}
		};
		
		 $(".genesis-progress-graph").bind("plothover", function (event, pos, item) {
			 if(!item){
				 return;
			}
		 });
		
		
		if(parseFloat(maxDate) - parseFloat(minDate) >=	1000000000){
			options.xaxis.min = 0;
			options.xaxis.max = 1000000000;
		}
		
		var data = [];
		
		

		data.push({
			"label":settings[mode].label,
			"data": userGraphData[mode]['data'],
			"color": settings[mode].color
		});
		if(window.averageUserGraphData && window.averageUserGraphData[mode] !== undefined){			
			data.push({
				"label":settings[mode].avgLabel,
				"data":window.averageUserGraphData[mode].data,
				"color":'rgb(207,207,207)'
			});	
		}
		
		
		

		window.plot = $plot = $.plot($('.genesis-progress-graph'), data, options);
		
		plot.pan({'left':1000000000});

	}
	});

})(jQuery);