let countdowncoursetimer = 0;

require(['jquery', 'core/config', 'core/ajax'], function ($, mdlcfg, Ajax) {
    // console.log(mdlcfg.wwwroot); // outputs the wwwroot of moodle to console
    var coursetimerstart = Date.now();
    var coursetimer = $('#coursetimer').val();

    setInterval(function () {
        var autopaused = $('#autopaused').val();
        if (autopaused == "false") {
            runCourseTimerScript()
        } else {
            // reset timer while paused.
            coursetimerstart = Date.now();
        }
    }, 5000);

    var runCourseTimerScript = () => {
        ajaxload();
    }
    var ajaxload = () => {
        var form_data = new FormData();
        var coursertimerupdated = Date.now();
        var coursetimerlength = coursertimerupdated - coursetimerstart;
        form_data.append('action', 'coursetimer_countdown');
        form_data.append('coursetimer', coursetimer);
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
                if (data.result > 0) {
                    var completionduration = $('#completionduration').val();
                    countdowncoursetimer = completionduration - Math.round(data.result / 1000);
                }
                coursetimerstart = coursertimerupdated;
            },
            error: function (xhr, text, error) {
                console.log('AJAX error:' + text + ' - ' + error);
            }
        });
    }
});
require(['jquery', 'core/config', 'core/ajax'], function ($, mdlcfg, Ajax) {
    $(document).ready(function () {
        var coursetime = $('#coursetime').val();
        var completionduration = $('#completionduration').val();
        var currentthemeused = $('#currentthemeused').val();
        var moodleversion = $('#moodleversion').val();
        // console.log(moodleversion);

        var remainingtime = completionduration - Math.round(coursetime / 1000);

        if (moodleversion > '2020110500') {
            if (currentthemeused == 'snap') {
                $('.pull-right.js-only').prepend('<div class="countdowncoursetimer fadein" style="display:none;">' + remainingtime + '</div>');
            } else {
                $('.ml-auto').eq(0).prepend('<div class="countdowncoursetimer fadein" style="display:none;">' + remainingtime + '</div>');
            }
        } else if (moodleversion > '2017051400') {
            if (currentthemeused == 'snap') {
                $('.pull-right.js-only').prepend('<div class="countdowncoursetimer fadein" style="display:none;">' + remainingtime + '</div>');
            } else {
                $('#nav-notification-popover-container').before('<div class="countdowncoursetimer fadein" style="display:none;">' + remainingtime + '</div>');
            }
        } else {
            if (currentthemeused == 'snap') {
                $('.pull-right.js-only').prepend('<div class="countdowncoursetimer fadein" style="display:none;">' + remainingtime + '</div>');
            } else {
                $('.d-none.d-lg-block').prepend('<div class="countdowncoursetimer fadein" style="display:none;">' + remainingtime + '</div>');
            }
        }

        countdowncoursetimer = parseInt($('.countdowncoursetimer').html());
        if (countdowncoursetimer < 0) { countdowncoursetimer = 0; }
        // console.log(countdowncoursetimer);
        if (countdowncoursetimer => 0) {

            setInterval(function () {
                if (countdowncoursetimer < 0) { countdowncoursetimer = 0; }

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
                    if (countdowncoursetimer > 0) {
                        countdowncoursetimer = countdowncoursetimer - 1;
                        // activate completion animation on reaching 0
                        if (countdowncoursetimer == 0) {
                            $('.countdowncoursetimer').html('00:00:00');
                            $('.countdowncoursetimer').removeClass('zero-element');
                            $('.countdowncoursetimer').addClass('completed-element');
                            $('.countdowncoursetimer').addClass('on-completion');
                        }
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
        if (countdowncoursetimer > 0) {
            $('.countdowncoursetimer').addClass('zero-element');
        }
        $('.countdowncoursetimer').removeClass('fadein');
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
        var actionscalls = 'focus mousemove click mouseup mousedown keydown keypress keyup submit change mouseenter scroll onscroll resize dblclick touchmove ontouchmove touchstart ontouchstart';
        $('*').bind(actionscalls, function () {
            autopausecheck();
        });
        $("body").trigger("mousemove");
    });
    $(window).bind('blur', function () {
        stopwatchandrequest();
    });
});