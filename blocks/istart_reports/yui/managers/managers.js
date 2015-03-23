/**
 * Prevent enter keyboard press from submitting the managers_form
 *
 * @module      block_istart_reports-managers
 * @author      Tim Butler <tim.butler@harcourts.net>
 */


YUI.add('moodle-block_istart_reports-managers', function(Y) {
    M.block_istart_reports = {
        init : function() {
            Y.one('#mform1').on("submit",  function(e) {
                if (e.keyCode == '13') {
                    alert('test');
                }
                    e.stopImmediatePropagation();
                    e.halt(true);
                    alert('the input element never receives this event.');
                    return false;
            });
        },
      }
}, '@VERSION@', {
    requires:['node','event']
});