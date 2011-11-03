/**
 * Spinner
 * @author Petr Bělohlávek
 */

/**
 * Global functions
 */
var Game = Game || {};
Game.spinner = {
	/**
	 * Size of the spinner
	 * @var integer
	 */
	size : 31,

	/**
	 * Returns object representing the spinner
	 * @return object
	 */
	getSpinner : function () {
		return $('<img class="spinner" />').attr('src', basePath + '/images/spinner.gif').css({
		'position' : 'absolute',
		'z-index' : '999999999999999999999999999999999999999999999999999999999999'});
	},

	/**
	 * Shows spinner
	 * @param string/object
	 * @return void
	 */
	show : function (target)
	{
		var spinnerClone = this.getSpinner().clone();

		var targetWidth = $(target).css('width');
		var targetHeight = $(target).css('height');

		spinnerClone.css({
			'top' : targetHeight.substring(0, targetHeight.length -2)/2 - this.size/2 + 'px',
			'left' : targetWidth.substring(0, targetWidth.length -2)/2 - this.size/2 + 'px'
		});
		$(target).append(spinnerClone);
	},

	/**
	 * Hides spinner
	 * @return void
	 */
	hide : function ()
	{
		$('.spinner').remove();
	},

};