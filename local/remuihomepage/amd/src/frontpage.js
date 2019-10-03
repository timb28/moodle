// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

// Section manager class
//
// @module     local_remuihomepage/frontpage
// @copyright  (c) 2018 WisdmLabs (https://wisdmlabs.com/)
// @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later.

define([
    'jquery',
    'core/modal_factory',
    'core/templates',
    'core/str','core/modal_events',
    'local_remuihomepage/sectionmanager',
    'core/fragment',
    'core/ajax',
    'core/notification',
    'theme_remui/events',
    'theme_remui/html2canvas'
], function(
    $,
    ModalFactory,
    Templates,
    Str,
    ModalEvents,
    SECTIONMANAGER,
    Fragment,
    Ajax,
    Notification,
    RemUIEvents,
    html2canvas
){
    var CONTEXTID = -1;
    var MAINMODAL = null;
    var CONFIGMODAL = null;
    var modalSelector = "";
    var SECTIONSELECT   = ".sections-container div[class*='section-']";
    var ADDSECTIONTRIGGER = ".add-section";
    var EDITSECTIONTRIGGER   = ".home-sections button.edit-section";
    var DELETESECTIONTRIGGER = ".home-sections button.delete-section";
    var DELETESECTIONCANCEL = ".home-sections button.cancel-delete-section";
    var DELETESECTIONCONFIRM = ".home-sections button.confirm-delete-section";
    var MOVESECTIONUP = ".moveup-section";
    var MOVESECTIONDOWN = ".movedown-section";
    var EDITINGACTION = ".editing-action";
    var SLIDERSECTIONS = ".home-sections section .carousel";
    var PUBLISH = ".publish";
    var PREVIEW = ".preview-section";
    var SETTINGS = ".settings";
    var VISIBILITY = ".home-sections button.section-visibility";
    var EDITINGALERT = ".editing-alert";
    var COURSESECTIONCATEGORY = ".home-sections .section-courses .category-list .category-item";
    var COURSESECTIONASIDE = ".home-sections .section-courses .category-list .site-skintools-toggle";
    var COURSEPERPAGE = 25;

    $(document).ready(function() {

        // Homepage screenshot js.

        // Hide camera flash effect.
        $('.flash').hide();

        var imageserverurl = 'https://share.edwiser.org/api/base64.php';
        // Take page screenshot.
        $('#take_screenshot').click(function (e) {
            e.preventDefault();
            $('.flash').show().animate({opacity: 0.4}, 300).fadeOut(300).css({'opacity': 1});

            // Cleanup previous values on capture again.
            // Change capture text to loading icon.
            $('#take_screenshot').html('<i class="fa fa-spinner fa-spin" aria-hidden="true"></i>');
            clear_screenshot_values();

            // Remove animation class from all sections.
            if(appearanimation == 1 && appearanimationstyle != '') {
                $(".home-sections section.panel, .default-sections.panel")
                .removeClass(appearanimationstyle)
                .removeClass('animation-slide-left')
                .removeClass('animation-slide-right');
            }

            // Fix html2canvas not supporting video tag.
            var canvas = document.getElementById('videofixCanvas');
            var ctx = canvas.getContext('2d');
            var videos = document.querySelectorAll('video');
            var w, h;
            for (var i = 0, len = videos.length; i < len; i++) {
                var v = videos[i];
                try {
                    w = v.videoWidth;
                    h = v.videoHeight;
                    canvas.width = w;
                    canvas.height = h;
                    ctx.fillRect(0, 0, w, h);
                    ctx.drawImage(v, 0, 0, w, h);
                    v.style.backgroundImage = "url('" + canvas.toDataURL() + "')"; // Here is the magic.
                    v.style.backgroundSize = 'cover';
                    ctx.clearRect(0, 0, 0, 0); // Clean the canvas.
                } catch (e) {
                    continue;
                }
            }
            // Wait for jquery to remove above classes from DOM elements.
            setTimeout(take_screenshot, 2000);
        });

        var options = {
            'logging': false,
            'useCORS': true
        };

        function take_screenshot() {
            html2canvas(document.querySelector(".page"), options).then(function (canvas) {
                var img = canvas.toDataURL('image/jpeg', 0.95);

                // Send image to server.
                var formdata = new FormData();
                formdata.append('base64', img);

                // Prepare filename.
                var filename = M.cfg.wwwroot.replace(/(^\w+:|^)\/\//, '');
                filename = String(filename).replace("#", "");
                filename = String(filename).replace("/", "");
                filename = String(filename).replace(".", "-");
                formdata.append('hash', filename + '-' + Math.random().toString(36).substring(7));

                $.ajax({
                    url: imageserverurl,
                    type: 'POST',
                    crossDomain: true,
                    data: formdata,
                    async: true,
                    cache: false,
                    contentType: false,
                    processData: false,
                    success: function (response) {
                        parsed = JSON.parse(response);

                        if(parsed.status == 'ok') {
                            $('section.take-screenshot').hide();
                            $('#take_screenshot').html('CAPTURE AGAIN');

                            $('.screenshot-container .screenshot').css("background", 'url(' + parsed.url + ')');
                            $('.delete-screenshot').attr('data-deleteurl', parsed.delete_url);
                            $('.download-screenshot').attr('href', parsed.url);
                            $('.preview-screenshot').attr('href', parsed.url);
                            $('section.taken-screenshot').show();

                            // Prepare share urls for all buttons.
                            $('a.social-facebook').attr('href', 'https://www.facebook.com/sharer/sharer.php?u=' + parsed.url);
                            $('a.social-twitter').attr('href', 'https://twitter.com/share?url=' + parsed.url);
                            $('a.social-linkedin').attr('href', 'https://www.linkedin.com/shareArticle?mini=true&url=' + parsed.url);
                        } else {
                            $('.failed-screenshot-message').html("Something went wrong!");
                            $('#take_screenshot').html('Capture Again');
                        }
                    },
                    error: function(xhr, status, error) {
                        $('.failed-screenshot-message').html(error);
                        $('#take_screenshot').html('CAPTURE AGAIN');
                    }
                });
            });
        }

        // Clear values from modal when capturing again.
        function clear_screenshot_values() {
            $('.failed-screenshot-message').html('');
            $('.screenshot-container .screenshot').css("background", "");
            $('.delete-screenshot').attr('data-deleteurl', '');
            $('.download-screenshot').attr('href', '');
            $('.preview-screenshot').attr('href', '');
            $('a.social-facebook').attr('href', '');
            $('a.social-linkedin').attr('href', '');
            $('a.social-twitter').attr('href', '');
        }

        // Delete screenshot, clean values from modal and show capture again.
        // On delete button click.
        $('.delete-screenshot').click(function (e) {
            var delete_url = $('.delete-screenshot').data('deleteurl');
            clear_screenshot_values();
            $('section.taken-screenshot').hide();
            $('section.take-screenshot').show();

            if(delete_url) {
                $.ajax({
                    url: delete_url,
                    type: 'GET',
                    dataType: "json",
                    crossDomain: true,
                    async: true
                });
            }
        });

        // Clean values from modal if modal closed.
        $('#screenshotShareModal').on('hidden.bs.modal', function (e) {
            clear_screenshot_values();
            $('section.taken-screenshot').hide();
            $('section.take-screenshot').show();
        });

        // Open share link in popup.
        $('a.social-share ').click(function(e){
            e.preventDefault();
            var $link   = $(this);
            var href    = $link.attr('href');
            var network = $link.attr('data-network');

            var networks = {
                facebook : { width : 600, height : 300 },
                twitter  : { width : 600, height : 254 },
                linkedin : { width : 600, height : 473 }
            };

            var popup = function(network){
                var options = 'menubar=no,toolbar=no,resizable=yes,scrollbars=yes,';
                window.open(href, '', options + 'height=' + networks[network].height + ',width=' + networks[network].width);
            }

            popup(network);
        });

        // Every time the window is scrolled.
        $(window).scroll( function() {
            // Check the location of each lazy section.
            $('.pagelayout-frontpage section.lazy-section').each(function(i){
                var sectiontop = $(this).position().top;
                var bottom_of_window = $(window).scrollTop() + $(window).height();
                // Load upto three sections.
                var thricewindow = bottom_of_window + $(window).height() * 2;
                if (thricewindow > sectiontop) {
                    if ($(this).is('.lazy-section')) {
                        $(this).removeClass("lazy-section");
                        var instanceid = $(this).data('instance');
                        var section = SECTIONMANAGER.getLoadedSection(instanceid);
                        SECTIONMANAGER.reloadSection({
                            success: true,
                            context: section
                        }, function() {
                            var section  = $('.home-sections section[data-instance="' + instanceid + '"]');
                        });
                        return;
                    }
                }
            });
            // Check the location of each invisible section.
            $('.pagelayout-frontpage section.invisible').each(function(i){
                var sectiontop = $(this).position().top + 100;
                var bottom_of_window = $(window).scrollTop() + $(window).height();
                if( bottom_of_window > sectiontop ){
                    setTimeout((function() {
                        $(this).removeClass('invisible');
                        // Show animation if appear animation is enabled.
                        if (appearanimation == true) {
                            if (appearanimationstyle == 'animation-slide-left-right') {
                                if ($(this).prev().is('.animation-slide-left')) {
                                    $(this).addClass('animation-slide-right');
                                } else {
                                    $(this).addClass('animation-slide-left');
                                }
                            } else {
                                $(this).addClass(appearanimationstyle);
                            }
                        }
                    }).bind(this), 100);
                }
            });
            if (transparentheader) {
                if ($('section[data-instance]:first-child').is('.section-slider')) {
                    var windowtop = $(window).scrollTop();
                    if(windowtop < 45) {
                        $("body.has-slider").addClass("animate-header");
                        return;
                    }
                }
                $("body.has-slider").removeClass("animate-header");
            }
        });

    });

    // Play video from slider when slide is changed.
    $(document).on('slid.bs.carousel', SLIDERSECTIONS, function() {
        var instanceid = $(this).parent().data('instance');
        SECTIONMANAGER.playSliderVideo(instanceid);
    });

    $(document).on('click', EDITINGACTION, function() {
        var form = $('<form method="post"></form>');
        form.append("<input type='hidden' name='frontpageediting' value='1'/>");
        form.append("<input type='hidden' name='editpage' value='" + $(this).data('edit') + "'/>");
        $('body').append(form);
        form.submit();
    });

    $(document).on('click', PUBLISH, function() {
        var form = $('<form method="post"></form>');
        form.append("<input type='hidden' name='frontpageediting' value='1'/>");
        form.append("<input type='hidden' name='publish' value='1'/>");
        $('body').append(form);
        form.submit();
    });

    // Toggle frontpage preview.
    $(document).on('click', PREVIEW, function() {
        SECTIONMANAGER.showAllSectionsLoader();
        $(this).find('i').toggleClass('fa-eye fa-eye-slash');
        var previewon = $(this).find('i').is('.fa-eye-slash');
        $('body').toggleClass('preview', previewon);
        $(SETTINGS + ',' + PUBLISH + ',' + ADDSECTIONTRIGGER + ',' + EDITINGACTION + ',' + EDITINGALERT).toggleClass('d-flex', !previewon).toggleClass('d-none', previewon);
        $('html, body').animate({scrollTop:0}, $(window).scrollTop() / 6);
        $(VISIBILITY + ' .fa-eye-slash').parents('section[data-instance]').toggleClass('section-invisible', previewon);
        if (transparentheader) {
            if (previewon) {
                var sections = $('body').find('section[data-instance]:not(.section-invisible)');
                var hasslider = false;
                if (sections) {
                    hasslider = $(sections[0]).is('.section-slider');
                }
                $('body').toggleClass('has-slider animate-header', hasslider);
            } else {
                $('body').toggleClass('has-slider animate-header', $('body').find('section[data-instance]:first-child').is('.section-slider'));
            }
        }
        setTimeout(function(){ SECTIONMANAGER.hideAllSectionsLoader(); }, 2000);

    });

    // Trigger testimonial auto slider, coursesandcategories first category.
    $(document).on(RemUIEvents.SECTION_ADDED + ' ' + RemUIEvents.SECTION_UPDATED, function(event) {
        switch(event.configdata.sectionname) {
            case 'courses':
                var section = SECTIONMANAGER.getSectionElement(event.configdata.id);
                if (section.is('.categoryandcourses-view') && event.configdata.catlist.length != 0) {
                    var firstcat = event.configdata.catlist[0].categoryid;
                    generate_courses(event.configdata.id, firstcat);
                } else {
                    apply_slick_to_courses(section.data('instance'), {});
                }
                break;
        }
    });

    // Load courses on course category selection.
    $('body').on('click', COURSESECTIONCATEGORY, function() {
        var instanceid = $(this).parents('section[data-instance]').data('instance');
        $(this).parents('section[data-instance]').find('.courses-slider').removeClass('show');
        var categoryid = $(this).data('id');
        generate_courses(instanceid, categoryid, (function() {
            $(this).siblings().removeClass('active');
            $(this).addClass('active');
            $(this).parents('.category-list').removeClass('show');
        }).bind(this));
    });

    $('body').on('click', COURSESECTIONASIDE, function() {
        $(this).parents('.category-list').toggleClass('show');
    }).on('afterChange', '[data-sectionname="courses"].section-courses:not(.categories-view) .courses-slider', function (event, slick, currentSlide) {
        var section = $(this).closest('section[data-sectionname="courses"]');
        if ($(slick.$nextArrow).is('.slick-disabled')) {
            var categoryid = $(section).is('.categoryandcourses-view') ? $(section).find('.category-list .category-item.active').data('id') : 0;
            var instanceid = section.data('instance');
            var totalcourses = section.data('totalcourses');
            var current = section.data('current');
            if(current + COURSEPERPAGE < totalcourses) {
                Ajax.call([{
                    methodname: 'local_remuihomepage_get_frontpage_section_courses_in_category',
                    args: {
                        instanceid: instanceid,
                        categoryid: categoryid,
                        start: current + COURSEPERPAGE
                    },
                }])[0]
                .done(function(response) {
                    var rendered = 0;
                    SECTIONMANAGER.showSectionLoader(instanceid);
                    response = JSON.parse(response);
                    section.data('current', current + COURSEPERPAGE);
                    response.courses.forEach(function(course) {
                        Templates.render('theme_remui/course_card_grid', course)
                        .done(function(html, js) {
                            html = "<div><div>" + html + "</div></div>";
                            $(slick.$slider).slick('slickAdd', html);
                            ++rendered;
                            if (rendered == response.courses.length) {
                                SECTIONMANAGER.showSectionLoader(instanceid, false);
                            }
                        })
                        .fail(Notification.exception);
                    });
                })
                .fail(Notification.exception);
            }
        }
    });

    /**
     * Apply slick to courses list
     * @param  {Number} instanceid Section instance id
     * @param  {Object} options    Options of slick slider
     */
    function apply_slick_to_courses(instanceid, options) {
        var defaults = {
            dots: false,
            arrows: true,
            infinite: false,
            speed: 500,
            prevArrow: $("section[data-instance='" + instanceid + "'] .button-container .btn-prev"),
            nextArrow: $("section[data-instance='" + instanceid + "'] .button-container .btn-next"),
            rtl: ($("html").attr("dir") == "rtl") ? true : false,
            slidesToShow: 4,
            slidesToScroll: 4,
            responsive: [{
                breakpoint: 1024,
                settings: {
                    slidesToShow: 3,
                    slidesToScroll: 3
                }
            }, {
                breakpoint: 800,
                settings: {
                    slidesToShow: 2,
                    slidesToScroll: 2
                }
            }, {
                breakpoint: 480,
                settings: {
                    slidesToShow: 1,
                    slidesToScroll: 1
                }
            }]
        };
        if (options != null && typeof options == 'object') {
            Object.assign(defaults, options);
        }
        var section = SECTIONMANAGER.getSectionElement(instanceid);
        section.find('.available-courses').addClass('d-none');
        if (section.find('.courses-slider .empty-courses-container').length != 0) {
            return;
        }
        section.find('.courses-slider').slick(defaults)
        .on('setPosition', function (event, slick) {
            $(section).find('.slick-slide > div').css('height', '100%');
            slick.$slides.css('height', slick.$slideTrack.height() + 'px');
        });
        section.find('.available-courses').removeClass('d-none');
    }

    /**
     * Generate courses slick slider
     * @param  {Number}   instanceid Instance id of section
     * @param  {Number}   categoryid Category id
     * @param  {Function} callback   Callback function
     */
    function generate_courses(instanceid, categoryid, callback) {
        SECTIONMANAGER.showSectionLoader(instanceid, true);
        var section = SECTIONMANAGER.getSectionElement(instanceid);
        section.find('.courses-slider').removeClass('show');
        Ajax.call([{
            methodname: 'local_remuihomepage_get_frontpage_section_courses_in_category',
            args: {
                instanceid: instanceid,
                categoryid: categoryid
            },
            done: function(response) {

                response = JSON.parse(response);

                section.find('.courses-slider.slick-initialized.slick-slider').slick('unslick');
                section.find('.courses-slider').empty();
                section.data('totalcourses', response.totalcourse);
                section.data('current', 0);
                Templates.render('local_remuihomepage/hs_courses_cards', response)
                .done(function(html, js) {
                    Templates.appendNodeContents(section.find('.courses-slider'), html, js);
                    apply_slick_to_courses(instanceid, {
                        slidesToShow: 3,
                        slidesToScroll: 3
                    });
                    if (typeof callback == 'function') {
                        callback();
                    }
                })
                .fail(Notification.exception);

                SECTIONMANAGER.showSectionLoader(instanceid, false);
            },
            fail: function(ex) {
                SECTIONMANAGER.showSectionLoader(instanceid, false);
                Notification.exception(ex);
            }
        }]);
    }

    // Open modal to show frontpage settings.
    $(document).on('click', SETTINGS, function() {
        ModalFactory.create({
            type: ModalFactory.types.SAVE_CANCEL,
            title: M.util.get_string('homepagesettings', 'local_remuihomepage'),
            body: Fragment.loadFragment('local_remuihomepage', 'frontpage_settings_form', CONTEXTID)
        }, $('#create'))
        .done(function(modal) {
            modal.modal.addClass("modal-sidebar d-inline-flex editmodal");
            modal.show();
            modal.getRoot().on(ModalEvents.save, function(e) {
                e.preventDefault();

                var form = modal.getRoot().find('form');

                // Convert all the form elements values to a serialised string.
                var formdata = JSON.stringify(form.serialize());
                Ajax.call([{
                    methodname: 'local_remuihomepage_save_frontpage_settings',
                    args: {
                        settings: formdata
                    }
                }])[0]
                .done(function(response) {
                    window.location.reload();
                })
                .fail(Notification.exception);
                modal.hide();
            });

            // Destroy the modal when the modal is closed.
            modal.getRoot().on(ModalEvents.hidden, function() {
                modal.destroy();
            });
        });
    });

    $( document ).on( "click", SECTIONSELECT, function(){
        // Get the data attribute.
        var dataatr = $(this).data('section');

        // Hide the existing section modal.
        MAINMODAL.hide();

        // Set Name of section.
        SECTIONMANAGER.setSectionName(dataatr);

        // Call method to add the section.
        SECTIONMANAGER.addSection();
    });

    // Move section up and downside.
    $('body').on('click', MOVESECTIONUP, function() {
        SECTIONMANAGER.reorderSection($(this).data('instance'), -1);
    }).on('click', MOVESECTIONDOWN, function() {
        SECTIONMANAGER.reorderSection($(this).data('instance'), 1);
    });

    // Change the visibility of section.
    $('body').on('click', VISIBILITY, function() {
        var instanceid = $(this).data('instance');
        var visible = $(this).data('visible');
        SECTIONMANAGER.updateVisibility(instanceid, !visible);
    });

    /**
     * Apply style to editor from style element
     * @param  {Object} element Jquery element object
     */
    var applyStyleToEditor = function(element) {
        var number = $(element).attr('id').replace('id_block_', '').replace('_style', '');
        var prefix = '#id_block_' + number + '_htmleditable ';
        var style = $(element).val().replace(/(([\.\#]?[\w\d\_\-]+\s*)+\{)/, function(match) {
            return prefix + match;
        });
        if (!$(element).next().is('#block-' + number + '-style')) {
            $(element).after('<style id="block-' + number + '-style" type="text/css"></style>');
        }
        $(element).next().html(style);
    };

    // Apply style to editor on change.
    $('body').on('input', '.block-style textarea', function() {
        applyStyleToEditor($(this));
    });

    /**
     * Apply style to separator
     * @param  {Object} form Form element
     */
    var applyStyleToSeparator = function(form) {
        var style = $(form).find('#id_style').val();
        var color = $(form).find('[name="color"]').val();
        var color2 = $(form).find('[name="color2"]').val();
        var height = $(form).find('#id_height').val();
        var width = $(form).find('#id_width').val();
        var css = {
            margin: '0px auto',
            width: width + '%'
        };
        $(form).find('[name="color2"]').parents('.separator-style').toggleClass('d-none', style != 'gradient');
        switch (style) {
            case 'blur':
                // Reset.
                css.height = '0px';
                css['background-image'] = 'none';
                // Set.
                css.border = '1px solid ' + color;
                css['box-shadow'] = '0 0 ' + height + 'px ' + (height / 2) + 'px ' + color;
                break;
            case 'blurend':
                // Reset.
                css.background = 'none';
                css['box-shadow'] = 'none';
                css.border = 'none';
                // Set.
                css.height = height + 'px';
                css['background-image'] = 'linear-gradient(to right, #ffffff, ' + color + ', #ffffff)';
                break;
            case 'gradient':
                // Reset.
                css['box-shadow'] = 'none';
                css['background-image'] = 'none';
                css.border = 'none';
                // Set.
                css.height = height + 'px';
                css.background = 'linear-gradient(to right, ' + color + ' 0%, ' + color2 + ' 50%, ' + color + ' 100%)';
                break;
            default:
                // Reset.
                css['box-shadow'] = 'none';
                css['background-image'] = 'none';
                css.height = '0px';
                // Set.
                css['border-top'] = height + 'px ' + style + ' ' + color;
                break;
        }
        $(form).find('#remui_separator_output').css(css);
    };

    // Apply style to separator on change.
    $('body').on('input', '.separator-style select, .separator-style input', function() {
        applyStyleToSeparator($(this).closest('form'));
    });

    $('body').on('change', '.updateform select', function() {
        var instanceid = $(this).parents('form').find('[name="instanceid"]').val();
        var formdata = JSON.stringify(CONFIGMODAL.getRoot().find('form').serialize());
        var frag = getBody(instanceid, formdata);
        CONFIGMODAL.setBody(frag);
    });

    // Section Edit Event Trigger.
    $( document ).on( "click", EDITSECTIONTRIGGER, function(){
        var id = getInstanceId(this);
        var sectionname = getSectionName(this);
        ModalFactory.create({
            type: ModalFactory.types.SAVE_CANCEL,
            title: 'Edit ' + sectionname.charAt(0).toUpperCase() + sectionname.slice(1) + ' Section',
            body: getBody(id, null),
        })
        .then(function(modal) {
            CONFIGMODAL = modal;
            modal.modal.addClass("modal-sidebar d-inline-flex editmodal");
            var root = modal.getRoot();

            // Save the details to the database when save button is clicked.
            root.on(ModalEvents.save, function(e) {
                e.preventDefault();

                var form = root.find('form');

                if (typeof remui_section_form_validate != 'undefined' && !remui_section_form_validate(form)) {
                    return;
                }
                // Convert all the form elements values to a serialised string.
                var formdata = JSON.stringify(form.serialize());
                SECTIONMANAGER.updateSection(id, formdata);
                CONFIGMODAL.hide();
            });

            // Destroy the modal when the modal is closed.
            root.on(ModalEvents.hidden, function() {
                CONFIGMODAL.destroy();
                $(document).find('.mform.atto_form.atto_media').parents('.moodle-dialogue-base').remove();
            });
            modal.show();
        });
    });

    // Section Delete Event Trigger.
    $( document ).on( "click", DELETESECTIONTRIGGER, function(){

        var id = getInstanceId(this);

        ModalFactory.create({
            type: ModalFactory.types.SAVE_CANCEL,
            title: 'Delete item',
            body: 'Do you really want to delete?',
        })
        .then(function(modal) {
            modal.setSaveButtonText('Delete');
            var root = modal.getRoot();
            root.on(ModalEvents.save, function() {
                SECTIONMANAGER.deleteInstance(id);
            });

            root.on(ModalEvents.hidden, function() {
                modal.destroy();
            });
            modal.show();
        });
    });

    // Cancel section deletion.
    $(document).on("click", DELETESECTIONCANCEL, function() {
        var id = getInstanceId(this);
        SECTIONMANAGER.deleteInstance(id, false);
    });

    // Skip timer and confirm section deletion.
    $(document).on("click", DELETESECTIONCONFIRM, function() {
        var id = getInstanceId(this);
        SECTIONMANAGER.deleteInstance(id, 2);
    });

    // Return Instance of Selected element, i.e. data-instance attribute.
    function getInstanceId(element){
        return $(element).attr("data-instance");
    }
    // Return Instance of Selected element, i.e. data-instance attribute.
    function getSectionName(element){
        return $(element).attr("data-sectionname");
    }

    /**
     * Apply some events when form is loaded in modal
     */
    function editFormIsLoaded() {
        setTimeout(function() {
            var styles = $('body').find('form .block-style textarea');
            if (styles.length != 0) {
                    styles.each(function(index, element) {
                        applyStyleToEditor($(element));
                    });
            }
        }, 2000);
    }

    var getBody = function(id, args) {
        var params = {instanceid: id};
        if (args != null) {
            params.formdata = args;
        }
        var body = Fragment.loadFragment('local_remuihomepage', 'frontpage_section_form', CONTEXTID, params);
        body.done(function(html, js) {
            editFormIsLoaded();
        });
        return body;
    }

    // Add Sections Modal Creation Function.
    // Context is the list of sections to be added.
    var initModal = function(context) {
        var triggerButtons = $(ADDSECTIONTRIGGER);

        ModalFactory.create({
            large: true,
            body: Templates.render('local_remuihomepage/hs_sections_list', context)
        }, triggerButtons)
        .then(function(modal) {
            MAINMODAL = modal;
            modal.setTitle(M.util.get_string('addsection', 'local_remuihomepage'));
            modal.modal.addClass("modal-sidebar d-inline-flex");
            modal.modal.width('500px');

            return;
        }.bind(this))
        .fail(Notification.exception);
    };

    /**
     * Initialize section loader
     */
    var InitSectionsLoader = function () {
        SECTIONMANAGER.LoadAllSections();
    };

    // Initialize the Modal.
    var InitializeModal = function() {
        initModal({
            sections: [
            {
                section:"slider",
                title: M.util.get_string('slidersection', 'local_remuihomepage'),
                imageurl: M.util.image_url('screenshots/slider', 'local_remuihomepage')
            },
            {
                section:"aboutus",
                title: M.util.get_string('aboutussection', 'local_remuihomepage'),
                imageurl: M.util.image_url('screenshots/aboutus', 'local_remuihomepage')
            },
            {
                section:"contact",
                title: M.util.get_string('contactsection', 'local_remuihomepage'),
                imageurl: M.util.image_url('screenshots/contact', 'local_remuihomepage')
            },
            {
                section:"feature",
                title: M.util.get_string('featuresection', 'local_remuihomepage'),
                imageurl: M.util.image_url('screenshots/feature', 'local_remuihomepage')
            },
            {
                section:"courses",
                title: M.util.get_string('coursessection', 'local_remuihomepage'),
                imageurl: M.util.image_url('screenshots/courses', 'local_remuihomepage')
            },
            {
                section:"team",
                title: M.util.get_string('teamsection', 'local_remuihomepage'),
                imageurl: M.util.image_url('screenshots/team', 'local_remuihomepage')
            },
            {
                section:"testimonial",
                title: M.util.get_string('testimonialsection', 'local_remuihomepage'),
                imageurl: M.util.image_url('screenshots/testimonial', 'local_remuihomepage')
            },
            {
                section:"html",
                title: M.util.get_string('htmlsection', 'local_remuihomepage'),
                imageurl: M.util.image_url('screenshots/html', 'local_remuihomepage')
            },
            {
                section:"separator",
                title: M.util.get_string('separatorsection', 'local_remuihomepage'),
                imageurl: M.util.image_url('screenshots/separator', 'local_remuihomepage')
            }
            ]
        });
    };

    // Intialize the JS, with context id.
    return {
        init: function(contextid, userisediting) {
            if (userisediting) {
                InitializeModal();
            }
            CONTEXTID = contextid;
            // Load on page ready.
            InitSectionsLoader();
        }
    };
});
