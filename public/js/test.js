
jQuery(document).ready(function(){


    $('.datetimepicker').datetimepicker({
        ownerDocument: document,
        contentWindow: window,

        value: '',
        rtl: false,

        format: 'Y-m-d H:i:00',
        formatTime: 'H:i:00',
        formatDate: 'Y-m-d',

        startDate:  false, // new Date(), '1986/12/08', '-1970/01/05','-1970/01/05',
        step: 15,
        monthChangeSpinner: true,

        closeOnDateSelect: false,
        closeOnTimeSelect: true,
        closeOnWithoutClick: true,
        closeOnInputClick: true,
        openOnFocus: true,

        timepicker: true,
        datepicker: true,
        weeks: false,

        defaultTime: false, // use formatTime format (ex. '10:00' for formatTime: 'H:i')
        defaultDate: false, // use formatDate format (ex new Date() or '1986/12/08' or '-1970/01/05' or '-1970/01/05')

        minDate: false,
        maxDate: false,
        minTime: false,
        maxTime: false,
        minDateTime: false,
        maxDateTime: false,

        allowTimes: [],
        opened: false,
        initTime: true,
        inline: false,
        theme: '',
        touchMovedThreshold: 5,

        onSelectDate: function () {},
        onSelectTime: function () {},
        onChangeMonth: function () {},
        onGetWeekOfYear: function () {},
        onChangeYear: function () {},
        onChangeDateTime: function () {},
        onShow: function () {},
        onClose: function () {},
        onGenerate: function () {},

        withoutCopyright: true,
        inverseButton: false,
        hours12: false,
        next: 'xdsoft_next',
        prev : 'xdsoft_prev',
        dayOfWeekStart: 0,
        parentID: 'body',
        timeHeightInTimePicker: 25,
    todayButton: true,
        prevButton: true,
        nextButton: true,
        defaultSelect: true,

        scrollMonth: true,
        scrollTime: true,
        scrollInput: true,

        lazyInit: false,
        mask: false,
        validateOnBlur: true,
        allowBlank: true,
        yearStart: 1950,
        yearEnd: 2050,
        monthStart: 0,
        monthEnd: 11,
        style: '',
        id: '',
        fixed: false,
        roundTime: 'round', // ceil, floor
        className: '',
        weekends: [],
        highlightedDates: [],
        highlightedPeriods: [],
        allowDates : [],
        allowDateRe : null,
        disabledDates : [],
        disabledWeekDays: [],
        yearOffset: 0,
        beforeShowDay: null,

        enterLikeTab: true,
        showApplyButton: false
});


    $('.test').on('click', function (e){
        e.preventDefault();
        let staff_id = $('#staff-id').val();
        let start_date = $('#start_date').val();
        let end_date = $('#end_date').val();
        $.post(ajaxurl, {
            action: 'bookly_check_appointment_errors',
            csrf_token: '6d251c444a',
            appointment_id: '',
            staff_id: staff_id,
            customers: [],
            location_id: '',
            service_id: 1,
            start_date: start_date,
            end_date: end_date
        }, function (response) { // response callback function
            let date_interval_not_available = response.date_interval_not_available;
            let date_interval_warning = response.date_interval_warning;
            let interval_not_in_service_schedule = response.interval_not_in_service_schedule;
            let interval_not_in_staff_schedule = response.interval_not_in_staff_schedule;
            let staff_reaches_working_time_limit = response.staff_reaches_working_time_limit;

            $('.test-result').html('');
            $('.test-result').append(date_interval_not_available+ ' date_interval_not_available <br>' )
            $('.test-result').append(date_interval_warning+ ' date_interval_warning <br>' )
            $('.test-result').append(interval_not_in_service_schedule+ ' interval_not_in_service_schedule <br>' )
            $('.test-result').append(interval_not_in_staff_schedule+ ' interval_not_in_staff_schedule <br>' )
            $('.test-result').append(staff_reaches_working_time_limit+ ' staff_reaches_working_time_limit <br>' )


        })
            .done(function () {
                //alert( "second success" );
                //location.reload();
            });

    });

});