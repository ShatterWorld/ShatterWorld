$(document).ready(function () {
	$('#frmchooseClanForm-clan').change(function () {
		Game.utils.signal('switchClan', {clanId: $(this).val()});
	})
});