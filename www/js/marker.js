/**
 * Marker
 * @author Petr Bělohlávek
 */

/**
 * Global functions
 */
jQuery.extend({
	marker: {
		/**
		 * Number of marked fields
		 * @var integer
		 */
		markedFields : 0,

		/**
		 * Returns object representing the marker
		 * @return object
		 */
		getMarkerImage : function () {return $('<img class="marker" />').attr('src', this.getBasepath() + '/images/fields/marker.png');},

		/**
		 * The size of the marker
		 * @var integer
		 */
		size : 5,

		mark : function (field, color) {
			$(field).drawEllipse(this.size, this.size, jQuery.gameMap.fieldWidth-2*this.size, jQuery.gameMap.fieldHeight-2*this.size, {color: color, stroke: this.size});
			$(field).attr('class', 'markedField'+color);
			/*todo:
			 * rozdělit dle barev
			 * */
		},

		/**
		* Unmarks all fields and sets click to zero
		* @return void
		*/
		unmarkAll : function(color){
			$('.markedField'+color+' canvas').remove();
			$('.markedField'+color).attr('class', 'field');
			this.markedFields = 0;
			this.initialField = null;
		},


	}
});
