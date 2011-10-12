jQuery.extend({
	descriptions: {
		/**
		 * Translated descriptions
		 * @var JSON
		 */
		data: null,

		/**
		 * Stack of waiting trans. functions
		 * @var array of functions
		 */
		describeFunctions : new Array(),

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
					jQuery.descriptions.data = data['descriptions'];
					jQuery.descriptions.isFetching = false;

					while (fnc = jQuery.descriptions.describeFunctions.pop()){
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
		translate : function (type, key, selector){
			if (this.isFetching || this.data == null){
				this.describeFunctions.push(function(){
					var trans = jQuery.descriptions.data[type][key];
					if (typeof(trans) !== 'undefined' && trans !== 'undefined') {
						$(selector).html(trans);
					} else {
						$(selector).html(key);
					}
				});

			}
			else{
				var trans = jQuery.descriptions.data[type][key];
				if (typeof(trans) !== 'undefined' && trans !== 'undefined') {
					$(selector).html(trans);
				} else {
					$(selector).html(key);
				}

			}


		},

		/**
		 * Returns the translated key phrase
		 * @deprecated
		 * @param string
		 * @param string
		 * @return string
		 */
		get: function (type, key) {
			if (jQuery.descriptions.data !== null && typeof(jQuery.descriptions.data[type][key]) !== 'undefined') {
				return jQuery.descriptions.data[type][key];
			} else {
				return key;
			}

		},



	}
});

$(document).ready(function () {
	jQuery.descriptions.fetchDescriptions();
});
