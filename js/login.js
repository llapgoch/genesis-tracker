;(function($){
    $(document).on('ready', function(){
        $('#registerform').attr('autocomplete', 'off');


        // Set the url of the header image - don't do this server side as it also changes the redirect URL
        if(window.wpBaseUrl){
            $('#login h1 a').attr('href', window.wpBaseUrl);
        }
    });
}(jQuery));