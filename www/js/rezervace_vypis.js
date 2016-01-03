function setTimeTo(timeStart) {
	var length = $(".game-type-list-group .active").attr('data-avg-time');
	var hrStart = timeStart.split(':')[0]*1.0,
		hrs = Math.floor(length / 60),
		mins = length % 60,
		endHour = $(".time-buttons").attr('data-end-hour');

	if(hrStart + hrs >= endHour){
		var time = endHour+":00:00";
	} else {
		var time = (hrStart + hrs)+':';
		if(mins < 10){
			time += '0';
		}
		time += mins+':00';
	}
	
	$('input[name=time_to]').val(time);
	
}

$(document).ready(function () {
	$(document).on("click", '.game-type-list-group .list-group-item', function () {
		$(this).addClass('active');
		$(this).siblings('.list-group-item').removeClass('active');
		$('input[name=game_type_id]').attr('value', $(this).attr('data-value'));
	});

	$(document).on("click", '.time-buttons .btn', function () {
		if ($(this).hasClass('disabled')) {
			return;
		}
		var timeStart = $(this).html() + ":00";


		$('input[name=time_from]').val(timeStart);
		setTimeTo(timeStart);
	});

});