<?php
function xmldb_auth_joomdle_upgrade($oldversion) {
    global $DB;
    $dbman = $DB->get_manager();

    if ($oldversion < 2008080273) {
		$sql = "DELETE FROM {events_handlers} WHERE component = 'joomdle'";
		$DB->execute($sql);
    }

    return true;
}
?>
