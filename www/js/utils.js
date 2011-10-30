/**
 * Utils
 * @author Petr Bělohlávek
 */

/**
 * Global functions
 */
var Game = Game || {};
Game.utils = {
	/**
	 * Send a signal to a Nette app
	 * @param string
	 * @param Object
	 * @param function
	 * @return void
	 */
	signal: function (signal, params, success)
	{
		params = this.isset(params) ? params : {};
		success = this.isset(success) ? success : function () {};
		$.get('?' + $.param($.extend({'do': signal}, params)), function (data) {
			jQuery.nette.success(data);
			success(data);
		});
	},
	
	/**
	 * Calculates local position
	 * @param object
	 * @param integer
	 * @param integer
	 * @return array of integer
	 */
	globalToLocal: function (context, globalX, globalY)
	{
		var position = context.offset();

		return({
			x: Math.floor( globalX - position.left ),
			y: Math.floor( globalY - position.top )
		});
	},

	/**
	 * Calculates global position
	 * @param object
	 * @param integer
	 * @param integer
	 * @return array of integer
	 */
	localToGlobal : function(context, localX, localY){
		var position = context.offset();

		return({
			x: Math.floor( localX + position.left ),
			y: Math.floor( localY + position.top )
		});
	},

	/**
	 * Returns true if the variable isnt undefined or null
	 * @param object
	 * @return boolean
	 */
	isset: function (variable)
	{
		return (typeof(variable) !== 'undefined' && variable !== null && variable != null);
	},

	/**
	 * Returns the clone of the array/obejct given
	 * @param object/array
	 * @return object/array
	 */
	clone: function (variable)
	{
		if (variable instanceof Array) {
			return variable.slice();
		} else if (variable instanceof Object) {
			return jQuery.extend({}, variable);
		} else {
			return variable;
		}
	}


};
