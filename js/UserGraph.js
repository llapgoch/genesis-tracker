function UserGraph(){
	var $plot = null;
	this.mode = null;
	this.unit = null;
	this.savePositions = {};
	this.previousPoint = null;
	this.overlayTooltip = null;
	
	this.userGraphData = null;
	this.averageUserGraphData = null;
	
	this.replot = function(){
		this.initialise(this.mode, this.unit, true);
	}
	
	this.initialise = function(mode, unit, moveToEnd) {
		var host = this;
		
		var settings = {
			'weight':{
				'noresults':"You haven't made any weight measurements yet.",
				'label':'Your Weight (metric)',
				'avgLabel':'Average User Weight',
				'color':'rgb(231,5,144)'
			},
			'weight_imperial':{
				'noresults':"You haven't made any weight measurements yet.",
				'label':'Your Weight (imperial)',
				'avgLabel':'Average User Weight',
				'color':'rgb(231,5,144)'
			},
			'calories':{
				'noresults':"You haven't made any calorie measurements yet.",
				'label':'Calories You\'ve Consumed',
				'avgLabel':'Average Calories Consumed',
				'color':'rgb(92,178,208)'
			},
			'exercise_minutes':{
				'noresults':"You haven't made any measurements for minutes exercised yet.",
				'label':'Minutes You\'ve Exercised',
				'avgLabel':'Average Minutes Exercised',
				'color':'rgb(255,201,107)'
			},
			'weight_loss':{
				'noresults':"You haven't made any weight measurements yet.",
				'label':'Your Weight Loss (metric)',
				'avgLabel':'Average Weight Loss',
				'color':'rgb(255,134,134)'
			},
			'weight_loss_imperial':{
				'noresults':"You haven't made any weight measurements yet.",
				'label':'Your Weight Loss (imperial)',
				'avgLabel':'Average Weight Loss',
				'color':'rgb(255,134,134)'
			}
		};
		
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
		
		if($plot){
			$plot.shutdown();
			$(".genesis-progress-graph").empty();
		}
		
		
		if(!this.userGraphData[mode]['data'] || !this.userGraphData[mode]['data'].length){
			$(".no-results h2").html(settings[mode].noresults);
			$(".genesis-progress-graph").hide();
			$(".genesis-graph-container").addClass('empty');
			$('.genesis-graph-container .no-results').show();
			return;
		}
	
		$(".genesis-progress-graph").show();
		$(".genesis-graph-container").removeClass('empty');
		$('.genesis-graph-container .no-results').hide();
	
		var xTicks = [];
	
		for(var i = 0; i < this.userGraphData[mode].timestamps.length; i++){
			xTicks.push(this.userGraphData[mode].timestamps[i], this.userGraphData[mode].timestamps[i]);
		}
	
		var yMin = parseFloat(this.userGraphData[mode].yMin);
		var yMax = parseFloat(this.userGraphData[mode].yMax);
		var minDate = parseFloat(xTicks[0]);
		var maxDate = parseFloat(xTicks[xTicks.length - 1]);
		
		
		switch(mode){
			case 'weight' :
			case 'weight_imperial' : 
			 yMin = Math.min(yMin, this.userGraphData['initial_weights']['initial_' + mode]);
			 yMax = Math.max(yMax, this.userGraphData['initial_weights']['initial_' + mode]);
		}
		
	
		// Show the min max y values for all users
		if(this.averageUserGraphData && this.averageUserGraphData[mode]){
			yMin = Math.min(yMin, parseFloat(this.averageUserGraphData[mode].yMin));
			yMax = Math.max(yMax, parseFloat(this.averageUserGraphData[mode].yMax));
		 }
	
	
		var yDiff = yMax - yMin;
		yTick = (yDiff / 10);
		
		console.log(yMin, Math.max(5,yMax));
		
		// Round the tick
		yTick = Math.round(yTick * 100) / 100;
		
		yMax = (yMax + Math.max(5, yTick));
		
		// Give a y margin lower than zero if the bottom is already lower than zero (otherwise a negative)
		// y margin with all positive results looks odd.
		if(yMin >= 0){
			yMin = Math.max(yMin - Math.max(5,yTick), 0);
		}else{
			yMin = yMin - yTick;
		}
		
		this.yMin = yMin;
		this.yMax = yMax;
		
	
		
		
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
				tickSize:yTick,
				tickLength: null,
				tickFormatter:function(val){
					return host.formatYVal(val, mode);
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
		
		if(parseFloat(maxDate) - parseFloat(minDate) >=	1000000000){
			options.xaxis.min = 0;
			options.xaxis.max = 1000000000;
		}else{
			options.xaxis.min = minDate;
			options.xaxis.max = maxDate;
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
		$('.genesis-progress-graph').off('plothover');
		$('.genesis-progress-graph').on('plothover', function(e, pos, item){
			if(!item){
				host.removeOverlayTooltip();
				host.previousPoint = null;
				return;
			}
			
			if(host.previousPoint == item.dataIndex){
				return;
			}
		    
			host.removeOverlayTooltip();
			
		    var x = item.datapoint[0].toFixed(2);
		    var y = item.datapoint[1].toFixed(2);
			
			var content = host.formatYVal(y, mode);
			
		    host.$overlayTooltip = $('<div id="tooltip">' + content + '</div>').css( {
	               position: 'absolute',
	               border: '1px solid #fdd',
	               padding: '2px',
	               'background-color': '#fee',
	               opacity: 0.80
		    });
			
			
			$(document.body).append(host.$overlayTooltip);
			
			host.$overlayTooltip.css({
				top: item.pageY + 5,
	            left: (item.pageX - 5) - host.$overlayTooltip.outerWidth(),
			})
			
			// Plot the point
			host.previousPoint = item.dataIndex;
		});
		
		if(moveToEnd){
			// This should probably be the max time we've got in our dataset - not sure where this came from
			this.$plot.pan({'left':1374534660788});
		}
		
		if(this.savePositions[this.mode]){
			this.$plot.getOptions().xaxes[0].min = this.savePositions[this.mode].xmin;
			this.$plot.getOptions().xaxes[0].max = this.savePositions[this.mode].xmax;
		}

		
		this.updateAxes();
	}
	
	this.formatYVal = function(val, mode){
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
	}
	
	this.removeOverlayTooltip = function(){
		if(this.$overlayTooltip){
			this.$overlayTooltip.remove();
		}
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
		this.updateAxes();
	}
	
	this.zoomOut = function(){
		if(!this.$plot){
			return;
		}
		
		this.$plot.zoomOut();
		this.updateSavePositions();
		this.updateAxes();
	}
	
	this.updateAxes = function(){
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