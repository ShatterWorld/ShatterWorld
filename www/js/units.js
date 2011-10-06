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
		 * Calculates relative position
		 * @param object
		 * @param integer
		 * @param integer
		 * @return array of integer
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

