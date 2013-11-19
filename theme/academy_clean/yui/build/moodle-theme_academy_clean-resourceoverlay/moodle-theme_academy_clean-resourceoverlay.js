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
        
        var resourcenode = Y.all('.activity.resource .activityinstance a');
        
        // Save then remove the existing onClick attribute
        var onclickurl = resourcenode.getAttribute('onclick');
        resourcenode.removeAttribute('onclick');
        resourcenode.setAttribute('params',onclickurl);

        /* Setup the modal pop-up. */
        resourcenode.append(" ResourceNodeFound");
        
        Y.delegate('click', function(e){
            e.preventDefault();
            
            var params = resourcenode.getAttribute('params');
            
            // Get the resource attributes from the onclickurl
            var widthregex = /width=(\d+)/i;
            var width = params[0].match(widthregex)[1];
            
            var heightregex = /height=(\d+)/i;
            var height = params[0].match(heightregex)[1];

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

        }, Y.one(document.body), '.activity.resource .activityinstance a');

    },
    hide : function(){
        RESOURCEOVERLAY.superclass.hide.call(this);
        this.destroy();
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


M.theme_academy_clean = M.theme_academy_clean || {};
M.theme_academy_clean.resourceoverlay = {
    init: function(config) {
        return new RESOURCEOVERLAY(config);
    }
};


}, '@VERSION@', {"requires": ["base", "node", "event-delegate", "moodle-core-dialogue", "moodle-core-notification"]});
