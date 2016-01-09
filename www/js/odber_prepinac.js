function toggleSubscription(subscribe, game_type_id) {
	var action, class_rem, class_add;
	if (subscribe) {
		action = 'subscribe';
		class_rem = 'panel-default';
		class_add = 'panel-info';
	} else {
		action = 'unsubscribe';
		class_rem = 'panel-info';
		class_add = 'panel-default';
	}
	var new_button = "<a class=\"btn btn-default " + (subscribe ? "un" : "") + "subscribe\" type=\"button\"><span class=\"glyphicon glyphicon-" + (subscribe ? "minus" : "plus") + "\"></span></a>";

	var xmlhttp = new XMLHttpRequest(),
		ajaxLink = $('#subToggleLink').attr('data-link').replace('~', action);
		

	xmlhttp.onreadystatechange = function () {
		if (xmlhttp.readyState === 4 && xmlhttp.status === 200) {
			var resp = xmlhttp.responseText;
			if (resp === 'false') {
				return;
			}
			var gp = $("#game_" + game_type_id);
			gp.removeClass(class_rem);
			gp.addClass(class_add);
			gp.find('.sub_user_count').html(resp);
			gp.find('.input-group-btn').html(new_button);

		}
	};
	xmlhttp.open("GET", ajaxLink + "&id=" + game_type_id, true);
	xmlhttp.send();
}


$(document).ready(function () {
	$(".game .input-group-btn").on('click', ".subscribe", function () {
		var game_type_id = $(this).parent(".input-group-btn").attr("id").split('_')[1];
		toggleSubscription(true, game_type_id);
	});

	$(".game .input-group-btn").on('click', ".unsubscribe", function () {
		var game_type_id = $(this).parent(".input-group-btn").attr("id").split('_')[1];
		toggleSubscription(false, game_type_id);
	});
});