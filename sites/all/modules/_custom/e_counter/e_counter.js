/**
 * Created by IVAN on 11.01.14.
 */

(function ($) {

    Drupal.behaviors.dateVisibility = {};

    Drupal.behaviors.dateVisibility.attach = function (context, settings) {

        $(".form-type-radios input").change(function(){

            var name = this.name;
            console.log(name.substr(0,6));
            if (name.substr(0,6) == 'period'){
                var id = name.substr(7, 1);
                console.log(id);
                if(this.value == 'current'){
                    $(".form-item-period-date-" + id).hide();
                }
                else{
                    $(".form-item-period-date-" + id).show();
                }
            }
        })
    };

    Drupal.behaviors.devicesTabs = {};

    Drupal.behaviors.devicesTabs.attach = function (context, settings) {


        $('#tabs', context)
            .once('.tabs-processed')
            .tabs();
    };


}(jQuery));