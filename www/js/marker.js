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

		mark : function (field) {
			$(field).drawEllipse(this.size, this.size, jQuery.gameMap.fieldWidth-2*this.size, jQuery.gameMap.fieldHeight-2*this.size, {color: 'red', stroke: this.size});
			$(field).attr('class', 'markedField');
			/*todo:
			 * rozdělit dle barev
			 * */
		},

		/**
		* Unmarks all fields and sets click to zero
		* @return void
		*/
		unmarkAll : function(){
			$('.markedField canvas').remove();
			$('.markedField').attr('class', 'field');
			this.markedFields = 0;
			this.initialField = null;
		},


	}
});
