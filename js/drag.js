// Jquery( document ).ready(function() {
jQuery(function() {
	jQuery( "#accordion" ).accordion();
	jQuery('#tabs').tabs();
});
var   x = 0, y = 0;
interact(".draggable").draggable({
    snap: {
            targets: [],
            // relativePoints: [ { x: 0.5, y: 0.5 } ],
            endOnly: true
	},
    inertia: true,
	onstart: function (event) {
			x = parseFloat(jQuery(event.target).attr( "data-x" ));
			y = parseFloat(jQuery(event.target).attr( "data-y" ));
			// x = 0; y = 0;
			// console.log(event);
			jQuery("#lowerthird_body").val(jQuery("#space-dropzone").html());
		
	},
	onmove: function (event) {
			x += event.dx;
			y += event.dy;			
			jQuery(event.target).attr( "data-x",x );
			jQuery(event.target).attr( "data-y",y );
			event.target.style.webkitTransform =
			event.target.style.transform = 'translate(' + x + 'px, ' + y + 'px)';
			jQuery("#lowerthird_body").val(jQuery("#space-dropzone").html());
     },
	 onend: function (event) {
			jQuery(event.target).attr( "data-x",x );
			jQuery(event.target).attr( "data-y",y );
			// (event.target)
			
            if (event.dropzone) {
					// console.log("in dropzone");
					console.log(event.dropzone);
            }else {
                 // console.log("Not in dropzone");
				 event.target.style.webkitTransform =
				event.target.style.transform =
				'translate(0px, 0px)';
				jQuery(event.target).attr( "data-x",0 );
				jQuery(event.target).attr( "data-y",0 );
            }
			jQuery("#lowerthird_body").val(jQuery("#space-dropzone").html());
        }
	
 
  });

	
	
	
interact('.dropzone').dropzone({
  // only accept elements matching this CSS selector
  accept: '.drag-drop',
  // Require a 75% element overlap for a drop to be possible
  overlap: 0.75,

  // listen for drop related events:

  ondropactivate: function (event) {
    // add active dropzone feedback
    event.target.classList.add('drop-active');
  },
  ondragenter: function (event) {
    var draggableElement = event.relatedTarget,
        dropzoneElement = event.target;

    // feedback the possibility of a drop
    dropzoneElement.classList.add('drop-target');
    draggableElement.classList.add('can-drop');
    // draggableElement.innerHTML  =jQuery(event.relatedTarget).attr( "data-example" );
	
	
	// console.log(relativeY);
	// console.log(relativeX);
  },
  ondragleave: function (event) {
    // remove the drop feedback style
    event.target.classList.remove('drop-target');
    event.relatedTarget.classList.remove('can-drop');
    event.relatedTarget.textContent = jQuery(event.relatedTarget).attr( "data-title" );
	var id=jQuery(event.relatedTarget).attr('id');
	jQuery("#"+id+"-val").val('0,0')
  },
  ondrop: function (event) {
	var draggableElement = event.relatedTarget,
        dropzoneElement = event.target;
		draggableElement.innerHTML = jQuery(draggableElement).attr( "data-example" );
		var relativeY = jQuery(draggableElement).offset().top - jQuery(dropzoneElement).offset().top;
		var relativeX = jQuery(draggableElement).offset().left  - jQuery(dropzoneElement).offset().left ;
		var id=jQuery(draggableElement).attr('id');
		jQuery("#"+id+"-val").val(relativeX+','+relativeY);
		console.log(jQuery("#"+id+"-val"));
		// console.log(relativeY);
  },
  ondropdeactivate: function (event) {
    // remove active dropzone feedback
    event.target.classList.remove('drop-active');
    event.target.classList.remove('drop-target');
  }
});
// });