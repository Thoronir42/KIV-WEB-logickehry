$duration = 350;

function showEdit(id, showEdit) {
	var edit = $(id).find('.grp-edit');
	var info = $(id).find('.grp-info');
	
	if (showEdit) {
		edit.animate({
			left: 0
		}, $duration );
		info.animate({
			left: parseInt(-info.outerWidth()),
		}, $duration );
	} else {
		edit.animate({
			left: parseInt(edit.outerWidth()),
		}, $duration );
		info.animate({
			left: parseInt(0),
		}, $duration );
	}
}

$(document).ready(function () {
	var edit = $(".grp-info");
	edit.css("left", parseInt(-edit.outerWidth()));

	$(".butt-edit").click(function () {
		var id = "#" + $(this).parents(".game").attr("id");
		showEdit(id, true);
	});

	$(".butt-cancel").click(function () {
		var id = "#" + $(this).parents(".game").attr("id");
		showEdit(id, false);
	});


	$(".butt-submit").click(function () {
		var panel = $(this).parents(".game");

		var xmlhttp = new XMLHttpRequest();
		xmlhttp.onreadystatechange = function () {
			if (xmlhttp.readyState === 4 && xmlhttp.status === 200) {
				var resp = xmlhttp.responseText;
				if (resp === 'true') {
					location.reload();
					return;
				} else {
					var resp_parts = resp.split(";");
					var id = resp_parts[0],
							message = resp_parts[1];
					var popover = $("#ai_" + id).attr('data-content', message).data('bs.popover');
					popover.setContent();
					popover.$tip.addClass(popover.options.placement);
				}
			}
		};
		xmlhttp.open("GET", "?controller=ajax&action=insertBox&code=" + game_code + "&gameId=" + game_id, true);
		xmlhttp.send();
	});

	$('[data-toggle="popover"]').popover();
	$('[data-toggle="tooltip"]').tooltip();
});