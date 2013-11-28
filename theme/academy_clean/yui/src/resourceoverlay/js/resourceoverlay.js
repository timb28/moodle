var RESOURCEOVERLAYNAME = 'Academy theme resource overlay',
    ACTIVITYSELECTOR = '.activity.url .activityinstance a',
    IFRAMECLASS = 'overlay',
    IFRAMEPADDING = 12, // px
    RESOURCEOVERLAY;

RESOURCEOVERLAY = function() {
    RESOURCEOVERLAY.superclass.constructor.apply(this, arguments);
};

Y.extend(RESOURCEOVERLAY, Y.Base, {
    
    dialogue : null,

    // The initial overflow setting
    initialoverflow : '',

    initializer : function() {
        var self = this;

        var resourcenodes = Y.all(ACTIVITYSELECTOR).each(processNodes);

        Y.delegate('mousedown', function(e){
            // Stop the event's default behavior
            e.preventDefault();

            // Stop the event from bubbling up the DOM tree
            e.stopPropagation();
            
            var params = this.getAttribute('onclick');
            
            // Get the resource attributes from the onclickurl
            var width = getValueFromOnClick(params, 'width');
            var paddedwidth = parseFloat(width) + parseFloat(IFRAMEPADDING * 2);
            var height = getValueFromOnClick(params, 'height');

            var location = this.getAttribute('href')+'&redirect=1';

            //display an overlay
            var title = '',
                content = Y.Node.create('<iframe class="'+IFRAMECLASS+'" width="'+width
                        +'" height="'+height+'" src="'+location+'" scrolling="no"></iframe>'),
                dialogue = new M.core.dialogue({
                    headerContent :  title,
                    bodyContent : content,
                    lightbox : true,
                    width : paddedwidth,
                    height : 'auto',
                    centered : true,
                    constrain : true,
                    draggable : false,
                    zindex : 1000, // Display in front of other items
                    shim : true,
                    closeButtonTitle : this.get('closeButtonTitle')
                });

            // Videos play in hidden iframe so destroy the node on close
            dialogue.get('buttons').header[0].on('click', function(e){
                var destroyAllNodes = true;
                dialogue.destroy(destroyAllNodes);

                // Re-enable the page scrollbars
                if (Y.UA.ie > 0) {
                    Y.one('html').setStyle('overflow', this.initialoverflow);
                } else {
                    Y.one('body').setStyle('overflow', this.initialoverflow);
                }

                this.container.detachAll();
            });

            // Destroy the dialog when anywhere outside it is clicked
            dialogue.get('maskNode').on("click", function(e) {
                setTimeout(function() {
                    var destroyAllNodes = true;
                    dialogue.destroy(destroyAllNodes);

                    // Re-enable the page scrollbars
                    if (Y.UA.ie > 0) {
                        Y.one('html').setStyle('overflow', this.initialoverflow);
                    } else {
                        Y.one('body').setStyle('overflow', this.initialoverflow);
                    }

                    this.container.detachAll();

                }, 50);
            });

            self.dialog = dialogue;
            dialogue.render(Y.one(document.body));
            center_dialogue(dialogue);

            // Get the overflow setting when the chooser was opened - we
            // may need this later
            if (Y.UA.ie > 0) {
                this.initialoverflow = Y.one('html').getStyle('overflow');
            } else {
                this.initialoverflow = Y.one('body').getStyle('overflow');
            }

            // This will detect a change in orientation and retrigger centering
            thisevent = Y.one('document').on('orientationchange', function() {
                center_dialogue(dialogue);
            }, this);

            // Detect window resizes (most browsers)
            thisevent = Y.one('window').on('resize', function() {
                center_dialogue(dialogue);
            }, this);

        }, Y.one('#page'), filterActivities);

    }
    
}, {
    NAME : RESOURCEOVERLAYNAME,
    ATTRS : {
        name : {
            validator : Y.Lang.isString,
            value : 'resourceoverlay'
        },
        minheight : {
            value : 300
        },
        baseheight: {
            value : 400
        },
        maxheight : {
            value : 660
        }
    }
});

/**
  * Calculate the optimum height of the chooser dialogue
  *
  * This tries to set a sensible maximum and minimum to ensure that some options are always shown, and preferably
  * all, whilst fitting the box within the current viewport.
  *
  * @param dialogue Y.Node The dialogue
  * @return void
  */
function center_dialogue(dialogue) {
  var bb = dialogue.get('boundingBox'),
      winheight = bb.get('winHeight'),
      winwidth = bb.get('winWidth'),
      offsettop = 0,
      newheight, totalheight, dialoguetop, dialoguewidth, dialogueleft;

  var container = dialogue.bodyNode;

  // Try and set a sensible max-height -- this must be done before setting the top
  // Set a default height of 640px
  newheight = container.get('maxheight');
  if (winheight <= newheight) {
      // Deal with smaller window sizes
      if (winheight <= container.get('minheight')) {
          newheight = container.get('minheight');
      } else {
          newheight = winheight;
      }
  }

  // Set a fixed position if the window is large enough
  if (newheight > container.get('minheight')) {
      bb.setStyle('position', 'fixed');
      // Disable the page scrollbars
      if (Y.UA.ie > 0) {
          Y.one('html').setStyle('overflow', 'hidden');
      } else {
          Y.one('body').setStyle('overflow', 'hidden');
      }
  } else {
      bb.setStyle('position', 'absolute');
      offsettop = Y.one('window').get('scrollTop');
      // Ensure that the page scrollbars are enabled
      if (Y.UA.ie > 0) {
          Y.one('html').setStyle('overflow', this.initialoverflow);
      } else {
          Y.one('body').setStyle('overflow', this.initialoverflow);
      }
  }

  // Take off 15px top and bottom for borders, plus 40px each for the title and button area before setting the
  // new max-height
  totalheight = newheight;
  newheight = newheight - (15 + 15 + 40 + 40);
  container.setStyle('maxHeight', newheight + 'px');

  dialogueheight = bb.getStyle('height');
  if (dialogueheight.match(/.*px$/)) {
      dialogueheight = dialogueheight.replace(/px$/, '');
  } else {
      dialogueheight = totalheight;
  }

  if (dialogueheight < dialogue.get('baseheight')) {
      dialogueheight = dialogue.get('baseheight');
      container.setStyle('height', dialogueheight + 'px');
  }


  // Re-calculate the location now that we've changed the size
  dialoguetop = Math.max(12, ((winheight - dialogueheight) / 2)) + offsettop;

  // We need to set the height for the yui3-widget - can't work
  // out what we're setting at present -- shoud be the boudingBox
  bb.setStyle('top', dialoguetop + 'px');

  // Calculate the left location of the chooser
  // We don't set a minimum width in the same way as we do height as the width would be far lower than the
  // optimal width for moodle anyway.
  dialoguewidth = bb.get('offsetWidth');
  dialogueleft = (winwidth - dialoguewidth) / 2;
  bb.setStyle('left', dialogueleft + 'px');
}

//handle_key_press : function(e) {
//  if (e.keyCode === 27) {
//      this.cancel_popup(e);
//  }
//},
//
//cancel_popup : function (e) {
//  // Prevent normal form submission before hiding
//  e.preventDefault();
//  this.hide();
//},
//
//hide : function() {
//  // Cancel all listen events
//  this.cancel_listenevents();
//
//  // Re-enable the page scrollbars
//  if (Y.UA.ie > 0) {
//      Y.one('html').setStyle('overflow', this.initialoverflow);
//  } else {
//      Y.one('body').setStyle('overflow', this.initialoverflow);
//  }
//
//  this.container.detachAll();
//  this.panel.hide();
//}


function processNodes(node) {
    if (node.getAttribute('onclick').length > 2) {
        /* TESTING. */
        node.append("&nbspPOPUP");
    }
}

function filterActivities(node) {
    if (node.getAttribute('onclick').length > 2
            && node.test(ACTIVITYSELECTOR)) {
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
