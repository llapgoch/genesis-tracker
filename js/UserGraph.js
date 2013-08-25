

function UserGraph(){
	var $plot = null;
	this.mode = null;
	this.unit = null;
	this.savePositions = {};
	
	this.userGraphData = null;
	this.averageUserGraphData = null;
	
	this.replot = function(){
		this.initialise(this.mode, this.unit, true);
	}
	
	this.initialise = function(mode, unit, moveToEnd) {
		if(!mode){
			mode = 'weight';
		}
		moveToEnd = moveToEnd == false ? false : true;
		
		// Save previous plot's settings
		this.updateSavePositions();
		
		this.mode = mode;
		this.unit = unit;
		
		if(unit && (mode == 'weight' || mode == 'weight_loss')){
			mode = mode + "_" + unit;
		}
		
		if (this.userGraphData == null){
			return;
		}
	
		if(!this.userGraphData[mode]) {
			return;
		}
	
		if($plot && moveToEnd){
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
		
		
		switch(mode){
			case 'weight' :
			case 'weight_imperial' : 
			 yMin = Math.min(yMin, this.userGraphData['initial_weights']['initial_' + mode]);
			 yMax = Math.max(yMax, this.userGraphData['initial_weights']['initial_' + mode]);
		}
		
	
	
		if(this.averageUserGraphData && this.averageUserGraphData[mode]){
			yMin = Math.min(yMin, parseFloat(this.averageUserGraphData[mode].yMin));
			yMax = Math.max(yMax, parseFloat(this.averageUserGraphData[mode].yMax));
		
			minDate = Math.min(minDate, this.averageUserGraphData.minDate);
			maxDate = Math.max(maxDate, this.averageUserGraphData.maxDate);
		}
	
	
		var yDiff = yMax - yMin;
		yTick = (yDiff / 10);
		
		// Round the tick
		yTick = Math.round(yTick * 100) / 100;
		
		yMax = (yMax + yTick);
		yMin = (yMin - yTick);
		
		this.yMin = yMin;
		this.yMax = yMax;
	
		var settings = {
			'weight':{
				'tickSize':yTick,
				'label':'Your Weight (metric)',
				'avgLabel':'Average User Weight',
				'color':'rgb(231,5,144)'
			},
			'weight_imperial':{
				'tickSize':yTick,
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
				'tickSize':yTick,
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
				min:yMin,
				max:yMax,
				panRange: [yMin, yMax],
				zoomRange: [yMin, yMax],
				tickSize:settings[mode].tickSize,
				tickLength: null,
				tickFormatter:function(val){
					val = Math.round(val * 100) / 100;
					switch(mode){
						case 'weight_loss' :
						case 'weight' : 
						return val + " kg";
					
						case 'weight_loss_imperial' :
						case 'weight_imperial' :
							var st = Math.floor(val / 14);
							var p = val - (st * 14);
							
							p = Math.round(p * 10) / 10; 
							return (st ? (st + " st ") : "") + (p ? p + " lb" : ""); 

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
				interactive: true,
				cursor:"move"
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
	
		this.$plot = window.$plot = $.plot($('.genesis-progress-graph'), data, options);
		
		if(moveToEnd){
			this.$plot.pan({'left':1374534660788});
		}
		
		if(this.savePositions[this.mode]){
			this.$plot.getOptions().xaxes[0].min = this.savePositions[this.mode].xmin;
			this.$plot.getOptions().xaxes[0].max = this.savePositions[this.mode].xmax;
		}

		
		this.updateXAxis();
	}
	
	this.updateSavePositions = function(){
		if(!this.$plot){
			return;
		}
		
		this.savePositions[this.mode] = {};
		this.savePositions[this.mode].xmin = this.$plot.getOptions().xaxes[0].min;
		this.savePositions[this.mode].xmax = this.$plot.getOptions().xaxes[0].max;
	}
	
	this.zoomIn = function(){
		if(!this.$plot){
			return;
		}
		
		this.$plot.zoom();
		this.updateSavePositions();
		this.updateXAxis();
	}
	
	this.zoomOut = function(){
		if(!this.$plot){
			return;
		}
		
		this.$plot.zoomOut();
		this.updateSavePositions();
		this.updateXAxis();
	}
	
	this.updateXAxis = function(){
		if(!this.$plot){
			return;
		}
		
		var timeframe;
		
		if(this.savePositions[this.mode]){
			timeframe = this.savePositions[this.mode].xmax - this.savePositions[this.mode].xmin;
		}else{
			timeframe = this.$plot.getData()[0].xaxis.max - this.$plot.getData()[0].xaxis.min;
		}
		
		var days = Math.ceil(timeframe / 500000000);
		
		this.$plot.getOptions().xaxes[0].tickSize = [days, "day"]; 
		this.$plot.getOptions().yaxes[0].min = this.yMin;
		this.$plot.getOptions().yaxes[0].max = this.yMax;
		
		this.$plot.setupGrid();
		this.$plot.draw();
	}
	
	this.getMode = function(){
		return this.mode;
	}
	
	this.changeUnits = function(unit){
		this.initialise(this.mode, unit);
	}
}