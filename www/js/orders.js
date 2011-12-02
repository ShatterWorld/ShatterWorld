var Game = Game || {};

Game.orders = {
	refresh: function ()
	{
		Game.utils.signal('fetchOrders', {}, function () {
			Game.orders.startCountdown();
		});
	},
	
	startCountdown: function ()
	{
		var timeout = $('#orderBar').data('nextorder');
		var countdownFunction = function () {
			if (timeout < 0){
				Game.orders.refresh();
				clearInterval(interval);
				return;
			}
			$('#orderTooltip .countdown').html(Game.utils.formatTime(timeout));
			timeout--;
		};
		var interval = setInterval(countdownFunction, 1000);
	}
}

$(document).ready(function () {
	Game.orders.startCountdown();
});