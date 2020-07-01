$(function() {
	moment.locale('ru');

	function init_date(selector, picker) {
		var start_date = $(selector).find('input').eq(0).val();
		var end_date = $(selector).find('input').eq(1).val();

		var start = moment(start_date, "DD.MM.YYYY");
		var end = moment(end_date, "DD.MM.YYYY");
		var date_string = "";

		if (start_date == "" && end_date == "") {
			date_string = "выберите дату";	
		} else {
			picker.setStartDate(start);
			picker.setEndDate(end);
			picker.updateCalendars();

			var label = picker.getLabel();

			if (label == 'другой') {
				date_string = picker.startDate.format('DD.MM.YYYY') + ' - ' + picker.endDate.format('DD.MM.YYYY');
			} else {
				date_string = picker.chosenLabel;
			}
		}

		$(selector).find('span').html(date_string);
	}

	$('.table-sticky-headers').stickyTableHeaders();

	$('.daterange-picker').each(function() {
		var $this = $(this);
		var datePicker = $this.daterangepicker({
	        ranges: {
	           'сегодня': [moment(), moment()],
	           'вчера': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
	           'за неделю': [moment().subtract(6, 'days'), moment()],
	           'за месяц': [moment().subtract(29, 'days'), moment()],
	           'за текущий месяц': [moment().startOf('month'), moment().endOf('month')],
	           'за прошлый месяц': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
	        },
	        'locale': {
	        	'format': 'DD.MM.YYYY',
	        	'cancelLabel': 'Очистить',
	        	'applyLabel': 'Выбрать',
	        	'customRangeLabel': "другой",
	        	'monthNames': moment.months()
	        }
	    })
	    .on('apply.daterangepicker', function(event, picker) {
	    	var target = $(event.target);
	    	var start_input = target.find('input').eq(0);
	    	var end_input = target.find('input').eq(1);
	    	var span = target.find('span');

	    	var start_date = picker.startDate.format('DD.MM.YYYY');
	    	var end_date = picker.endDate.format('DD.MM.YYYY');

	    	start_input.val(start_date);
	    	end_input.val(end_date);
	    	
	    	if (picker.chosenLabel == 'другой') {
		    	span.html(start_date + ' - ' + end_date);
	    	} else {
	    		span.html(picker.chosenLabel);
	    	}
	    })
	    .on('cancel.daterangepicker', function(event, picker) {
	    	var target = $(event.target);
	    	var start_input = target.find('input').eq(0);
	    	var end_input = target.find('input').eq(1);
	    	var span = target.find('span');

	    	start_input.val('');
	    	end_input.val('');
	    	span.html('выберите дату');

	    }).data('daterangepicker');

	    init_date($this, datePicker);
	});

	$('.item').click(function() {
		var $this = $(this).parent();
		var id 	  = $this.data('id');
		var depth = $this.data('level');

		if (id != undefined) {
			depth 		+= 1;
			var $childs = $('.item-'+id);
			var f 		= '.level-'+depth;

			if ($this.hasClass('closed')) {
				$childs.filter(f).show().addClass('closed');
				$this.removeClass('closed');
			} else {
				$childs.hide();
				$this.addClass('closed');
			}
		}
	});
});