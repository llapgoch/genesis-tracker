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
				initialiseUserGraph();
			}
		});
		
		
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
				mode = 'weight-metric';
			}
		
		if (!window.userGraphData) {
			return;
		}
		
		var xTicks = [];
		
		for(var i = 0; i < userGraphData.allDates.length; i++){
			xTicks.push(userGraphData.allDates[i], userGraphData.allDates[i]);
		}
		
		
		
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
				timeformat: "%d/%m/%Y",
				tickSize: [1, "day"],
				tickLength: 10,
				panRange:[userGraphData.minDate, userGraphData.maxDate],
				ticks:xTicks
		
			},
			yaxis: {
				autoscaleMargin: 0.5,
				min: userGraphData[mode].yMin,
				max:userGraphData[mode].yMax,
				panRange: [userGraphData[mode].yMin, userGraphData[mode].yMax],
				tickLength: null,
				tickFormatter:function(val){
					switch(mode){
						case 'weight-metric' : 
						return val + " kg";
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
			console.log(item);
		 });
		
		if(parseFloat(userGraphData.maxDate) - parseFloat(userGraphData.minDate) >=	1000000000){
			options.xaxis.min = 0;
			options.xaxis.max = 1000000000;
		}

		data = [{
			"label": "Your Weight",
			"data": userGraphData[mode]['data'],
			"color": "rgb(231,5,144)"
		}

		];

		window.plot = $.plot($('.genesis-progress-graph'), data, options);
		
		plot.pan({'left':1000000000});

	}
	});

})(jQuery);