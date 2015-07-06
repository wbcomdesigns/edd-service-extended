<?php
	
	/**
	 * Prints the box content.
	 * 
	 * @param WP_Post $post The object for the current post/page.
	 */
	if( !function_exists( 'fes_edd_add_custom_info_box' ) )
	{
		function fes_edd_add_custom_info_box( $form_id, $user_id, $args ) {
		
			// Add a nonce field so we can check for it later.
			wp_nonce_field( 'fes_edd_meta_box', 'fes_edd_meta_box_nonce' );
		
			echo '<fieldset class="fes-el">        
					<div class="fes-label">
						<label for="fes-post_custom_info">' . __( 'Initial Message/ Requirement', WBCOM_EDD_DASH_MSG_TEXT_DOMIAN ) . ' <span class="edd-required-indicator">*</span></label>
						<br>
					</div>
					<div class="fes-fields">
						 <textarea class="textareafield edd-required-indicator" id="post_custom_info" name="fes_edd_initial_message" data-required="yes" data-type="textarea" required="required" placeholder="" rows="5" cols="25"></textarea>
					</div>
		
			</fieldset>';
		}
	}
	
	/** edit the form**/
	if( !function_exists( 'fes_edd_edit_custom_info_box' ) )
	{
		function fes_edd_edit_custom_info_box( $form_id, $id, $user_id, $args ) {
		
			// Add a nonce field so we can check for it later.
			wp_nonce_field( 'fes_edd_meta_box', 'fes_edd_meta_box_nonce' );
		
			/*
			 * Use get_post_meta() to retrieve an existing value
			 * from the database and use the value for the form.
			 */
			 if( $id != "" )
			$value = get_post_meta( $id, '_fes_edd_initial_message', true );
		
			echo '<fieldset class="fes-el">        
					<div class="fes-label">
						<label for="fes-post_custom_info">' . __( 'Initial Message/ Requirement', WBCOM_EDD_DASH_MSG_TEXT_DOMIAN ) . '<span class="edd-required-indicator">*</span></label>
						<br>
					</div>
					<div class="fes-fields">
						 <textarea class="textareafield edd-required-indicator" id="post_custom_info" name="fes_edd_initial_message" data-required="yes" data-type="textarea" required="required" placeholder="" rows="5" cols="25">' . esc_attr( $value ) . '</textarea>
					</div>
				</fieldset>';
		}
	}
	
	/**
	 * When the post is saved, saves our custom data.
	 *
	 * @param int $post_id The ID of the post being saved.
	 */
	if( !function_exists( 'fes_edd_save_custom_info_box_data' ) )
	{
		function fes_edd_save_custom_info_box_data( $post_id ) {
		
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
	add_action( 'fes_submission_form_save_custom_fields', 'fes_edd_save_custom_info_box_data', 0, 1 );
	add_action( 'fes_submission_form_new_bottom', 'fes_edd_add_custom_info_box', 0, 3 );
	add_action( 'fes_submission_form_existing_bottom', 'fes_edd_edit_custom_info_box', 0, 4 );
?>