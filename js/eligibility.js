;(function($){
    $(document).ready(function(){
       $('.weight-unit').on('change', function(){
           $('.weight-input').val('');
           calculateBmi();
       });
       
       $('.height-unit').on('change', function(){
           $('.height-input').val('');
           calculateBmi();
       });
       
       $('.weight-input, .height-input').on('keyup change', function(){
          calculateBmi(); 
       });
       
       function calculateBmi(){
           var weight = $('#weight-main').val();
           var height = $('#height-main').val();
           
           if($('.weight-unit').val() == 1){
               weight = GenesisTracker.weightToMetric(weight, $('#weight-pounds').val());
           }
           
           if($('.height-unit').val() == 1){
               height = GenesisTracker.distToMetric(height, $('#height-inches').val());
           }

           var bmi = GenesisTracker.calculateBmi(weight, height);
           
           $('.bmi-inner').html(bmi <= 0 ? "*" : bmi);
       }
       
       
       calculateBmi();
    });
}(jQuery));