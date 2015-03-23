/**
 * Prevent enter keyboard press from submitting the managers_form
 *
 * @module      block_istart_reports-managers
 * @author      Tim Butler <tim.butler@harcourts.net>
 */


YUI.add('moodle-block_istart_reports-managers', function(Y) {
    M.block_istart_reports = {
        init : function() {
        Y.one('#manager_searchtext').on("keyup",  function(e) {
                if (e.keyCode == '13') {
                    e.preventDefault();
                    alert('the input element never receives this event.');
                }
            });
        },
      }
}, '@VERSION@', {
    requires:['node']
});