;(function($){
	$(document).ready(function(){
		$('.confirm-submit').on('submit', function(ev){
			if(window.confirm('Are you sure?')){
				return true;
			}
			
			ev.preventDefault();
		});
	});
}(jQuery));