jQuery(document).ready(function($) {

    let calendar_view = 'timeGridWeek';
    // check if screen is mobile change calendar view to dayView
    if (window.matchMedia('(max-width: 768px)').matches) {
         calendar_view = 'listWeek'; // was dayview => timeGridDay
    } else {
         calendar_view = 'timeGridWeek';
    }

    // Code using $ as usual goes here.
    let calendar_staff_ids = $('#calendar_staff_ids').val();
    let calendar_services_ids = $('#calendar_services_ids').val();
    let customer_id = $('#calendar_customer_id').val();
    let wp_parent_id = $('#wp_parent_id').val();
    let access_delete = $('#access_delete').val();
    let access_edit = $('#access_edit').val();
    let delete_without_validation = $('#delete_without_validation').val();
    let bb_group_id = $('#bb_group_id').val();
    let display_teacher_timezone = $('#display_teacher_timezone').val();
    let is_parent = $('#is_parent').val();


    let Calendar = function($container, options) {
        let obj  = this;
        jQuery.extend(obj.options, options);

        // Special locale for moment
        moment.locale('bookly', {
            months: obj.options.l10n.datePicker.monthNames,
            monthsShort: obj.options.l10n.datePicker.monthNamesShort,
            weekdays: obj.options.l10n.datePicker.dayNames,
            weekdaysShort: obj.options.l10n.datePicker.dayNamesShort,
            meridiem : function (hours, minutes, isLower) {
                return hours < 12
                    ? obj.options.l10n.datePicker.meridiem[isLower ? 'am' : 'AM']
                    : obj.options.l10n.datePicker.meridiem[isLower ? 'pm' : 'PM'];
            },
        });

        // Settings for Event Calendar
        let settings = {
            view: 'timeGridWeek',
            views: {
                dayGridMonth: {
                    dayHeaderFormat: function (date) {
                        return moment(date).locale('bookly').format('ddd');
                    },
                    displayEventEnd: true,
                    dayMaxEvents: obj.options.l10n.monthDayMaxEvents === '1'
                },
                timeGridDay: {
                    dayHeaderFormat: function (date) {
                        return moment(date).locale('bookly').format('dddd');
                    },
                    pointer: false
                },
                timeGridWeek: {
                    pointer: false
                },
                resourceTimeGridDay: {pointer: false},

            },
            nowIndicator: true,
            hiddenDays: obj.options.l10n.hiddenDays,
            slotDuration:  obj.options.l10n.slotDuration,
            slotMinTime: obj.options.l10n.slotMinTime,
            slotMaxTime: obj.options.l10n.slotMaxTime,
            scrollTime: obj.options.l10n.scrollTime,
            moreLinkContent: function (arg) {
                return obj.options.l10n.more.replace('%d', arg.num)
            },
            flexibleSlotTimeLimits: true,
            eventStartEditable: false,

            slotLabelFormat: function (date) {
                return moment(date).locale('bookly').format(obj.options.l10n.mjsTimeFormat);
            },
            eventTimeFormat: function (date) {
                return moment(date).locale('bookly').format(obj.options.l10n.mjsTimeFormat);
            },
            dayHeaderFormat: function (date) {
                return moment(date).locale('bookly').format('ddd, D');
            },
            listDayFormat: function (date) {
                return moment(date).locale('bookly').format('dddd');
            },
            firstDay: obj.options.l10n.datePicker.firstDay,
            locale: obj.options.l10n.locale.replace('_', '-'),
            buttonText: {
                today: obj.options.l10n.today,
                dayGridMonth: obj.options.l10n.month,
                timeGridWeek: obj.options.l10n.week,
                timeGridDay: obj.options.l10n.day,
                resourceTimeGridDay: obj.options.l10n.day,
                listWeek: obj.options.l10n.list
            },
            noEventsContent: obj.options.l10n.noEvents,
            eventSources: [{
                url: ajaxurl,
                method: 'POST',
                extraParams: function () {
                    return {
                        action: 'muslimeto_get_staff_appointments',
                        csrf_token: BooklyL10nGlobal.csrf_token,
                        staff_ids: calendar_staff_ids,
                        location_ids: obj.options.getLocationIds(),
                        service_ids: calendar_services_ids,
                        customer_id: customer_id,
                        bb_group_id: bb_group_id,
                        wp_parent_id: wp_parent_id,
                        display_teacher_timezone: display_teacher_timezone
                    };
                }
            }],
            eventBackgroundColor: '#ccc',
            eventMouseEnter: function(arg) {
                if (arg.event.display === 'auto' && arg.view.type !== 'listWeek') {
                    fixPopoverPosition($(arg.el).find('.bookly-ec-popover'));
                }
            },
            eventContent: function (arg) {
                if (arg.event.display === 'background') {
                    return '';
                }
                let event = arg.event;
                let props = event.extendedProps;
                let event_status = props.status;
                let stored_bb_group_id = props.stored_bb_group_id;
                let nodes = [];
                let $time = $('<div class="ec-event-time"/>');
                let $title = $('<div class="ec-event-title"/>');
                let hide_appointment_status = props.hide;
                let bb_group_permalink = props.bb_group_permalink;
                let event_title = $('<div></div>');
                if(props.event_title.length > 0){
                    event_title = $('<div class="event-title '+ event_status +'" >' + props.event_title + '</div>');
                }





                // hide non BB group events
                if( hide_appointment_status !== 'show' && typeof hide_appointment_status  !== "undefined") {
                    $title.addClass('hide');
                    $('.ec-event-title.hide').parent().addClass('hide_event');
                    //$('.ec-event-title.hide').parent().remove();
                }

                nodes.push(event_title.get(0))

                $time.append(props.header_text || arg.timeText);

                //nodes.push($time.get(0));
                if (arg.view.type === 'listWeek') {
                    let dot = $('<div class="ec-event-dot"></div>').css('border-color', event.backgroundColor);
                    nodes.push($('<div/>').append(dot).get(0));
                }
                $title.append(props.desc || '');

                nodes.push($title.get(0));

                switch (props.overall_status) {
                    case 'pending':
                        $time.addClass('text-muted');
                        $title.addClass('text-muted');
                        break;
                    case 'rejected':
                    case 'cancelled':
                        $time.addClass('text-muted').wrapInner('<s>');
                        $title.addClass('text-muted');
                        break;
                }

                const $buttons = $('<div class="mt-2 event-actions"/>');
                // if( access_delete === 'true' && event_status === 'approved' ){
                //     $buttons.append($('<a data-appointment-id="'+ arg.event.id +'" data-stored-bb-group-id="'+ stored_bb_group_id +'" class="btn btn-danger btn-sm mr-1 delete-calendar-program"> <i class="far fa-trash-alt"></i> </a>'));
                //     $buttons.addClass('justify-content-space-between flex-flow-row-reverse view-first');
                // }

                if( delete_without_validation === 'true' ){
                    $buttons.append($('<a data-appointment-id="'+ arg.event.id +'" data-stored-bb-group-id="'+ stored_bb_group_id +'" data-admin-delete="true" class="btn btn-danger btn-sm mr-1 delete-calendar-program admin_delete" data-balloon-pos="down" data-balloon="Delete Program"> <i class="far fa-trash-alt"></i> </a>'));
                    $buttons.addClass('justify-content-space-between flex-flow-row-reverse view-first');
                } else if (access_delete === 'true' && event_status === 'approved') {
                    $buttons.append($('<a data-appointment-id="'+ arg.event.id +'" data-stored-bb-group-id="'+ stored_bb_group_id +'" class="btn btn-danger btn-sm mr-1 delete-calendar-program" data-balloon-pos="down" data-balloon="Delete Program"> <i class="far fa-trash-alt"></i> </a>'));
                    $buttons.addClass('justify-content-space-between flex-flow-row-reverse view-first');
                }

                // show admin action edit/cancel/clone buttons on tooltip
                if( access_edit === 'true' ){
                    $buttons.append($('<a class="btn edit-program" href="/edit-program?bb_group_id='+ stored_bb_group_id +'" data-balloon-pos="down" data-balloon="Edit Program"> <i class="far fa-edit"></i>  </a>'));
                    // add single event links, duplicate - delete
                    $buttons.append($('<a class="btn duplicate-event" href="#" data-event-id="'+ event.id +'" data-bb-group-id="'+ stored_bb_group_id +'" data-balloon-pos="down" data-balloon="Clone Session"> <i class="fas fa-clone"></i> </a>'));
                    $buttons.append($('<a class="btn delete-single-event" href="#" data-event-id="'+ event.id +'" data-bb-group-id="'+ stored_bb_group_id +'" data-balloon-pos="down" data-balloon="Delete Session"> <i class="fas fa-calendar-times"></i> </a>'));
                }


                // show view page link in tooltip
                if( bb_group_permalink !== undefined ){
                    $buttons.append($('<a class="btn bb-event-url" href="'+ bb_group_permalink +'" data-balloon-pos="down" data-balloon="Open Class"> <i class="fas fa-link"></i>  </a>'));
                }

                // show cancel session btn if parent
                if( is_parent && is_parent == 'true' && props.show_cancel_btn == true ){
                    $buttons.append($('<a data-ca-id="'+ props.ca_id +'"  class="btn btn-danger btn-sm mr-1 cancel-session" data-balloon-pos="down" data-balloon="Cancel Session" data-bs-toggle="modal" data-bs-target="#cancel-session"> <i class="fas fa-calendar-times"></i>  </a>'));
                }

                // show makeup btn if parent
                if( is_parent && is_parent == 'true' && props.showMakeupFlagBtn == true && event_status != 'pending' ){
                    $buttons.append($('<div class="makeup-label" data-balloon-pos="down" data-balloon="Makeup session can not be cancelled" > <span class="makeup-session"> M </span> <span class="btn btn-warning btn-sm mr-1 makeup-flag-btn" > Makeup Session </span> </div>'));
                }

                if( is_parent && is_parent == 'true' && event_status == 'pending' ){
                    $buttons.append($('<a href="/verify-schedule-makeup?verify_token='+props.tk+'&uid='+props.pid+'" class="btn btn-info btn-sm mr-1" data-balloon-pos="down" data-balloon="Confirm Makeup Session"> confirm </a>'));
                }

                // $buttons.append($('<button class="btn btn-success btn-sm mr-1">').append('<i class="far fa-fw fa-edit">'));
                // if (obj.options.l10n.recurring_appointments.active == '1' && props.series_id) {
                //     $buttons.append(
                //         $('<a class="btn btn-default btn-sm mr-1">').append('<i class="fas fa-fw fa-link">')
                //             .attr('title', obj.options.l10n.recurring_appointments.title)
                //             .on('click', function (e) {
                //                 e.stopPropagation();
                //                 BooklySeriesDialog.showDialog({
                //                     series_id: props.series_id,
                //                     done: function () {calendar.refetchEvents();}
                //                 });
                //             })
                //     );
                // }
                // if (obj.options.l10n.waiting_list.active == '1' && props.waitlisted > 0) {
                //     $buttons.append(
                //         $('<a class="btn btn-default btn-sm mr-1">').append('<i class="far fa-fw fa-list-alt">')
                //             .attr('title', obj.options.l10n.waiting_list.title)
                //     );
                // }
                // if (obj.options.l10n.packages.active == '1' && props.package_id > 0) {
                //     $buttons.append(
                //         $('<a class="btn btn-default btn-sm mr-1">').append('<i class="far fa-fw fa-calendar-alt">')
                //             .attr('title', obj.options.l10n.packages.title)
                //             .on('click', function (e) {
                //                 e.stopPropagation();
                //                 if (obj.options.l10n.packages.active == '1' && props.package_id) {
                //                     $(document.body).trigger('bookly_packages.schedule_dialog', [props.package_id, function () {
                //                         calendar.refetchEvents();
                //                     }]);
                //                 }
                //             })
                //     );
                // }
                // $buttons.append(
                //     $('<a class="btn btn-danger btn-sm text-white">').append('<i class="far fa-fw fa-trash-alt">')
                //         .attr('title', obj.options.l10n.delete)
                //         .on('click', function (e) {
                //             e.stopPropagation();
                //             // Localize contains only string values
                //             if (obj.options.l10n.recurring_appointments.active == '1' && props.series_id) {
                //                 $(document.body).trigger('recurring_appointments.delete_dialog', [calendar, arg.event]);
                //             } else {
                //                 new BooklyConfirmDeletingAppointment({
                //                         action: 'bookly_delete_appointment',
                //                         appointment_id: arg.event.id,
                //                         csrf_token: BooklyL10nGlobal.csrf_token
                //                     },
                //                     function (response) {calendar.removeEventById(arg.event.id);}
                //                 );
                //             }
                //         })
                // );

                if (arg.view.type !== 'listWeek') {
                    $buttons.addClass('border-top pt-2');
                    let $popover = $('<div class="bookly-popover bs-popover-top bookly-ec-popover">')
                    let $arrow = $('<div class="arrow" style="left:8px;">');
                    let $body = $('<div class="popover-body">');
                    $body.append(props.tooltip).append($buttons).css({minWidth: '200px'});
                    $popover.append($arrow).append($body);
                    nodes.push($popover.get(0));
                    $time.on('touchstart', function () {
                        fixPopoverPosition($popover);
                    });
                    $title.on('touchstart', function () {
                        fixPopoverPosition($popover);
                    });
                } else {
                    $title.append($buttons);
                }

                return {domNodes: nodes};
            },
            eventClick: function (arg) {
                // if (arg.event.display === 'background') {
                //     return;
                // }
                // arg.jsEvent.stopPropagation();
                // var visible_staff_id;
                // if (arg.view.type === 'resourceTimeGridDay') {
                //     visible_staff_id = 0;
                // } else {
                //     visible_staff_id = obj.options.getCurrentStaffId();
                // }
                //
                // BooklyAppointmentDialog.showDialog(
                //     arg.event.id,
                //     null,
                //     null,
                //     function (event) {
                //         if (event == 'refresh') {
                //             calendar.refetchEvents();
                //         } else {
                //             if (event.start === null) {
                //                 // Task
                //                 calendar.removeEventById(event.id);
                //             } else {
                //                 if (visible_staff_id == event.resourceId || visible_staff_id == 0) {
                //                     // Update event in calendar.
                //                     calendar.updateEvent(event);
                //                 } else {
                //                     // Switch to the event owner tab.
                //                     jQuery('li > a[data-staff_id=' + event.resourceId + ']').click();
                //                 }
                //             }
                //         }
                //
                //         if (locationChanged) {
                //             calendar.refetchEvents();
                //             locationChanged = false;
                //         }
                //     }
                // );
            },
            dateClick: function (arg) {
                let staff_id, visible_staff_id;
                if (arg.view.type === 'resourceTimeGridDay') {
                    staff_id = arg.resource.id;
                    visible_staff_id = 0;
                } else {
                    staff_id = visible_staff_id = obj.options.getCurrentStaffId();
                }
                //addAppointmentDialog(arg.date, staff_id, visible_staff_id);
            },
            noEventsClick: function (arg) {
                let staffId = obj.options.getCurrentStaffId();
                //addAppointmentDialog(arg.view.activeStart, staffId, staffId);
            },
            loading: function (isLoading) {
                if (isLoading) {
                    BooklyL10nAppDialog.refreshed = true;
                    if (dateSetFromDatePicker) {
                        dateSetFromDatePicker = false;
                    } else {
                        calendar.setOption('highlightedDates', []);
                    }
                    $('.bookly-ec-loading').show();
                } else {
                    $('.bookly-ec-loading').hide();
                    obj.options.refresh();
                }
            },
            viewDidMount: function (view) {
                calendar.setOption('highlightedDates', []);
                obj.options.viewChanged(view);
            },
            theme: function (theme) {
                theme.button = 'btn btn-default';
                theme.buttonGroup = 'btn-group';
                theme.active = 'active';
                theme.nowIndicator = 'ec-now-indicator';
                return theme;
            }
        };

        function fixPopoverPosition($popover) {
            let $event = $popover.closest('.ec-event'),
                offset = $event.offset(),
                top = Math.max($popover.outerHeight() + 40, Math.max($event.closest('.ec-body').offset().top, offset.top) - $(document).scrollTop());

            $popover.css('top', (top - $popover.outerHeight() - 4) + 'px')
            $popover.css('left', (offset.left + 2) + 'px')
        }

        function addAppointmentDialog(date, staffId, visibleStaffId) {
            BooklyAppointmentDialog.showDialog(
                null,
                parseInt(staffId),
                moment(date),
                function (event) {
                    if (event == 'refresh') {
                        calendar.refetchEvents();
                    } else {
                        if (visibleStaffId == event.resourceId || visibleStaffId == 0) {
                            if (event.start !== null) {
                                if (event.id) {
                                    // Create event in calendar.
                                    calendar.addEvent(event);
                                } else {
                                    calendar.refetchEvents();
                                }
                            }
                        } else {
                            // Switch to the event owner tab.
                            jQuery('li[data-staff_id=' + event.resourceId + ']').click();
                        }
                    }

                    if (locationChanged) {
                        calendar.refetchEvents();
                        locationChanged = false;
                    }
                }
            );
        }

        let dateSetFromDatePicker = false;

        let calendar = new window.EventCalendar($container.get(0), $.extend(true, {}, settings, obj.options.calendar));
        calendar.setOption('view', calendar_view);


        $('.ec-toolbar .ec-title', $container).on('click', function () {
            let picker = $(this).data('daterangepicker');
            picker.setStartDate(calendar.getOption('date'));
            picker.setEndDate(calendar.getOption('date'));
        });
        // Init date picker for fast navigation in Event Calendar.
        $('.ec-toolbar .ec-title', $container).daterangepicker({
            parentEl        : '.bookly-js-calendar',
            singleDatePicker: true,
            showDropdowns   : true,
            autoUpdateInput : false,
            locale          : obj.options.l10n.datePicker
        }).on('apply.daterangepicker', function (ev, picker) {
            dateSetFromDatePicker = true;
            if (calendar.view.type !== 'timeGridDay' && calendar.view.type !== 'resourceTimeGridDay') {
                calendar.setOption('highlightedDates', [picker.startDate.toDate()]);
            }
            calendar.setOption('date', picker.startDate.toDate());
        });

        // Export calendar
        this.ec = calendar;
        if (obj.options.l10n.monthDayMaxEvents == '1') {
            let theme = this.ec.getOption('theme');
            theme.month += ' ec-minimalistic';
            this.ec.setOption('theme', theme);
        }
    };

    var locationChanged = false;
    $('body').on('change', '#bookly-appointment-location', function() {
        locationChanged = true;
    });

    Calendar.prototype.options = {
        calendar: {},
        getCurrentStaffId: function () { return -1; },
        getStaffMemberIds: function () { return [this.getCurrentStaffId()]; },
        getServiceIds: function () { return ['all']; },
        getLocationIds: function () { return ['all']; },
        refresh: function () {},
        viewChanged: function () {},
        l10n: {}
    };

    window.BooklyCalendar = Calendar;


    // $(document).click(function(e) {
    //     if ($('.modal.micromodal-slide').attr('aria-hidden')=='true' && !$(e.target).closest('.event-actions .btn').length) {
    //         $("#content").css('zIndex',1)
    //     }else if($(e.target).closest('.event-actions .btn').length){
    //         $("#content").css('zIndex',99999)
    //     }
    // });


    

});

