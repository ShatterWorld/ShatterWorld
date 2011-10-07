jQuery.extend({
	descriptions: {
		data: null,

		period : 250,

		fetchDescriptions: function () {
			$.get('?do=fetchDescriptions', function (data) {
				jQuery.descriptions.data = data['descriptions'];
			})
		},

		get: function (type, key) {
			if (this.data === null){
				setTimeout(function(){
					return jQuery.descriptions.get(type, key);
				}, jQuery.descriptions.period);
			}

			if (typeof(this.data[type][key]) !== 'undefined') {
				return this.data[type][key];
			} else {
				return key;
			}


		}
	}
});

$(document).ready(function () {
	jQuery.descriptions.fetchDescriptions();
});
