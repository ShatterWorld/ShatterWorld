var Game = Game || {};
Game.descriptions = {
	/**
	 * Translated descriptions
	 * @var JSON
	 */
	data: null,

	/**
	 * Stack of waiting trans. functions
	 * @var array of functions
	 */
	callbackStack : new Array(),

	/**
	 * True if fetching is in progress, false otherwise
	 * @var boolean
	 */
	isFetching : true,

	/**
	 * Fetches descriptions
	 * @return void
	 */
	fetchDescriptions : function () {
		this.isFetching=true;
		$.get('?do=fetchDescriptions',
			function (data) {
				Game.descriptions.data = data['descriptions'];
				Game.descriptions.isFetching = false;

				while (fnc = Game.descriptions.callbackStack.pop()){
					fnc();
				}


			});
	},

	/**
	 * Translates key phrase of type type and prints it to the selector
	 * @param string
	 * @param string
	 * @param object/string
	 * @return void
	 */
	translate : function (type, key, selector, attr)
	{
		var callback = function () {
			var trans = Game.descriptions.data[type][key];
			if (Game.utils.isset(trans) && trans !== 'undefined') {
				if (attr) {
					$(selector).attr(attr, trans);
				} else {
					$(selector).html(trans);
				}
			} else {
				if (attr) {
					$(selector).attr(attr, key);
				} else {
					$(selector).html(key);
				}
			}
		};
		if (this.isFetching || this.data == null){
			this.callbackStack.push(callback);
		} else {
			callback();
		}


	},

};

$(document).ready(function () {
	Game.descriptions.fetchDescriptions();
});
