require(['jquery', 'core/config', 'core/ajax'], function ($, mdlcfg, Ajax) {
    // console.log(mdlcfg.wwwroot); // outputs the wwwroot of moodle to console
    var coursetimerstart = Date.now();
    var coursetimerinstance = $('#coursetimerinstance').val();

    setInterval(function () {
        var autopaused = $('#autopaused').val();
        if (autopaused == "false") {
            runCourseTimerScript()
        } else {
            // reset timer while paused.
            coursetimerstart = Date.now();
            console.log('Reset timer start while paused: ' + coursetimerstart);
        }
    }, 2000);

    var runCourseTimerScript = () => {
        ajaxload();
    }
    var ajaxload = () => {
        var form_data = new FormData();
        var coursertimerupdated = Date.now();
        var coursetimerlength = coursertimerupdated - coursetimerstart;
        form_data.append('action', 'coursetimer_countdown');
        form_data.append('coursetimerinstance', coursetimerinstance);
        form_data.append('coursetimerlength', coursetimerlength);
        form_data.append('coursetimerupdated', coursertimerupdated);
        $.ajax({
            url: mdlcfg.wwwroot + '/mod/courseduration/ajax.php',
            method: "POST",
            data: form_data,
            contentType: false,
            cache: false,
            processData: false,
            beforeSend: function () {},
            success: function (data) {
                console.log( coursetimerlength + ' - ' + coursertimerupdated );
                coursetimerstart = coursertimerupdated;
                console.log( coursetimerlength + ' - ' + coursertimerupdated );
            }
        });
    }
});
require(['jquery', 'core/config', 'core/ajax'], function ($, mdlcfg, Ajax) {
    $(document).ready(function () {
        var availabletime = $('#availabletime').val();
        var completiontime = $('#completiontime').val();
        var currentthemeused = $('#currentthemeused').val();
        var moodleversion = $('#moodleversion').val();
        // console.log(moodleversion);

        var remainingtime = completiontime - availabletime;

        if (moodleversion > '2020110500') {
            if (currentthemeused == 'snap') {
                $('.pull-right.js-only').append('<div class="countdowncoursetimer" style="display:none;">' + remainingtime + '</div>');
            } else {
                $('.ml-auto').eq(0).append('<div class="countdowncoursetimer" style="display:none;">' + remainingtime + '</div>');
            }
        } else if (moodleversion > '2017051400') {
            if (currentthemeused == 'snap') {
                $('.pull-right.js-only').append('<div class="countdowncoursetimer" style="display:none;">' + remainingtime + '</div>');
            } else {
                $('#nav-notification-popover-container').before('<div class="countdowncoursetimer" style="display:none;">' + remainingtime + '</div>');
            }
        } else {
            if (currentthemeused == 'snap') {
                $('.pull-right.js-only').append('<div class="countdowncoursetimer" style="display:none;">' + remainingtime + '</div>');
            } else {
                $('.d-none.d-lg-block').append('<div class="countdowncoursetimer" style="display:none;">' + remainingtime + '</div>');
            }
        }
        var countdowncoursetimer = parseInt($('.countdowncoursetimer').html());
        if (countdowncoursetimer < 0) { countdowncoursetimer = 0; }
        // console.log(countdowncoursetimer);
        if (countdowncoursetimer => 0) {

            setInterval(function () {
                var autopaused = $('#autopaused').val();
                var hours = Math.floor(countdowncoursetimer / 60 / 60);
                var minutes = Math.floor(countdowncoursetimer / 60) - (hours * 60);
                var seconds = countdowncoursetimer % 60;

                var hhours = hours >= 10 ? hours : '0' + hours;
                var mminutes = minutes >= 10 ? minutes : '0' + minutes;
                var sseconds = seconds >= 10 ? seconds : '0' + seconds;

                var formatted = hhours + ':' + mminutes + ':' + sseconds;

                $('.countdowncoursetimer').html(formatted);


                if (autopaused == "false") {
                    if (countdowncoursetimer >= 0) {
                        countdowncoursetimer = countdowncoursetimer - 1;
                    } else {
                        $('.countdowncoursetimer').html('00:00:00');
                        $('.countdowncoursetimer').removeClass('zero-element');
                        $('.countdowncoursetimer').addClass('completed-element');
                    }
                }
                $('.countdowncoursetimer').show();
            }, 1000);

        } else {
            $('.countdowncoursetimer').hide();
        }
    });

    var inactiveTime;
    function stopwatchandrequest() {
        // console.log('inactive');
        $('#autopaused').val('true');
        $('.countdowncoursetimer').addClass('zero-element');
        // TODO remove autopaused time from counter
    }

    function autopausecheck() {
        var autopausedtime = $('#autopausedtime').val();
        var countdowncoursetimer = parseInt($('.countdowncoursetimer').html());
        if (autopausedtime > 0) {
            clearTimeout(inactiveTime);
            var convertimetoseconds = autopausedtime * 1000;
            inactiveTime = setTimeout(stopwatchandrequest, convertimetoseconds); // 10 seconds
            $('#autopaused').val('false');
            $('.countdowncoursetimer').removeClass('zero-element');
            // console.log('active');
        }
    }

    $(document).ready(function () {
        var actionscalls = 'focus mousemove click mouseup mousedown keydown keypress keyup submit change mouseenter scroll resize dblclick';
        $('*').bind(actionscalls, function () {
            autopausecheck();
        });
        $("body").trigger("mousemove");
    });
    $(window).bind('blur', function () {
        stopwatchandrequest();
    });
});