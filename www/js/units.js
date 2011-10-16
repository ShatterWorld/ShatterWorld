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
	 * Total costs
	 * @var JSON
	 */
	totalCosts : {metal : 0, stone : 0, food : 0, fuel : 0},

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
				$(td).parent().children('.amount').children('input').change();
			});

			$(td).css({
				'cursor': 'pointer',
				'text-decoration' : 'underline'
			});

		});
	},

	/**
	 * Calculates total costs
	 * @return void
	 */
	handleTotalCosts: function (){
		var maxTds = $('#trainUnitTable .amount');

		var metal = 0;
		var stone = 0;
		var food = 0;
		var fuel = 0;
		$.each(maxTds, function(key, td){
			metal += $(td).parent().data()['costs'][0] * $(td).children('input').val();
			stone += $(td).parent().data()['costs'][1] * $(td).children('input').val();
			food += $(td).parent().data()['costs'][2] * $(td).children('input').val();
			fuel += $(td).parent().data()['costs'][3] * $(td).children('input').val();

		});

		this.totalCosts['metal'] = metal;
		this.totalCosts['stone'] = stone;
		this.totalCosts['food'] = food;
		this.totalCosts['fuel'] = fuel;

		if (!Game.resources.hasSufficientResources(Game.units.totalCosts)){
			document.forms['frm-trainUnitForm'].elements['send'].disabled = true;
		}
		else{
			document.forms['frm-trainUnitForm'].elements['send'].disabled = false;
		}
	},

	/**
	 * Prints total costs
	 * @return void
	 */
	printTotalCosts: function (){
		$('#resSum #metal').html(this.totalCosts['metal']);
		$('#resSum #stone').html(this.totalCosts['stone']);
		$('#resSum #food').html(this.totalCosts['food']);
		$('#resSum #fuel').html(this.totalCosts['fuel']);

	}
};


$(document).ready(function(){
	Game.units.setMaximumAmount();

	$('#trainUnitTable .amount input').change(function() {
		Game.units.handleTotalCosts();
		Game.units.printTotalCosts();
	});

	/*$('#trainUnitTable .amount input').keypress(function(e) {
		if (e.which < 48 || e.which > 57){
			return;
		}
		//alert(e.which);
		$(this).change();
	});*/



});

