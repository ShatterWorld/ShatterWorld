/**
* Navigation in the map
* @author Petr Bělohlávek
*/
$(document).ready(function(){

	/**
	 * @var integer represents speed of navigation [ms]
	 */
	var navigSpeed = 400;

	/**
	 * @var integer how far the scroller moves [px]
	 */
	var slide = 250;

	/**
	 * Scrolls map when user uses the navigation arrows
	 *
	 */
	$('#navig #up').click(function(){
			var pos = $('#mapContainer').scrollTop();
			$('#mapContainer').animate({scrollTop: pos-slide}, navigSpeed);
	});
	$('#navig #down').click(function(){
			var pos = $('#mapContainer').scrollTop();
			$('#mapContainer').animate({scrollTop: pos+slide}, navigSpeed);
	});

	$('#navig #left').click(function(){
			var pos = $('#mapContainer').scrollLeft();
			$('#mapContainer').animate({scrollLeft: pos-slide}, navigSpeed);
	});
	$('#navig #right').click(function(){
			var pos = $('#mapContainer').scrollLeft();
			$('#mapContainer').animate({scrollLeft: pos+slide}, navigSpeed);
	});

	$('#navig #leftup').click(function(){
			var posTop = $('#mapContainer').scrollTop();
			$('#mapContainer').animate({scrollTop: posTop-slide}, navigSpeed);
			var posLeft = $('#mapContainer').scrollLeft();
			$('#mapContainer').animate({scrollLeft: posLeft-slide}, navigSpeed);
	});
	$('#navig #rightup').click(function(){
			var posTop = $('#mapContainer').scrollTop();
			$('#mapContainer').animate({scrollTop: posTop-slide}, navigSpeed);
			var posLeft = $('#mapContainer').scrollLeft();
			$('#mapContainer').animate({scrollLeft: posLeft+slide}, navigSpeed);
	});

	$('#navig #leftdown').click(function(){
			var posTop = $('#mapContainer').scrollTop();
			$('#mapContainer').animate({scrollTop: posTop+slide}, navigSpeed);
			var posLeft = $('#mapContainer').scrollLeft();
			$('#mapContainer').animate({scrollLeft: posLeft-slide}, navigSpeed);
	});
	$('#navig #rightdown').click(function(){
			var posTop = $('#mapContainer').scrollTop();
			$('#mapContainer').animate({scrollTop: posTop+slide}, navigSpeed);
			var posLeft = $('#mapContainer').scrollLeft();
			$('#mapContainer').animate({scrollLeft: posLeft+slide}, navigSpeed);
	});

});
