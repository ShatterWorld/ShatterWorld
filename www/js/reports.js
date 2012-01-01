$(document).ready(function(){

	$(".report .reportBody").hide();

	$(".report .reportHead").click(function(){
		var report = $(this).parent();
		report.children(".reportBody").slideToggle("fast");

		var params = {'reportId': report.attr('id')};

		if (report.attr('class') == 'report unread'){
			Game.utils.signal('markRead', params);
			report.attr('class', 'report read');
		}
	});

});
