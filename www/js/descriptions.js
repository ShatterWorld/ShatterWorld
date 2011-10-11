jQuery.extend({
	descriptions: {
		data: null,

		period : 250,

		fetching : false,

		fetchDescriptions: function () {
			this.fetching = true;
			$.get('?do=fetchDescriptions', function (data) {
				jQuery.descriptions.data = data['descriptions'];
				jQuery.descriptions.fetching = false;
			})
		},
/*
	div = $('<div id = "militia" />');
	get('unit','militia',div);

		get: function (type, key, object) {
			this.data.addFetchedLstener(function(e){

				var res;
	
				if (jQuery.descriptions.data !== null && typeof(jQuery.descriptions.data[type][key]) !== 'undefined') {
					res = jQuery.descriptions.data[type][key];
				}
				else {
					res =  key;
				}

				object.html(res);
			});
		},
*/
		get: function (type, key) {
			/*while (this.fetching){
				this.wait();
			}*/

/*
			var res = this.delayedExecution(
				this.period,
				function (){
					return jQuery.descriptions.data != null ? true : false;
				},*/
				/*
				function (type, key) {
					//alert('data '+jQuery.descriptions.data+'; type '+type+'; key '+key);
					if (jQuery.descriptions.data !== null && typeof(jQuery.descriptions.data[type][key]) !== 'undefined') {
						return jQuery.descriptions.data[type][key];
					} else {
						return key;
					}
				},*/
				/*
				jQuery.descriptions.innerGet,
				type,
				key
				);

			alert(res);
			return res;*/


			if (jQuery.descriptions.data !== null && typeof(jQuery.descriptions.data[type][key]) !== 'undefined') {
				return jQuery.descriptions.data[type][key];
			} else {
				return key;
			}

		},

		delayedExecution : function(delay, condition, fnc, type, key)
		{
			var interval = setInterval(function () {
				//alert('delay');
				if(condition()) {
					clearInterval(interval);
					return fnc(type, key);
				}
			}, delay);
		},



		innerGet: function (type, key) {
			if (jQuery.descriptions.data !== null && typeof(jQuery.descriptions.data[type][key]) !== 'undefined') {
				return jQuery.descriptions.data[type][key];
			} else {
				return key;
			}

		},
/*
		wait : function(time, type){
			time = time || 1000;
			type = type || "fx";
			return this.queue(type, function() {
				var self = this;
				setTimeout(function() {
					$(self).dequeue();
				}, time);
			});



		}*/


	}
});

$(document).ready(function () {
	jQuery.descriptions.fetchDescriptions();
});
