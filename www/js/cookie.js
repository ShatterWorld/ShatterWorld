/**
 * Cookie
 * @author Petr Bělohlávek
 */

/**
 * Global functions
 */
jQuery.extend({
	cookie: {
		/**
		 * Adds new cookie
		 * @param string
		 * @param string
		 * @param string
		 * @return void
		 */
		set : function (name, value, days) {
			if (days) {
				var date = new Date();
				date.setTime(date.getTime() + (days*24*60*60*1000));
				var expires = "; expires="+date.toGMTString();
			}
			else {
				var expires = "";
			}

			document.cookie = name + "=" + value + expires + "; path=/";
		},

		/**
		 * Returns the specified cookie
		 * @param string
		 * @return string
		 */
		get : function (name) {
			var nameEQ = name + "=";
			var ca = document.cookie.split(';');
			for(var i=0; i < ca.length; i++) {
				var c = ca[i];
				while (c.charAt(0) == ' ') c = c.substring(1, c.length);
				if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length,c.length);
			}
			return null;
		},

		/**
		 * Deletes the specified cookie
		 * @param string
		 * @return void
		 */
		free : function (name) {
			this.set(name, "", -1);
		}

	}
});


