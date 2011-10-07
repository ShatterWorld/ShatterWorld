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

		get: function (type, key) {
			/*while (this.fetching){
				this.wait();
			}*/

			if (this.data !== null && typeof(this.data[type][key]) !== 'undefined') {
				return this.data[type][key];
			} else {
				return key;
			}

		},

		wait : function(time, type){
			time = time || 1000;
			type = type || "fx";
			return this.queue(type, function() {
				var self = this;
				setTimeout(function() {
					$(self).dequeue();
				}, time);
			});
		}


/*    $.fn.wait = function(time, type) {
        time = time || 1000;
        type = type || "fx";
        return this.queue(type, function() {
            var self = this;
            setTimeout(function() {
                $(self).dequeue();
            }, time);
        });
    };
    function runIt() {
      $("div").wait()
              .animate({left:'+=200'},2000)
              .wait()
              .animate({left:'-=200'},1500,runIt);
    }
    runIt();*/



	}
});

$(document).ready(function () {
	jQuery.descriptions.fetchDescriptions();
});
