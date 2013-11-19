YUI.add('moodle-theme_academy_clean-resourceoverlay', function (Y, NAME) {

var RESOURCEOVERLAYNAME = 'Academy theme resource overlay',
    ACTIVITYSELECTOR = '.activity.resource .activityinstance a',
    IFRAMECLASS = 'overlay',
    LOCATION = 'location',
    WIDTH = 600,
    HEIGHT = 450,
    RESOURCEOVERLAY;

RESOURCEOVERLAY = function() {
    RESOURCEOVERLAY.superclass.constructor.apply(this, arguments);
};

Y.extend(RESOURCEOVERLAY, Y.Base, {
    
    overlay : null,
    initializer : function() {
        var self = this;
        
        var resourcenodes = Y.all(ACTIVITYSELECTOR).each(processNodes);
        
        Y.delegate('click', function(e){
            // Stop the event's default behavior
            e.preventDefault();
            
            var params = e.target.getAttribute('params');
            
            // Get the resource attributes from the onclickurl
            WIDTH = getValueFromOnClick(params, 'width');
            HEIGHT = getValueFromOnClick(params, 'height');

            LOCATION = this.getAttribute('href')+'&redirect=1';

            //display an overlay
            var title = '',
                content = Y.Node.create('<iframe class="'+IFRAMECLASS+'" width="'+WIDTH+'" height="'+HEIGHT+'" src="'+LOCATION+'"></iframe>'),
                d = new M.core.dialogue({
                    headerContent :  title,
                    bodyContent : content,
                    lightbox : true,
                    width : WIDTH,
                    height : 'auto',
                    centered : true,
                    modal: true,
                    draggable : false,
                    zindex : 5, // Display in front of other items
                    shim : false,
                    closeButtonTitle : this.get('closeButtonTitle'),
                    hideOn: [
                        {
                            eventName: 'clickoutside'
                        }
                    ]
                });

            // Videos play in hidden iframe so destroy the node on close
            d.get('buttons').header[0].on('click', function(e){
                var destroyAllNodes = true;
                d.destroy(destroyAllNodes);
            });

            self.dialog = d;
            d.render(Y.one(document.body));

        }, Y.one(document.body), filterActivities);

    }

}, {
    NAME : RESOURCEOVERLAYNAME,
    ATTRS : {
        name : {
            validator : Y.Lang.isString,
            value : 'resourceoverlay'
        },
        options : {
            getter : function() {
                return {
                    width : this.get(WIDTH),
                    height : this.get(HEIGHT),
                    location : this.get(LOCATION)
                };
            },
            readOnly : true
        },
        width : {value : 600},
        height : {value : 450},
        location : {value : null}
    }
});

function processNodes(node) {
    // If the node has an onClick attribute, rename it to avoid it being run
    if (node.getAttribute('onclick').length > 2) {
        var onclickurl = node.getAttribute('onclick');
        
        node.removeAttribute('onclick');
        node.setAttribute('params',onclickurl);

        /* TESTING. */
        node.append("&nbspPOPUP");            
    }
}

function filterActivities(node) {
    // Limit overlay to activities that open in a pop-up window
    if (node.hasAttribute('params') && node.test(ACTIVITYSELECTOR)) {
        return true;
    }
  
    return false;
}

function getValueFromOnClick(onClick, value) {
    var regex, results;
    
    // Get the value from the onclickurl
    regex = new RegExp(value + '=([0-9]+)', 'i');
    results = onClick.match(regex);
    
    if (results === null) {
        return null;
    }
    return results[1];
}

M.theme_academy_clean = M.theme_academy_clean || {};
M.theme_academy_clean.resourceoverlay = {
    init: function(config) {
        return new RESOURCEOVERLAY(config);
    }
};


}, '@VERSION@', {"requires": ["base", "node", "event-delegate", "moodle-core-dialogue", "moodle-core-notification"]});
