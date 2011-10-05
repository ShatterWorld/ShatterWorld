jQuery.extend({
	descriptions: {
		data: null,
		
		fetchDescriptions: function () {
			$.get('?do=fetchDescriptions', function (data) {
				jQuery.descriptions.data = data['descriptions'];
			})
		},
		
		get: function (type, key) {
			if (this.data !== null && typeof(this.data[type][key]) !== 'undefined') {
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