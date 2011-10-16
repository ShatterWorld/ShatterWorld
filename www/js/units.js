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
	totalCosts : {},

	/**
	 * Sets the clickable maximal amounts of each unit
	 * @return void
	 */
	setMaximumAmount: function (){
		var maxTds = $('#trainUnitTable .max');

		$.each(maxTds, function(key, td){
			var cost = $(td).parent().data()['costs'];
			var countSpan = $('<span />').html('');
			$(td).html('(');
			$(td).append(countSpan);
			$(td).append(')');

			Game.resources.printAvailableUnitCount(cost, countSpan);

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
		this.totalCosts = {};
		$.each(maxTds, function(key, td){
			$.each($(td).parent().data()['costs'], function (resource, cost) {
				if (!Game.utils.isset(Game.units.totalCosts[resource])) {
					Game.units.totalCosts[resource] = 0;
				}
				Game.units.totalCosts[resource] += $(td).children('input').val() * cost;
			});

		});

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

	},

	/**
	 * Disables/enables submit button
	 * @return void
	 */
	handleSubmit : function(){
		if (!Game.resources.hasSufficientResources(Game.units.totalCosts)){
			document.forms['frm-trainUnitForm'].elements['send'].disabled = true;
		}
		else{
			document.forms['frm-trainUnitForm'].elements['send'].disabled = false;
		}
	}
};


$(document).ready(function(){
	Game.units.setMaximumAmount();

	$('#trainUnitTable .amount input').change(function() {
		Game.units.handleTotalCosts();
		Game.units.printTotalCosts();
		Game.units.handleSubmit();
	});

	$('#trainUnitTable .amount input').keyup(function(e) {
		if (e.keyCode < 96 || e.keyCode > 105){
			return;
		}
		$(this).change();
	});



});

