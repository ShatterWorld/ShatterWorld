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
	 * Calculates relative position
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
	}


};
