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

	formatTime: function (time)
	{
		var h = Math.floor(time / 3600);
		time -= h*3600;
		var m = Math.floor(time / 60);
		time -= m*60;
		var s = time;

		if (s < 10){
			s = '0' + s;
		}
		if (m < 10){
			m = '0' + m;
		}

		return h + ':' + m + ':' + s;
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

var Class = function (def) 
{
	var constructor = def.hasOwnProperty('constructor') ? def.constructor : function () { };
	for (var name in Class.Initializers) {
		Class.Initializers[name].call(constructor, def[name], def);
	}
	return constructor;
};

Class.Initializers = {
	extends: function (parent) {
		if (parent) {
			var F = function () { };
			this._superClass = F.prototype = parent.prototype;
			this.prototype = new F;
		}
	},
	
	mixins: function(mixins, def) {
		this.mixin = function (mixin) {
			for (var key in mixin) {
				if (key in Class.Initializers) continue;
				this.prototype[key] = mixin[key];
			}
			this.prototype.constructor = this;
		};
		var objects = [def].concat(mixins || []);
		for (var i = 0, l = objects.length; i < l; i++) {
			this.mixin(objects[i]);
		}
    }
};