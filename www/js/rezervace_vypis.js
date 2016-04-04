function setReservationTimes(checkStartTime) {
	var length = $(".game-type-list-group .active").attr('data-avg-time'),
			time_from = ($('input[name=time_from]').val() + "").split(':')[0] * 1,
			dayStartHour = $(".time-buttons").attr('data-start-hour'),
			dayEndHour = $(".time-buttons").attr('data-end-hour');

	if (checkStartTime && time_from < dayStartHour) {
		time_from = dayStartHour;
	}

	var hrs = Math.floor(length / 60),
			mins = length % 60;

	//alert('('+length+')'+hrStart+"+"+hrs+">="+endHour);

	if (time_from + hrs >= dayEndHour) {
		var time = dayEndHour + ":00:00";

	} else {
		var time = (time_from + hrs);
		if (time < 10) {
			time = '0' + time;
		}
		time += ':';
		if (mins < 10) {
			time += '0';
		}
		time += mins + ':00';
	}
	if (time_from < 10) {
		time_from = '0' + time_from;
	}

	$('input[name=time_from]').val(time_from + ":00:00");
	$('input[name=time_to]').val(time);

}

function toggleSlide(instant, val) {
	var bodyHide, bodyShow;
	var showEvent = val === "evt";
	if (showEvent) {
		bodyHide = "reservation";
		bodyShow = "event";
	} else {
		bodyHide = "event";
		bodyShow = "reservation";
	}
	
	var butPrimary = $(showEvent ? "#label_evt" : "#label_res");
	var butDefault = $(showEvent ? "#label_res" : "#label_evt");
	
	butPrimary.removeClass("btn-default");
	butPrimary.addClass("btn-primary");
	butDefault.removeClass("btn-primary");
	butDefault.addClass("btn-default");
	
	if (instant) {
		$("#" + bodyHide + "Body").hide();
		$("#" + bodyShow + "Body").show();
	} else {
		$("#" + bodyHide + "Body").slideUp();
		$("#" + bodyShow + "Body").slideDown();
	}

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

		$('input[type=date]').val(year + '-' + month + '-' + day);
		setReservationTimes(true);
	});

	$("#filter").change(function () {
		var reservations = $('.reservation');
		var gid = $(this).val();
		if (gid === '0') {
			reservations.removeClass('hidden');
			return;
		}
		reservations.addClass('hidden');
		var fil = reservations.filter('.game' + gid);
		fil.removeClass('hidden');

	});

	$("#eventBody").hide();

	$("input[name=rb_add_type]").change(function(){
		toggleSlide(false, this.value);
	});
	
	if ($("#addReservation").attr("data-start-on-evt") === "true") {
		toggleSlide(true, 'evt');
	}
	
	
	$('.clh-date-picker').each(function(){
		$(this).datetimepicker({
			locale: 'cs',
			format: 'YYYY-MM-DD'
		});
		var dp = $(this).data('DateTimePicker');
		dp.showTodayButton(true);
	});
	
	$('.clh-time-picker').each(function(){
		$(this).datetimepicker({
			locale: 'cs',
			format: 'HH:mm'
		});
		//var dp = $(this).data('DateTimePicker');
	});
	
	prepareUpcommingReservations();
	

});

function prepareUpcommingReservations(){
	$(".badge-link").each(function(){
		var href = $("#upcomming-reservations").attr("data-fetch-link");
		href += $(this).parents("a.list-group-item").attr("data-value");
		$(this).attr('href', href);
	});
	
	$(".badge-link").click(function(){
		var xmlhttp = new XMLHttpRequest();
		xmlhttp.onreadystatechange = function() {
			if (xmlhttp.readyState === 4 && xmlhttp.status === 200) {
				var resp = xmlhttp.responseText;
				if(resp === 'false'){
					return;
				}
				$(".upcomming-list").html(resp);
				$("#upcomming-reservations").slideDown();
			}
		};
		xmlhttp.open("GET", $(this).attr("href"), true);
		xmlhttp.send();
		
		var label = $(this).siblings("h4").html();
		$("#upcomming-reservations .game_name").html(label);
		$("#upcomming-reservations .game_name").find("br").replaceWith(' ');
	});
	
	$(".close-upcomming").click(function(){
		$("#upcomming-reservations").slideUp();
	});
	$("#upcomming-reservations").hide();
}