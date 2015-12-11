$(document).ready(function() {
	$(".retire").click(function(){
		var game_code = $(this).parent().parent(".input-group").children("input").attr("value");
		var xmlhttp = new XMLHttpRequest();
        xmlhttp.onreadystatechange = function() {
            if (xmlhttp.readyState === 4 && xmlhttp.status === 200) {
                var resp = xmlhttp.responseText;
				console.info(resp);
				if(resp !== 'false'){
					$("#ig_"+resp).remove();
				}
            }
        };
        xmlhttp.open("GET", "?controller=ajax&action=retireBox&code=" + game_code, true);
        xmlhttp.send();
	});
	
	$(".insert")
});