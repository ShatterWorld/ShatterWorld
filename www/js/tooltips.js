$(document).ready(function () {
	var set = $('.hasTooltip');
	var getTooltipElement = function (selector) {
		var tooltip = $('.tooltip[data-for="' + $(selector).attr('id') + '"]');
		return !tooltip.data('fixed') ? tooltip : $();
	};
	$(set).live('mouseenter', function (e) {
		getTooltipElement(this).css({
			display: 'block',
			left: e.pageX + 10,
			top: e.pageY + 20
		});
	});
	$(set).live('mousemove', function (e) {
		getTooltipElement(this).css({
			left: e.pageX + 10,
			top: e.pageY + 20
		});
	});
	$(set).live('mouseleave', function (e) {
		getTooltipElement(this).css({
			display: 'none'
		});
	});
});