/**
 * Units
 * @author Petr Bělohlávek
 */

/**
 * Global functions
 */
jQuery.extend({
	units: {
		/**
		 * Sets the clickable maximal amounts of each unit
		 * @return void
		 */
		setMaximumAmount: function (){
			var maxTds = $('#trainUnitTable .max');

			$.each(maxTds, function(key, td){
				var metal = $(td).parent().data()['costs'][0];
				var stone = $(td).parent().data()['costs'][1];
				var food = $(td).parent().data()['costs'][2];
				var fuel = $(td).parent().data()['costs'][3];

				var n = jQuery.resources.getAvailibleUnits(metal, stone, food, fuel);

				alert(n);
			});
		}


	}
});


$(document).ready(function(){
	jQuery.units.setMaximumAmount();


});

