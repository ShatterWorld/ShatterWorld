/**
 * Units
 * @author Petr Bělohlávek
 */

/**
 * Global functions
 */
var Game = Game || {};
Game.units = {
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

			var countSpan = $('<span />').html('');
			$(td).html('(');
			$(td).append(countSpan);
			$(td).append(')');

			Game.resources.printAvailableUnitCount(metal, stone, food, fuel, countSpan);

			$(td).click(function(){
				$(td).parent().children('.amount').children('input').val(countSpan.html());
			});

			$(td).css({
				'cursor': 'pointer',
				'text-decoration' : 'underline'
			});

		});
	}


};


$(document).ready(function(){
	Game.units.setMaximumAmount();
});

