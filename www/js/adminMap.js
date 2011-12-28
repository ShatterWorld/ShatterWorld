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
		return (field['coordX'] * -2) + (field['coordY'] * 2);
	},
	
	getColor: function (field)
	{
// 		switch (this.overlay) {
// 			case 'rank':
// 				var color = (this.ranks[field.coordX][field.coordY] * 255).toString(16)
// 				return '#';
// 		}
		return '#00ff00';
	},
	
	render: function ()
	{
		this.fetchMap(function () {
			$.each(Game.adminMap.map, function (x, row) {
				$.each(row, function (y, field) {
					$('#map').append($('<div>').css({
						position: 'absolute',
						left: Game.adminMap.calculateXPos(field),
						top: Game.adminMap.calculateYPos(field),
						background: Game.adminMap.getColor(field),
						width: 6,
						height: 4
					}));
				})
			});
		});
		
	},
	
	fetchMap: function (success)
	{
		if (!success) success = function () {};
		Game.utils.signal('fetchMap', {}, function (data) {
			Game.adminMap.map = data.fields;
			Game.adminMap.ranks = data.ranks
			success(data);
		});
	}
}

$(document).ready(function () {
	Game.adminMap.render();
})