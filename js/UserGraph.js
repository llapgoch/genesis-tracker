function UserGraph(){
	var $plot = null;
	var mode = null;
	
	this.userGraphData = null;
	this.averageUserGraphData = null;
	
	this.initialise = function(mode, unit) {
		if(!mode){
			mode = 'weight';
		}
		
		this.mode = mode;
		
		if(unit && (mode == 'weight' || mode == 'weight_loss')){
			mode = mode + "_" + unit;
		}
		
		if (this.userGraphData == null){
			return;
		}
	
		if(!this.userGraphData[mode]) {
			return;
		}
	
		if($plot){
			$plot.shutdown();
			$(".genesis-progress-graph").empty();
		}
	
		var xTicks = [];
	
		for(var i = 0; i < this.userGraphData.allDates.length; i++){
			xTicks.push(this.userGraphData.allDates[i], this.userGraphData.allDates[i]);
		}
	
		var yMin = parseFloat(this.userGraphData[mode].yMin);
		var yMax = parseFloat(this.userGraphData[mode].yMax);
		var minDate = parseFloat(this.userGraphData.minDate);
		var maxDate = parseFloat(this.userGraphData.maxDate);
	
		if(this.averageUserGraphData && this.averageUserGraphData[mode]){
			yMin = Math.min(yMin, parseFloat(this.averageUserGraphData[mode].yMin));
			yMax = Math.max(yMax, parseFloat(this.averageUserGraphData[mode].yMax));
		
			minDate = Math.min(minDate, this.averageUserGraphData.minDate);
			maxDate = Math.max(maxDate, this.averageUserGraphData.maxDate);
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
				min: yMin - 2,
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
			"data": this.userGraphData[mode]['data'],
			"color": settings[mode].color
		});
	
		// Plot the average user data for everyone on the site along side the user's data
		if(this.averageUserGraphData && this.averageUserGraphData[mode] !== undefined){			
			data.push({
				"label":settings[mode].avgLabel,
				"data":this.averageUserGraphData[mode].data,
				"color":'rgb(207,207,207)',
				"points":{
					"show":false
				},
				"lines":{
					"fill":false
				}
			});	
		}
	
		if(mode == 'weight' || mode == 'weight_imperial' && this.userGraphData['initial_weights']){
			// Plot the user's start date

			data.push({
				"label":"Your initial Weight",
				"data":[
					[this.userGraphData.minDate, this.userGraphData['initial_weights']['initial_' + mode]],
					[this.userGraphData.maxDate, this.userGraphData['initial_weights']['initial_' + mode]]
				],
				"color":'rgb(0,0,0)',
				"fill":false,
				"lines":{
					fill:false
				},
				"points":{
					"show":false
				}
			});
		}
	
		window.plot = $plot = $.plot($('.genesis-progress-graph'), data, options);
	
		plot.pan({'left':1000000000});
	}
	
	this.getMode = function(){
		return mode;
	}
	
	this.changeUnits = function(unit){
		this.initialise(this.mode, unit);
	}
}