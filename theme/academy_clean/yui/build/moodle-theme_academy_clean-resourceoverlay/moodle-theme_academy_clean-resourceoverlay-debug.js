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

        /* Setup the modal pop-up. */
        resourcenode.append(" ResourceNodeFound");
        
        // Save then remove the existing onClick attribute
        var popupurl = resourcenode.getAttribute('onclick');
        resourcenode.removeAttribute('onclick');
        
        Y.delegate('click', function(e){
            e.preventDefault();
            
            fullurl = this.getAttribute('href')+'&redirect=1';

            //display a progress indicator
            var title = '',
                content = Y.Node.create('<iframe src="'+fullurl+'"></iframe>'),
                o = new Y.Overlay({
                    headerContent :  title,
                    bodyContent : content,
                    lightbox : true,
                    width : '540px',
                    centered : true,
                    draggable : false,
                    zindex : 100, // Display in front of other items
                    lightbox : true, // This dialogue should be modal
                    shim : true,
                    closeButtonTitle : this.get('closeButtonTitle'),
                    plugins : [
 
                        Y.Plugin.OverlayModal,
                        Y.Plugin.OverlayKeepaligned,
                        { fn: Y.Plugin.OverlayAutohide, cfg: {
                            focusedOutside : false  // disables the Overlay from auto-hiding on losing focus
                        }}

                    ],
                }),
                fullurl,
                cfg;
            self.overlay = o;
            o.render(Y.one(document.body));

//            cfg = {
//                method: 'get',
//                context : self,
//                on: {
//                    success: function(id, o) {
//                        this.display_callback(o.responseText);
//                    },
//                    failure: function(id, o) {
//                        var debuginfo = o.statusText;
//                        if (M.cfg.developerdebug) {
//                            o.statusText += ' (' + fullurl + ')';
//                        }
//                        this.display_callback('bodyContent',debuginfo);
//                    }
//                }
//            };
//            Y.io(fullurl, cfg);

        }, Y.one(document.body), '.activity.resource .activityinstance a');
    },
    display_callback : function(content) {
        var data,
            key,
            alertpanel;
        try {
            data = Y.JSON.parse(content);
            if (data.success){
                this.overlay.hide(); //hide progress indicator

                for (key in data.entries) {
                    definition = data.entries[key].definition + data.entries[key].attachments;
                    alertpanel = new M.core.alert({title:data.entries[key].concept, message:definition, lightbox:false});
                    Y.Node.one('#id_yuialertconfirm-' + alertpanel.COUNT).focus();
                }

                return true;
            } else if (data.error) {
                new M.core.ajaxException(data);
            }
        } catch(e) {
            new M.core.exception(e);
        }
        return false;
    }

// // Use Y.one( [css selector string] )
// Create a new YUI instance and populate it with the required modules.
//YUI().use('overlay', function (Y) {
    // Overlay is available and ready for use. Add implementation
    // code here.

    ////YUI().use('node', function (Y) {
//        var resourcenode = Y.all('.activity.resource .activityinstance a');
//
//        /* Setup the modal pop-up. */
//        resourcenode.append("Test");
//
//        /* Trigger the modal pop-up. */
//        resourcenode.on('click', function(e) {
//            e.preventDefault();
//
//            //display a progress indicator
//            var title = '',
//                content = Y.Node.create('<div id="overlayprogress"><img src="'+M.cfg.loadingicon+'" class="spinner" /></div>'),
//                o = new Y.Overlay({
//                    headerContent :  title,
//                    bodyContent : content
//                }),
//                    fullurl,
//                    cfg;
//            this.overlay = o;
//            o.render(Y.one(document.body));
//
//            alert('href: ' + e.target.ancestor('a').get('href') + ' event: ' + e.type + ' target: ' + e.target.get('tagName'));
//
//        });

    ////});

//});

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


}, '@VERSION@', {
    "requires": [
        "base",
        "node",
        "io-base",
        "json-parse",
        "event-delegate",
        "panel",
        "moodle-core-notification"
    ]
});
