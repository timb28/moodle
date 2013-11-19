YUI.add('moodle-theme_academy_clean-resourceoverlay', function (Y, NAME) {

var RESOURCEOVERLAYNAME = 'Academy theme resource overlay',
    WIDTH = 'width',
    HEIGHT = 'height',
    MENUBAR = 'menubar',
    LOCATION = 'location',
    SCROLLBARS = 'scrollbars',
    RESIZEABLE = 'resizable',
    TOOLBAR = 'toolbar',
    STATUS = 'status',
    DIRECTORIES = 'directories',
    FULLSCREEN = 'fullscreen',
    DEPENDENT = 'dependent',
    RESOURCEOVERLAY;

RESOURCEOVERLAY = function() {
    RESOURCEOVERLAY.superclass.constructor.apply(this, arguments);
};

Y.extend(RESOURCEOVERLAY, Y.Base, {
    
    overlay : null,
    initializer : function() {
        var self = this;
        
        var resourcenodes = Y.all('.activity.resource .activityinstance a').each(processNodes);
        
        Y.delegate('click', function(e){
            // Stop the event's default behavior
            e.preventDefault();
            
            var params = e.target.getAttribute('params');
            
            // Get the resource attributes from the onclickurl
            var width = getValueFromOnClick(params, 'width');
            var height = getValueFromOnClick(params, 'height');

            fullurl = this.getAttribute('href')+'&redirect=1';

            //display an overlay
            var title = '',
                content = Y.Node.create('<iframe class="overlay" width="'+width+'" height="'+height+'" src="'+fullurl+'"></iframe>'),
                d = new M.core.dialogue({
                    headerContent :  title,
                    bodyContent : content,
                    lightbox : true,
                    width : width,
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
        url : {
            validator : Y.Lang.isString,
            value : M.cfg.wwwroot+'/mod/glossary/showentry.php'
        },
        name : {
            validator : Y.Lang.isString,
            value : 'glossaryconcept'
        },
        options : {
            getter : function() {
                return {
                    width : this.get(WIDTH),
                    height : this.get(HEIGHT),
                    menubar : this.get(MENUBAR),
                    location : this.get(LOCATION),
                    scrollbars : this.get(SCROLLBARS),
                    resizable : this.get(RESIZEABLE),
                    toolbar : this.get(TOOLBAR),
                    status : this.get(STATUS),
                    directories : this.get(DIRECTORIES),
                    fullscreen : this.get(FULLSCREEN),
                    dependent : this.get(DEPENDENT)
                };
            },
            readOnly : true
        },
        width : {value : 600},
        height : {value : 450},
        menubar : {value : false},
        location : {value : false},
        scrollbars : {value : true},
        resizable : {value : true},
        toolbar : {value : true},
        status : {value : true},
        directories : {value : false},
        fullscreen : {value : false},
        dependent : {value : true}
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
    if (node.hasAttribute('params') && node.test('.activity.resource .activityinstance a')) {
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
