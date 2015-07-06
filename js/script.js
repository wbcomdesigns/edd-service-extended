jQuery(document).ready(function(e) {
    
	jQuery("#edd_message_attachment").change(function() {
		var names = [];
		jQuery('#edd_files_names').html('');
		for (var i = 0; i < jQuery(this).get(0).files.length; ++i) {
			/*var ext = jQuery(this).get(0).files[i].name.match(/\.(.+)$/)[1];
			switch (ext) {
				case 'jpg':
				case 'jpeg':
				case 'png':
				case 'gif':
				case 'pdf':*/
					jQuery('#edd_files_names').append('<label>'+jQuery(this).get(0).files[i].name+'</label>');
					/*break;
				default:
					alert(ext+' file is not an allowed file type.');
					jQuery("#edd_message_attachment").val('');
					jQuery('#edd_files_names').html('');
			}*/
		}
	});// JavaScript Document
	
	jQuery('.edd-messages-list a.edd-close-thread').on('click',function(){
		jQuery('#dialog-form').slideDown('slow');
	});
	
});
	jQuery(function () {
			var that = this;
			jQuery("#jRate").jRate({
				rating: 1,
				strokeColor: 'black',
				width: 20,
				height: 20,
				precision: 0.1,
				minSelected: 1,
				startColor: "yellow",
           		endColor: "yellow",
				onChange: function(rating) {
				},
				onSet: function(rating) {
					jQuery('#rating').val(rating);
				}
			});
			
		});