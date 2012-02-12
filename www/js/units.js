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
	 * The sum of costs
	 * @var JSON
	 */
	totalCosts : {},

	/**
	 * The total amount of free slots while init
	 * @var JSON
	 */
	totalAvailableSlots : {},

	/**
	 * The total amount of free slots right now
	 * @var JSON
	 */
	availableSlots : {},

	/**
	 * Sets the clickable maximal amounts of each unit
	 * @return void
	 */
	setMaximumAmount: function (){
		var maxTds = $('#trainUnitTable .max');

		$.each(maxTds, function(key, td) {
			var cost = $(td).parent().data()['cost'];
			var difficulty = $(td).parent().data()['difficulty'];
			var slots = Game.units.availableSlots;
			var amount = null;

			$.each(difficulty, function (slot, count) {
				var available = 0;
				if (Game.utils.isset(slots[slot])) {
					available = Math.floor(slots[slot] / count);
				}
				amount = Game.utils.isset(amount) ? Math.min(amount, available) : available;
			});
			$.each(cost, function (resource, count) {
				var myBalance = Game.resources.getBalance(resource);
				var available = 0;
				if (Game.utils.isset(Game.resources.getBalance(resource))) {
					if(count > 0){
						available = Math.max(0, Math.floor(Game.resources.getBalance(resource) / count));
						amount = Game.utils.isset(amount) ? Math.min(amount, available) : available;
					}
				}
			});
			$(td).html('(' + (Game.utils.isset(amount) ? amount : 0) + ')');
			$(td).click(function(){
				$(td).parent().children('.amount').children('input').val(Game.utils.isset(amount) ? amount : 0);
				Game.units.inputChange();
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
			$.each($(td).parent().data()['cost'], function (resource, cost) {
				if (!Game.utils.isset(Game.units.totalCosts[resource])) {
					Game.units.totalCosts[resource] = 0;
				}
				Game.units.totalCosts[resource] += $(td).children('input').val() * cost;
			});
		});
	},

	/**
	 * Calculates total used slots
	 * @return void
	 */
	handleTotalSlots: function (){
		this.availableSlots = Game.utils.clone(this.totalAvailableSlots);
		$.each($('#trainUnitTable .unit'), function(unitKey, tr){

			var difficulty = $(tr).data()['difficulty'];

			$.each(difficulty, function(key, diff){
				if (Game.utils.isset(Game.units.availableSlots[key])){
					Game.units.availableSlots[key] -= $(tr).children('.amount').children('input').val() * diff;
				}
			});
		});
	},

	/**
	 * Prints total costs
	 * @return void
	 */
	printTotalCosts: function (){
		$.each(this.totalCosts, function(key, res){
			$('#resSum #'+key).html(res);
		});
	},

	/**
	 * Prints slots info
	 * @return void
	 */
	printTotalSlots: function (){
		$.each(this.availableSlots, function(key, slotCount){
			$('#slotSum #'+key+' .used').html(Game.units.totalAvailableSlots[key] - slotCount);
			$('#slotSum #'+key+' .remains').html(slotCount);
		});
	},

	/**
	 * Disables/enables submit button
	 * @return void
	 */
	handleSubmit : function(){
		if (Game.resources.hasSufficientResources(Game.units.totalCosts) && this.hasSufficientSlots()){
			document.forms['frm-trainUnitForm'].elements['send'].disabled = false;
		}
		else{
			document.forms['frm-trainUnitForm'].elements['send'].disabled = true;
		}
	},

	/**
	 * True if the clan has enough slots, false otherwise
	 * @return bool
	 */
	hasSufficientSlots : function(){
		var ret = true;
		$.each(this.availableSlots, function(key, actSlot){
			if(actSlot < 0){
				ret = false;
				return false;
			}
		});
		return ret;
	},

	inputChange : function(){
		this.handleTotalCosts();
		this.handleTotalSlots();

		this.printTotalCosts();
		this.printTotalSlots();

		this.handleSubmit();
	}

};


$(document).ready(function(){
	Game.units.totalAvailableSlots = totalSlots;
	Game.units.availableSlots = totalSlots;
	Game.units.setMaximumAmount();

	$('#trainUnitTable .amount input').keypress(function(e) {
		if(e.which != 0/* && e.which != 8*/){
			e.preventDefault();
		}

		if(e.which == 8){
			$(this).val($(this).val().substr(0, $(this).val().length - 1));
		}

		if (e.which == 0 || e.which == 8 || e.which < 48 || e.which > 57){
			Game.units.inputChange();
			return;
		}

		$(this).val($(this).val() + String.fromCharCode(e.which));
		Game.units.inputChange();
	});



});

