/* eslint-disable */
var funkhausAdmin = {
	enabledHomeUrlEdit: function(){
		jQuery('input#home').prop('readonly', false)
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
	}
}
jQuery(document).ready(function() {
	funkhausAdmin.showAttachmentIds();
})
jQuery(window).load(function(){
    funkhausAdmin.enabledHomeUrlEdit()
})