<?php

$hasheading = ($PAGE->heading);
$hasnavbar = (empty($PAGE->layout_options['nonavbar']) && $PAGE->has_navbar());
$hasfooter = (empty($PAGE->layout_options['nofooter']));
$hassidepre = $PAGE->blocks->region_has_content('side-pre', $OUTPUT);
$hascenter = $PAGE->blocks->region_has_content('center', $OUTPUT);
$hassidepost = $PAGE->blocks->region_has_content('side-post', $OUTPUT);
$hasbottom = $PAGE->blocks->region_has_content('bottom', $OUTPUT);

$custommenu = $OUTPUT->custom_menu();
$hascustommenu = (empty($PAGE->layout_options['nocustommenu']) && !empty($custommenu));

$hasglobalsearchenabled = false;
$globalsearchqueryurl = $CFG->httpswwwroot.'/search/query.php';

$bodyclasses = array();
$bodyclasses[] = 'academy';
if ($hassidepre && !$hassidepost) {
    $bodyclasses[] = 'side-pre-only';
} else if ($hassidepost && !$hassidepre) {
    $bodyclasses[] = 'side-post-only';
} else if (!$hassidepost && !$hassidepre) {
    $bodyclasses[] = 'content-only';
}

$hasfootnote = (!empty($PAGE->theme->settings->footnote));

/*
 * Include role specific CSS.
 */ 
$ha_rolecss = null;
if (!empty($USER->id)) {
    if (!empty($COURSE->id) and has_capability('moodle/course:markcomplete', get_context_instance(CONTEXT_COURSE, $COURSE->id))) {
        //no teacher specific CSS
    } else {
        $ha_rolecss = 'student.css';
    }

    if (is_guest(get_context_instance(CONTEXT_COURSE, $COURSE->id), $USER)) {
        $ha_rolecss = 'guest.css';
    }
} else {
    // $USER->id = 0 on the home page even when the default role is guest.
    $ha_rolecss = 'guest.css';
}

if ($ha_rolecss != null) {
    $ha_rolecss = '<link rel="stylesheet" type="text/css" href="'.$CFG->httpswwwroot.'/theme/academy/style/'.$ha_rolecss . '" />';
}

/*
 * Harcourts One authentication
 */
$thisPageURL = rawurlencode($PAGE->url);

// If a Harcourts One server has been specified, authorise the user.
if ($PAGE->theme->settings->harcourtsoneprotected != 0) {
    academy_h1_authorisation($thisPageURL);
}

echo $OUTPUT->doctype() ?>
<html <?php echo $OUTPUT->htmlattributes() ?>>
<head>
    <title><?php echo $OUTPUT->page_title() ?></title>
    <link rel="shortcut icon" href="<?php echo $OUTPUT->favicon(); ?>" />
    <meta name="description" content="<?php echo strip_tags(format_text($SITE->summary, FORMAT_HTML)) ?>" />
    <?php echo $OUTPUT->standard_head_html() ?>
    <?php echo $ha_rolecss ?>
    <link href='http://fonts.googleapis.com/css?family=Covered+By+Your+Grace' rel='stylesheet' type='text/css' />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex">
</head>
<body id="<?php echo $PAGE->bodyid ?>" class="<?php echo $PAGE->bodyclasses.' '.join(' ', $bodyclasses) ?>">
    <?php echo $OUTPUT->standard_top_of_body_html() ?>
    <div id="page">
        <?php if ($hasheading || $hasnavbar) { ?>
        <div id="page-header">
            <div id="page-header-wrapper" class="wrapper clearfix">
                <?php if ($hasheading) { ?>
                <div id="headermenu">
                <?php if (isloggedin() && $hasglobalsearchenabled) {
                    echo html_writer::start_tag('form', array('id'=>'globalsearchquery', 'method'=>'get', 'action'=>$globalsearchqueryurl));
                    echo html_writer::start_tag('div', array('id'=>'search-wrapper'));
                    echo html_writer::empty_tag('input', array('id'=>'site-search-q', 'name'=>'query_string', 'type'=>'text'));
                    echo html_writer::empty_tag('input', array('id'=>'site-search-submit', 'value'=>'go', 'type'=>'submit'));
                    echo html_writer::end_tag('div');
                    echo html_writer::tag('label', 'Search', array('for'=>'site-search-q'));
                    echo html_writer::end_tag('form');
                } ?>
                    <?php echo $OUTPUT->lang_menu();?>
                    <div id="userinfo">
                    <?php if (isloggedin()) {
                        $username = $USER->firstname . ' ' . $USER->lastname;
                        if (isguestuser($USER)) {
                            echo html_writer::tag('span', get_string('usergreeting', 'theme_academy', $USER->firstname), array('class'=>'username')).' (';
                            echo html_writer::link(new moodle_url('/login/'), get_string('loginhere', 'theme_academy')).')';
                        } else {
                            echo html_writer::link(new moodle_url('/user/profile.php', array('id'=>$USER->id)), $username, array('class'=>'username')).' (';
                            echo html_writer::link(new moodle_url('/login/logout.php', array('sesskey'=>sesskey())), get_string('logout')). ')';
                        }
                    } else {
                        echo html_writer::start_tag('div', array('id'=>'userdetails_loggedout'));
                        $loginlink = html_writer::link(new moodle_url('/login/'), get_string('loginhere', 'theme_academy'));
                        echo html_writer::tag('p', get_string('welcome', 'theme_academy', $loginlink));
                        echo html_writer::end_tag('div');;
                    } ?>
                    </div>
                </div>
                <div id="logobox">
                    <?php echo html_writer::link(new moodle_url('/'), "<img src='".$OUTPUT->pix_url('logo', 'theme')."' alt='logo' />"); ?>
                </div>
                <?php } // End of if ($hasheading)?>
                <!-- DROP DOWN MENU -->
                <div class="clearer"></div>
                <div id="sitemenu">
                <?php echo $PAGE->theme->settings->sitemenu; ?>
                </div>
                <div class="navbar">
                    <div class="wrapper clearfix">
                        <div class="breadcrumb"><?php if ($hasnavbar) echo $OUTPUT->navbar(); ?></div>
                        <div class="navbutton"> <?php echo $PAGE->button; ?></div>
                    </div>
                </div>
                <!-- END DROP DOWN MENU -->
            </div>
        </div>
    <?php } // if ($hasheading || $hasnavbar) ?>
        <!-- END OF HEADER -->
        <!-- START OF CONTENT -->
        <div id="page-content">
            <h4 class="headermain inside"><?php echo $PAGE->heading ?></h4>
            <h5 class="subheadermain"><?php echo $COURSE->summary ?></h5>
            <div class="clearer"></div>
            <div id="region-main-box">
                <div id="region-post-box">
                    <div id="region-main-wrap">
                        <div id="region-main">
                            <div class="region-content">
                                <?php echo core_renderer::MAIN_CONTENT_TOKEN ?>
                            </div>
                            <?php if ($hascenter) { ?>
                                <div id="region-content" class="block-region">
                                <div class="region-content">
                                <?php echo $OUTPUT->blocks_for_region('center') ?>
                                </div>
                                </div>
                            <?php } ?>
                        </div>
                    </div>

                    <?php if ($hassidepre) { ?>
                    <div id="region-pre" class="block-region">
                        <div class="region-content">
                            <?php echo $OUTPUT->blocks_for_region('side-pre') ?>
                        </div>
                    </div>
                    <?php } ?>

                    <?php if ($hassidepost) { ?>
                    <div id="region-post" class="block-region">
                        <div class="region-content">
                            <?php echo $OUTPUT->blocks_for_region('side-post') ?>
                        </div>
                    </div>
                    <?php } ?>
                </div>
            </div>
        </div>
        <!-- END OF CONTENT -->
        <div class="clearfix"></div>
    <!-- END OF #Page -->
    </div>
    <!-- START OF BOTTOM -->
    <?php if ($hasbottom) { ?>
    <div id="page-bottom">
        <div class="block-region">
            <?php echo $OUTPUT->blocks_for_region('bottom') ?>
            <div class="clearfix"></div>
        </div>
    </div>
    <?php } // if $hasbottom ?>
    <!-- START OF FOOTER -->
    <?php if ($hasfooter) { ?>
    <div id="page-footer">
	<div id="footer-wrapper">
            <?php if ($hasfootnote) { ?>
            <div id="footnote"><?php echo $PAGE->theme->settings->footnote; ?></div>
            <?php } ?>
            <p class="helplink"><?php echo page_doc_link(get_string('moodledocslink')) ?></p>
            <?php
            //echo $OUTPUT->login_info();
            //echo $OUTPUT->home_link();
            echo $OUTPUT->standard_footer_html();
            ?>
            <div id="copyrightnotice">Copyright &copy; Harcourts International Pty Ltd</div>
        </div>
    </div>
    <?php } ?>
<?php echo $OUTPUT->standard_end_of_body_html() ?>
</body>
</html>
