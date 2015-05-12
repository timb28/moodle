<?php

// TODO remove entire file after testing

require_once("../../config.php");
require_once("$CFG->dirroot/blocks/istart_reports/lib.php");

opcache_reset();
istart_reports_cron();
