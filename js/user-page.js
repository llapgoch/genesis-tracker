(function($){
	$(document).ready(function(){
		$('#add-progress').on('submit', function(ev){
			ev.preventDefault();
			
			$.ajax(myAjax.ajaxurl, {
				'method':'post',
				'dataType':'xml',
				'complete':function(){
					alert('done');
				},
				'data':{
					'action':'moose',
					'chimp':'bobble'
				}
			});
		});
	});
})(jQuery);