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
	 * @var array of int
	 */
	totalAvailableSlots : null,

	/**
	 * The total amount of free slots right now
	 * @var array of int
	 */
	availableSlots : null,

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

			var slots = $(td).parent().data()['difficulty'];
			Game.resources.printAvailableUnitCount(cost, Game.units.totalCosts, slots, Game.units.availableSlots, countSpan);

			$(td).click(function(){
				$(td).parent().children('.amount').children('input').val(countSpan.html());

				$('#slotDiv').append('<div>max change</div>');//!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!

				//$(td).parent().children('.amount').children('input').change();
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
			$.each($(td).parent().data()['costs'], function (resource, cost) {
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

		this.availableSlots = this.totalAvailableSlots;

		$.each($('#trainUnitTable .unit'), function(key, tr){

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
		$.each(this.availableSlots, function(key, res){
			$('#slotDiv').append('<div>available: '+key+': '+res+'</div>');
		});


	},

	/**
	 * Disables/enables submit button
	 * @return void
	 */
	handleSubmit : function(){
		if (!Game.resources.hasSufficientResources(Game.units.totalCosts) || !this.hasSufficientSlots()){
			document.forms['frm-trainUnitForm'].elements['send'].disabled = true;
		}
		else{
			document.forms['frm-trainUnitForm'].elements['send'].disabled = false;
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
		$('#slotDiv').append('<div>own change</div>');//!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
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

	/*$('#trainUnitTable .amount input').change(function() {
		$('#slotDiv').append('<div>own change</div>');//!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
		Game.units.handleTotalCosts();
		Game.units.handleTotalSlots();

		Game.units.printTotalCosts();
		Game.units.printTotalSlots();

		Game.units.handleSubmit();
	});*/

	$('#trainUnitTable .amount input').keypress(function(e) {
		$('#slotDiv').append('<div>keyup</div>');//!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
		if (e.keyCode < 96 || e.keyCode > 105){
			//return;
		}
		//$(this).change();
		Game.units.inputChange();
	});



});

