function UserGraph(){
    var $ = jQuery;
	var $plot = null;
	this.mode = null;
	this.unit = null;
    this.averages = null;
	this.savePositions = {};
	this.previousPoint = null;
	this.overlayTooltip = null;
	
	this.userGraphData = null;
	this.averageUserGraphData = null;
	
	this.replot = function(){
		this.initialise(this.mode, this.unit, this.averages, true);
	}
	
	this.initialise = function(mode, unit, averages, moveToEnd) {
		var host = this;

		var settings = {
			'weight':{
				'noresults':"You haven't made any weight measurements yet.",
				'label':'Your Weight',
				'avgLabel':'Average weight for all participants',
				'color':'rgb(231,5,144)'
			},
			'weight_imperial':{
				'noresults':"You haven't made any weight measurements yet.",
				'label':'Your Weight',
				'avgLabel':'Average weight for all participants',
				'color':'rgb(231,5,144)'
			},
			'fat':{
				'noresults':"You haven't recorded any fat consumption yet.",
				'label':'Fat You\'ve Consumed',
				'avgLabel':'Average Fat Consumed',
				'color':'rgb(230,130,130)'
			},
			'carbs':{
				'noresults':"You haven't recorded any carbohydrate consumption yet.",
				'label':'Carbohydrates You\'ve Consumed',
				'avgLabel':'Average Carbohydrates Consumed',
				'color':'rgb(179,179,179)'
			},
			'protein':{
				'noresults':"You haven't recorded any protein consumption yet.",
				'label':'Protein You\'ve Consumed',
				'avgLabel':'Average Protein Consumed',
				'color':'rgb(92,178,208)'
			},
			'fruit':{
				'noresults':"You haven't recorded any fruit consumption yet.",
				'label':'Fruit You\'ve Consumed',
				'avgLabel':'Average Fruit Consumed',
				'color':'rgb(231,5,144)'
			},
			'vegetables':{
				'noresults':"You haven't recorded any vegetable consumption yet.",
				'label':'Vegetables You\'ve Consumed',
				'avgLabel':'Average Vegetables Consumed',
				'color':'rgb(255,201,107)'
			},
			'dairy':{
				'noresults':"You haven't recorded any dairy consumption yet.",
				'label':'Dairy You\'ve Consumed',
				'avgLabel':'Average Dairy Consumed',
				'color':'rgb(178,219,106)'
			},
			'alcohol':{
				'noresults':"You haven't recorded any alcohol units yet.",
				'label':'Alcohol Consumption',
				'avgLabel':'Average Alcohol Consumption',
				'color':'rgb(230,130,130)'
			},
			'treat':{
				'noresults':"You haven't recorded any treats yet.",
				'label':'Treat Consumption',
				'avgLabel':'Average Treat Consumption',
                'color':'rgb(179,179,179)'
			},
			'exercise_minutes':{
				'noresults':"You have not recorded any minutes of aerobic exercise yet.",
				'label':'Aerobic Minutes You\'ve Exercised',
				'avgLabel':'Average Minutes Exercised',
				'color':'rgb(255,201,107)'
			},
			'exercise_minutes_resistance':{
				'noresults':"You have not recorded any minutes of resistance exercise yet.",
				'label':'Resistance Minutes You\'ve Exercised',
				'avgLabel':'Average Minutes Exercised',
				'color':'rgb(174,218,74)'
			},
			'weight_loss':{
				'noresults':"You haven't made any weight measurements yet.",
				'label':'Your Weight Change',
				'avgLabel':"Average weight loss for all participants",
				'color':'rgb(118,47,152)',
                'legend':'<h3>This is your weight change since you started the study</h3><p class="under-title">An upward line indicates weight gain, and a downward line shows weight loss</p>'
			},
			'weight_loss_imperial':{
				'noresults':"You haven't made any weight measurements yet.",
				'label':'Your Weight Change',
				'avgLabel':"Average weight loss for all participants",
				'color':'rgb(118,47,152)',
                'legend':'<h3>This is your weight change since you started the study</h3><p class="under-title">An upward line indicates weight gain, and a downward line shows weight loss</p>'
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
        this.averages = averages == true ? true : false;
        
        var isExercise = this.mode == 'exercise_minutes' || this.mode == 'exercise_minutes_resistance';
		
		if(unit && (mode == 'weight' || mode == 'weight_loss')){
			mode = mode + "_" + unit;
		}
		
		$(".genesis-progress-graph").hide();
        $(".genesis-graph-container").addClass('empty');
		$('.no-results').show();
		
		if(settings[mode]){
			$(".no-results h2").html(settings[mode].noresults);
		}
		
		if($plot){
			$plot.shutdown();
			$(".genesis-progress-graph").empty();
		}
			
		if (this.userGraphData == null){
			return;
		}
		
		if(!this.userGraphData[mode]) {
			return;
		}
		
		
		// If we're in weight mode, require a start weight to be present
		if(!this.userGraphData[mode]['data']){
			return;
		}
		
		if(this.mode != 'weight' && this.userGraphData[mode]['data'].length == 0){
			return;
		}

	
		$(".genesis-progress-graph").show();
		$(".genesis-graph-container").removeClass('empty');
		$('.no-results').hide();
	
		var xTicks = [];
	
		if(this.userGraphData[mode].timestamps){
			for(var i = 0; i < this.userGraphData[mode].timestamps.length; i++){
				xTicks.push(this.userGraphData[mode].timestamps[i], this.userGraphData[mode].timestamps[i]);
			}
		}
	    
        
		var yMin = parseFloat(this.userGraphData[mode].yMin);
		var yMax = parseFloat(this.userGraphData[mode].yMax);
		var minDate = parseFloat(xTicks[0]);
		var maxDate = parseFloat(xTicks[xTicks.length - 1]);
		var showTicks = true;
		

        
		// Falsify the min and max date if we have no results
		if(isNaN(minDate)){
			minDate = 0;
			showTicks = false;
		}
		
		if(isNaN(maxDate)){
			maxDate = 1;
		}
		// So the single value is in the middle, add an arbirtrary amount of time either side
		if(minDate == maxDate){
			minDate -= 50000000;
			maxDate += 50000000;
		}
		
		switch(mode){
			case 'weight' :
			case 'weight_imperial' : 
			 yMin = Math.min(yMin, this.userGraphData['initial_weights']['initial_' + mode]);
			 yMax = Math.max(yMax, this.userGraphData['initial_weights']['initial_' + mode]);
		}
		

		// Show the min max y values for all users

		if(this.averageUserGraphData && this.averageUserGraphData[mode] && this.averages){
            if( !isNaN(parseFloat(this.averageUserGraphData[mode].yMin)) ){
                yMin = Math.min(yMin, parseFloat(this.averageUserGraphData[mode].yMin));
            }
            if( !isNaN(parseFloat(this.averageUserGraphData[mode].yMax)) ){
			    yMax = Math.max(yMax, parseFloat(this.averageUserGraphData[mode].yMax));
            }
		 }


	
		var yDiff = yMax - yMin;
		yTick = (yDiff / 10);
	
		
		// Round the tick
		yTick = Math.max(1, Math.ceil(yTick * 10) / 10);
		yMax = (yMax + yTick);
		
		// Give a y margin lower than zero if the bottom is already lower than zero (otherwise a negative)
		// y margin with all positive results looks odd.
		if(yMin >= 0){
            // Changed to -1 instead of zero, because the first time a user logs in their weight loss appears
            // right at the top of the graph, instead of being in the middle
			yMin = Math.max(yMin - yTick, 0);
            
            if(yMax == 1 && yMin == 0){
                yMin = -1;
            }
		}else{
			yMin = yMin - yTick;
		}
		
		this.yMin = yMin;
		this.yMax = yMax;
		
        var xMargin = isExercise ? 46400000 : 0;
	    var fill = true;
        
        if(mode == 'weight_loss' || mode == 'weight_loss_imperial'){
            fill = false;
        }
		
		var options = {
			lines: {
				show: isExercise ? false : true,
				fill: fill
			},
			points: {
				show: isExercise ? false : true,
				fill: true,
                radius: isExercise ? 7 : 3
			},
            bars : {
                show: isExercise ? true : false,
                barWidth:46400000,
                align:"center"
            },
			xaxis: {
				mode: 'time',
				timeformat: "%b %d",
				tickSize: [1, "day"],
				tickLength: 10,
				panRange:[minDate - xMargin, maxDate + xMargin],
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
				},
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
    
		
        if(this.mode == 'weight_loss'){
            options.yaxis.transform = function(v) {
                return -v;
                    };

            options.yaxis.inverseTransform = function(v) {
                return -v;
            }
        }
		
		if(!showTicks){
			options.xaxis.ticks = false;
		}
		
		
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
		// AVERAGE USER GRAPH DATA REMOVED TEMPORARILY

		if(this.averageUserGraphData && this.averageUserGraphData[mode] !== undefined && this.averages){
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
					[minDate, this.userGraphData['initial_weights']['initial_' + mode]],
					[maxDate, this.userGraphData['initial_weights']['initial_' + mode]]
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

		// Set the legend
        $('.graph-legend').empty();
        if(settings[mode].legend){
            $('.graph-legend').append(settings[mode].legend);
        }
        
		this.updateAxes();
	}
	
	this.formatYVal = function(val, mode){
		val = Math.round(val * 100) / 100;
		switch(mode){
			case 'weight_loss' :
			case 'weight' : 
                if(mode == 'weight_loss'){
                    val = -val;
                }
                
                return val + " kg";
		
			case 'weight_loss_imperial' :
			case 'weight_imperial' :
                if(mode == 'weight_loss_imperial'){
                    val = -val;
                }
				var st = val >= 0 ? Math.floor(val / 14) : Math.ceil(val / 14);		
				var p = val - (st * 14);
		
				p = Math.round(p * 100) / 100; 
				return (st ? (st + " st ") : "") + (p + " lb"); 

			case 'exercise_minutes' :
            case 'exercise_minutes_resistance' :
				return val + " minutes";
			case 'calories' :
				return val + " kcals";
            case 'alcohol' :
                return val + " units"
            case 'fat' :
            case 'protein' :
            case 'carbs' :
            case 'dairy' :
            case 'treat' :
            case 'vegetables':
            case 'fruit' :
                return Math.round(val) + ' portions';
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
		this.initialise(this.mode, unit, this.averages);
	}
    
    this.switchAverages = function(avg){
        this.initialise(this.mode, this.unit, avg);
    }
}