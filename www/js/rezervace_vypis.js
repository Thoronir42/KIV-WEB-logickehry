function setReservationTimes(checkStartTime) {
	var length = $(".game-type-list-group .active").attr('data-avg-time'),
		time_from = ($('input[name=time_from]').val()+"").split(':')[0]*1,
		dayStartHour = $(".time-buttons").attr('data-start-hour'),
		dayEndHour = $(".time-buttons").attr('data-end-hour');

	if(checkStartTime && time_from < dayStartHour){
		alert(time_from+"<"+dayStartHour);
		time_from = dayStartHour;
	}
	
	var hrs = Math.floor(length / 60),
		mins = length % 60;

	//alert('('+length+')'+hrStart+"+"+hrs+">="+endHour);
	
	if(time_from + hrs >= dayEndHour){
		var time_to = dayEndHour+":00:00";
		
	} else {
		var time_to = (time_from + hrs);
		if(time_to < 10){
			time_to = '0' + time_to;
		}
		time_to += ':';
		if(mins < 10){
			time_to += '0';
		}
		time_to += mins+':00';
	}
	if(time_from < 10){
		time_from = '0'+time_from;
	}
	
	$('input[name=time_from]').val(time_from+":00:00");
	$('input[name=time_to]').val(time_to);
	
}

$(document).ready(function () {
	$(document).on("click", '.game-type-list-group .list-group-item', function () {
		setReservationTimes(true);
	});

	$(document).on("click", '.time-buttons .btn', function () {
		if ($(this).hasClass('disabled')) {
			return;
		}
		var timeStart = $(this).html() + ":00";
		$('input[name=time_from]').val(timeStart);
		setReservationTimes(false);
	});

});