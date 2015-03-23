/**
 * Prevent enter keyboard press from submitting the managers_form
 *
 * @module      block_istart_reports-managers
 * @author      Tim Butler <tim.butler@harcourts.net>
 */


YUI.add('moodle-block_istart_reports-managers', function(Y) {
    M.block_istart_reports = {
        init : function() {
            Y.one('document').on("keyup",  function(e) {
                if (e.keyCode == '13') {
                    e.preventDefault();
                    e.stopPropagation();
                    e.stopImmediatePropagation();
                    e.cancelBubble = true;
                    e.returnValue = false;
                    e.halt(true);
                    alert('the document element never receives this event.');
                    return false;
                }
            });

            Y.one('#mform1').on("keyup",  function(e) {
                if (e.keyCode == '13') {
                    e.preventDefault();
                    e.stopPropagation();
                    e.stopImmediatePropagation();
                    e.cancelBubble = true;
                    e.returnValue = false;
                    e.halt(true);
                    alert('the form element never receives this event.');
                    return false;
                }
            });

            Y.one('#manager').on("keyup",  function(e) {
                if (e.keyCode == '13') {
                    e.preventDefault();
                    e.stopPropagation();
                    e.stopImmediatePropagation();
                    e.cancelBubble = true;
                    e.returnValue = false;
                    e.halt(true);
                    alert('the select element never receives this event.');
                    return false;
                }
            });
        },
      }
}, '@VERSION@', {
    requires:['node','event-key']
});