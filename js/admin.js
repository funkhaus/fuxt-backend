/* eslint-disable */
var stackhausAdmin = {
	enabledHomeUrlEdit: function(){
		jQuery('input#home').removeClass('disabled').prop('readonly', false).prop('disabled', false);
	},
	showAttachmentIds: function() {
	    // Show the attachment IDs on hover of attachment grid blocks
	    jQuery(document).on(
	        'mouseenter',
	        '.media-modal .attachment, .media-frame .attachment',
	        function() {
	            var id = jQuery(this).data('id')
	            if (id) {
	                jQuery(this).attr('title', 'Attachment ID: ' + id)
	            }
	        }
	    )
	},
	shiftClickNestedPages: function() {
        // Enable shift-clicking on NestedPages admin lists
        var lastChecked = null;
		jQuery("#wpbody-content").on("click", ".nestedpages .np-bulk-checkbox input[type='checkbox']", function(e){

			// Abort if first click
	        if (!lastChecked) {
	            lastChecked = this;
	            return;
	        }

	        // Handle shift clicking and auto selecting all following checkboxes
			var $chkboxes = jQuery(".nestedpages .np-bulk-checkbox input[type='checkbox']");
	        if (e.shiftKey) {
	            var start = $chkboxes.index(this);
	            var end = $chkboxes.index(lastChecked);
	            $chkboxes.slice(Math.min(start,end), Math.max(start,end)+ 1).prop('checked', lastChecked.checked);
	        }

	        lastChecked = this;
		});
	}
}
jQuery(document).ready(function() {
	stackhausAdmin.showAttachmentIds();
	stackhausAdmin.shiftClickNestedPages();
})
jQuery(window).load(function(){
    stackhausAdmin.enabledHomeUrlEdit()
})
