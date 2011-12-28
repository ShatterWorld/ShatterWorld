var Game = Game || {};

Game.adminMap = {

	overlay: 'default',

	mapSize : 0,

	/**
	 * Calculates somehow x-position of the field
	 * @param field
	 * @return integer
	 */
	calculateXPos : function (field)
	{
		return (field['coordX'] * 7) + (field['coordY'] * 7);
	},

	/**
	 * Calculates somehow y-position of the field
	 * @param field
	 * @return integer
	 */
	calculateYPos : function (field)
	{
		return (field['coordX'] * -3) + (field['coordY'] * 3);
	},

	getColor: function (field)
	{
		switch (this.overlay) {
			case 'rank':
				if (field.coordX == this.maxRank.x && field.coordY == this.maxRank.y) {
					return '#ff0000';
				}
				if (field.owner) {
					return '#0000ff';
				}
				if (Game.utils.isset(this.ranks[field.coordX]) && Game.utils.isset(this.ranks[field.coordX][field.coordY])) {
					var rank = this.ranks[field.coordX][field.coordY];
					var color = Math.floor((rank) * 255).toString(16);
					if (color.length < 2) {color = '0' + color};
					return '#' + color + color + '00';
				} else {
					return '#cdcdcd';
				}
			default:
				if (field.owner) {
					return '#0000ff';
				} else {
					return '#ffffff'
				}
		}
		return '#00ff00';
	},

	render: function ()
	{
		this.fetchMap(function () {
			Game.adminMap.repaint();
		});

	},

	repaint: function ()
	{
		var max = Game.adminMap.mapSize
		var fieldWidth = 6;
		var fieldHeight = 4;

		var S = Game.adminMap.map[max/2][max/2]; //center field
		var xPosS = Game.adminMap.calculateXPos(S);
		var yPosS = Game.adminMap.calculateYPos(S);

		var dX = xPosS - 2*fieldWidth  - parseInt($('#mapContainer').css('width')) / 2;
		var dY = yPosS - 2*fieldHeight - parseInt($('#mapContainer').css('height'))/2;

		$('#map').html('');
		$.each(Game.adminMap.map, function (x, row) {
			$.each(row, function (y, field) {
				var element = $('<div>').css({
					position: 'absolute',
					left: Game.adminMap.calculateXPos(field) - dX,
					top: Game.adminMap.calculateYPos(field) - dY,
					background: Game.adminMap.getColor(field),
					width: fieldWidth,
					height: fieldHeight
				});
				field.element = element;
				$('#map').append(element);
			})
		});

		$('#mapContainer').scrollLeft(dX + fieldWidth);
		$('#mapContainer').scrollTop(sY + fieldHeight);

	},

	fetchMap: function (success)
	{
		if (!success) success = function () {};
		Game.utils.signal('fetchMap', {}, function (data) {
			Game.adminMap.map = data.fields;
			Game.adminMap.mapSize = data.mapSize;
			Game.adminMap.ranks = data.ranks;
			Game.adminMap.maxRank = data.maxRank;
			success(data);
		});
	}
}

$(document).ready(function () {
	$('#frmswitchOverlayForm-overlay').change(function () {
		Game.adminMap.overlay = $(this).val();
		Game.adminMap.repaint();
	});
	Game.adminMap.render();
})
