$(document).ready(function() {
	$(".retire").click(function(){
		var game_code = $(this).parent().parent(".input-group").children("input").attr("value");
		var xmlhttp = new XMLHttpRequest(),
			ajaxLink = $('#retireBoxLink').attr('data-link');
		xmlhttp.onreadystatechange = function() {
			if (xmlhttp.readyState === 4 && xmlhttp.status === 200) {
				var resp = xmlhttp.responseText;
				
				if(resp !== 'false'){
					$("#ig_"+resp).remove();
				}
			}
		};
		xmlhttp.open("GET", ajaxLink + "?code=" + game_code, true);
		xmlhttp.send();
	});
	
	$(".insert").click(function(){
		var inputText = $(this).parent().parent(".input-group").find('input[type=text]');
		var game_id = inputText.attr('name').split("_")[1],
			game_code = inputText.val(),
			ajaxLink = $('#insertBoxLink').attr('data-link');
		var xmlhttp = new XMLHttpRequest();
        xmlhttp.onreadystatechange = function() {
			if (xmlhttp.readyState === 4 && xmlhttp.status === 200) {
				var resp = xmlhttp.responseText;
				if(resp === 'true'){ location.reload(); return; } else {
					var resp_parts = resp.split(";");
					var id = resp_parts[0],
						message = resp_parts[1];
					var popover = $("#ai_"+id).attr('data-content',message).data('bs.popover');
					popover.setContent();
					popover.$tip.addClass(popover.options.placement);
				}
			}
		};
		xmlhttp.open("GET", ajaxLink+"?code=" + game_code + "&gameId=" + game_id, true);
		xmlhttp.send();
	});
	
	$('[data-toggle="popover"]').popover();
	$('[data-toggle="tooltip"]').tooltip();
});