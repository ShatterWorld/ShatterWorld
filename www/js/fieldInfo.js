/**
 * FieldInfo
 * @author Petr Bělohlávek
 */

/**
 * Global functions
 */
var Game = Game || {};
Game.fieldInfo = {
	/**
	 * @var object represents field info
	 */
	fieldInfo :  $('<div id="fieldInfo" />')
		.append('<table>')

		.append('<tr>')
		.append('<th>Souřadnice</th><td id="coords"></td>')
		.append('</tr>')

		.append('<tr>')
		.append('<th>Typ</th><td id="type"></td>')
		.append('</tr>')

		.append('<tr>')
		.append('<th>Vlastník</th><td id="owner"></td>')
		.append('</tr>')

		.append('<tr>')
		.append('<th>Aliance</th><td id="alliance"></td>')
		.append('</tr>')

		.append('<tr>')
		.append('<th>Budova</th><td id="facility"></td>')
		.append('</tr>')

		.append('<tr>')
		.append('<th>Úroveň</th><td id="level"></td>')
		.append('</tr>')

		.append('</table>')

		.css({
			'background' : '#5D6555',
			'color' : 'white',
			'border' : '1px solid white',
			'padding' : '3px',
			'min-width' : '180px',
			'position' : 'absolute',
			'z-index' : '99999999999999999',
			'display' : 'none',
			'-ms-filter' : "progid:DXImageTransform.Microsoft.Alpha(Opacity=90)",
			'-moz-opacity' : '0.9',
			'opacity' : '0.9'
	}),

	/**
	 * Displays the info
	 * @return void
	 */
	show : function(){
		this.fieldInfo.show();
	},

	/**
	 * Hides the info
	 * @return void
	 */
	hide : function(){
		this.fieldInfo.hide();
	},

	/**
	 * Positions the info
	 * @param integer
	 * @param integer
	 * @return void
	 */
	position : function(left, top){
		$('#fieldInfo').css("left", left + 'px');
		$('#fieldInfo').css("top", top + 'px');
	},

	/**
	 * Appends the info to the target
	 * @param object/strng
	 * @return void
	 */
	append : function(target){
		$(target).append(this.fieldInfo);
	}
};
