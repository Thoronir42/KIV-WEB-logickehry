function setReservationTimes(checkStartTime) {
	var length = $(".game-type-list-group .active").attr('data-avg-time'),
		time_from = ($('input[name=time_from]').val()+"").split(':')[0]*1,
		dayStartHour = $(".time-buttons").attr('data-start-hour'),
		dayEndHour = $(".time-buttons").attr('data-end-hour');

	if(checkStartTime && time_from < dayStartHour){
		time_from = dayStartHour;
	}
	
	var hrs = Math.floor(length / 60),
		mins = length % 60;

	//alert('('+length+')'+hrStart+"+"+hrs+">="+endHour);
	
	if(time_from + hrs >= dayEndHour){
		var time = dayEndHour+":00:00";
		
	} else {
		var time = (time_from + hrs);
		if(time < 10){
			time = '0' + time;
		}
		time += ':';
		if(mins < 10){
			time += '0';
		}
		time += mins+':00';
	}
	if(time_from < 10){
		time_from = '0'+time_from;
	}
	
	$('input[name=time_from]').val(time_from+":00:00");
	$('input[name=time_to]').val(time);
	
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
	
	$(document).on("click", '.dateCol .btn', function () {
		var year = $(this).attr('data-year'),
			split = ($(this).find('.date').html()).split('.');
		var month = split[1],
			day = split[0];
		
		$('input[name=reservation_date]').val(year+'-'+month+'-'+day);
		setReservationTimes(true);
	});

});