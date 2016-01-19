$duration = 350;

function showEdit(id, showEdit) {
	var edit = $(id).find('.grp-edit');
	var info = $(id).find('.grp-info');

	edit.html($("#edit_form").html());
	edit.find("input[type=checkbox]").tooltip();


	edit.find("input").each(function () {
		var name = $(this).attr("name");
		if (name === "picture") {
			return false;
		}
		$(this).attr("value", $(id).find("." + name).html());


	});

	if (showEdit) {
		edit.animate({
			left: 0
		}, $duration);
		info.animate({
			left: parseInt(-info.outerWidth()),
		}, $duration);
	} else {
		edit.animate({
			left: parseInt(edit.outerWidth()),
		}, $duration);
		info.animate({
			left: parseInt(0),
		}, $duration);
	}
}

$(document).ready(function () {
	var edit = $(".grp-edit");
	edit.css("left", parseInt(edit.outerWidth()));

	$(".game").on('click', ".butt-edit", function () {
		var id = "#" + $(this).parents(".game").attr("id");
		showEdit(id, true);
	});

	$(".game").on('click', ".butt-cancel", function () {
		var id = "#" + $(this).parents(".game").attr("id");
		showEdit(id, false);
	});

	$(document).on('fileselect', '.btn-file :file', function (event, numFiles, label) {
		$(this).parents('.input-group').find(':checkbox').prop('checked', false);
	});
	
	$(".tt_totBox").attr('data-toggle', 'tooltip');
	$(".tt_totBox").attr('title', 'Počet evidenčních kódů kdy vedených u této hry.');
	
	$(".tt_actBox").attr('data-toggle', 'tooltip');
	$(".tt_actBox").attr('title', 'Počet aktivních evidenčních kódů.');

	$('[data-toggle="tooltip"]').tooltip();
});