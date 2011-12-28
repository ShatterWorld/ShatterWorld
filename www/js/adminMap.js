var Game = Game || {};

Game.adminMap = {
	
	overlay: 'default',
	
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
		return (field['coordX'] * -3) + (field['coordY'] * 3) + 300;
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
		$.each(Game.adminMap.map, function (x, row) {
			$.each(row, function (y, field) {
				var element = $('<div>').css({
					position: 'absolute',
					left: Game.adminMap.calculateXPos(field),
					top: Game.adminMap.calculateYPos(field),
					background: Game.adminMap.getColor(field),
					width: 6,
					height: 4
				});
				field.element = element;
				$('#map').append(element);
			})
		});
	},
	
	fetchMap: function (success)
	{
		if (!success) success = function () {};
		Game.utils.signal('fetchMap', {}, function (data) {
			Game.adminMap.map = data.fields;
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