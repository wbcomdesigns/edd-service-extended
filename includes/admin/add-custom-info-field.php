<?php
	
	/* Adds a meta field to input the initial message*/
	if( !function_exists( 'fes_edd_edit_custom_info_box' ) )
	{
		function fes_edd_add_meta_box() {
		
				add_meta_box(
					'fes_edd_initial_msg',
					__( 'Initial Message/ Requirement', WBCOM_EDD_DASH_MSG_TEXT_DOMIAN ),
					'fes_edd_meta_box_callback',
					'download',
					'normal',
					'high'
				);
		}
	}
	add_action( 'add_meta_boxes', 'fes_edd_add_meta_box' );
	
	/**
	 * Prints the box content.
	 * 
	 * @param WP_Post $post The object for the current post/page.
	 */
	if( !function_exists( 'fes_edd_meta_box_callback' ) )
	{
		function fes_edd_meta_box_callback( $post ) {
		
			// Add a nonce field so we can check for it later.
			wp_nonce_field( 'fes_edd_meta_box', 'fes_edd_meta_box_nonce' );
		
			/*
			 * Use get_post_meta() to retrieve an existing value
			 * from the database and use the value for the form.
			 */
			$value = get_post_meta( $post->ID, '_fes_edd_initial_message', true );
		
			echo '<textarea id="fes_edd_initial_message" name="fes_edd_initial_message" style="width:100%" >' . esc_attr( $value ) . '</textarea>';
		}
	}
	
	/**
	 * When the post is saved, saves our custom data.
	 *
	 * @param int $post_id The ID of the post being saved.
	 */
	if( !function_exists( 'fes_edd_save_meta_box_data' ) )
	{
		function fes_edd_save_meta_box_data( $post_id ) {
		
			/*
			 * We need to verify this came from our screen and with proper authorization,
			 * because the save_post action can be triggered at other times.
			 */
		
			// Check if our nonce is set.
			if ( ! isset( $_POST['fes_edd_meta_box_nonce'] ) ) {
				return;
			}
		
			// Verify that the nonce is valid.
			if ( ! wp_verify_nonce( $_POST['fes_edd_meta_box_nonce'], 'fes_edd_meta_box' ) ) {
				return;
			}
		
			// If this is an autosave, our form has not been submitted, so we don't want to do anything.
			if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
				return;
			}
		
		
			/* OK, it's safe for us to save the data now. */
			
			// Make sure that it is set.
			if ( ! isset( $_POST['fes_edd_initial_message'] ) ) {
				return;
			}
		
			// Sanitize user input.
			$my_data = sanitize_text_field( $_POST['fes_edd_initial_message'] );
		
			// Update the meta field in the database.
			update_post_meta( $post_id, '_fes_edd_initial_message', $my_data );
		}
	}
	add_action( 'save_post', 'fes_edd_save_meta_box_data' );

?>