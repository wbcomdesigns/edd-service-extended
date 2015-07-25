<?php
/*
Plugin Name: EDD Service Extended
Plugin URI: http://www.wbcomdesigns.com
Description: Easy Digital Download Message adds message section in the user dashboard for conversation.
Version: 1.0
Author: WBCOM DESIGNS
Author URI: http://www.wbcomdesigns.com
License: GPL2
http://www.gnu.org/licenses/gpl-2.0.html
*/ 
defined( 'ABSPATH' ) or die( 'Plugin file cannot be accessed directly.' );
	
	if ( !defined( '' ) ) {
	
		define( 'WBCOM_EDD_DASH_MSG' , '1.0' );
	}
	
	if ( !defined( 'WBCOM_EDD_DASH_MSG_PATH' ) ) {
	
		define( 'WBCOM_EDD_DASH_MSG_PATH' , plugin_dir_path( __FILE__ ) );
	}
	
	if ( !defined( 'WBCOM_EDD_DASH_MSG_URL' ) ) {
	
		define( 'WBCOM_EDD_DASH_MSG_URL' , plugin_dir_url( __FILE__ ));
	}
	
	if ( !defined( 'WBCOM_EDD_DASH_MSG_DB_VERSION' ) ) {
	
		define( 'WBCOM_EDD_DASH_MSG_DB_VERSION' , '1' );
	}
	
	if ( !defined( 'WBCOM_EDD_DASH_MSG_TEXT_DOMIAN' ) ) {
	
		define( 'WBCOM_EDD_DASH_MSG_TEXT_DOMIAN' , 'wb-edd-order-thread' );
	}
	/*Function to check Main Easy digtal Download plugin is active or not
	If not active it will not get active*/
	if( !function_exists( 'wbcom_edd_require_check' ) )
	{
		function wbcom_edd_require_check()
		{
			if( !class_exists( 'Easy_Digital_Downloads' ) )
				{
					require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		
					add_action( 'admin_notices',  'wbcom_edd_require_notice' );
		
					deactivate_plugins( plugin_basename( __FILE__ ) );
				}
		}
	}
	
	add_action( 'admin_init',  'wbcom_edd_require_check' );
	
	/*File have code to add and display initial message in the admin for single service
	This initial will be used as first message by the service provider on service purchase*/
	
	require_once WBCOM_EDD_DASH_MSG_PATH . 'includes/admin/add-custom-info-field.php';
	
	/* Checks the Front end posting plugin is active
	File have code to add and display initial message in the front end submition form for single service
	This initial will be used as first message by the service provider on service purchase*/
	
	if( class_exists( 'EDD_Front_End_Submissions' ) )
		require_once WBCOM_EDD_DASH_MSG_PATH . 'includes/add-custom-info-field-front.php';
	
	/*Function to create table for the conversation message to store and add db version in the option table*/
	if( !function_exists( 'wbcom_edd_install' ) )
	{
		function wbcom_edd_install() {
			global $wpdb;
			$installed_ver = get_option( "edd_message_db_version" );
			if ( $installed_ver != WBCOM_EDD_DASH_MSG_DB_VERSION ) 
			{
				$table_name = $wpdb->prefix . 'edd_dashboard_message';
				$charset_collate = $wpdb->get_charset_collate();
			
				$sql = "CREATE TABLE $table_name (
					id mediumint(9) NOT NULL AUTO_INCREMENT,
					author_id mediumint(9) NOT NULL,
					user_id mediumint(9) NOT NULL,
					site_id mediumint(9) NOT NULL,
					purchase_id mediumint(9) NOT NULL,
					msg_time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
					message text NOT NULL,
					attachment text NOT NULL,
					msg_read ENUM('0','1') NOT NULL,
					UNIQUE KEY id (id)
				) $charset_collate;";
			
				require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
				
				dbDelta( $sql );
				
				update_option( 'edd_message_db_version', WBCOM_EDD_DASH_MSG_DB_VERSION );
			}
		}
	}
		
	register_activation_hook( __FILE__, 'wbcom_edd_install' );
	
	/*Function to display the notice for the admin to activate EDD plugin*/
	if( !function_exists( 'wbcom_edd_require_notice' ) )
	{
		function wbcom_edd_require_notice() {
		
			echo '<div id="message" class="updated fade"><p style="line-height: 150%">';
		
			echo __( '<strong>Easy Digital Download</strong> plugin is not activated please activate it first.' ,WBCOM_EDD_DASH_MSG_TEXT_DOMIAN );
		
			echo '</p></div>';
		
		}
	}
	
	/*Function to add the style css and javascripts*/
	if( !function_exists( 'add_edd_message_style_script' ) )
	{
		function add_edd_message_style_script()
			{
				wp_enqueue_script( 'jquery' );
		
				wp_register_script('edd_message_script', WBCOM_EDD_DASH_MSG_URL . 'js/script.js', 'all');
		
				wp_enqueue_script('edd_message_script');
				
				wp_register_script('edd_rate_script', WBCOM_EDD_DASH_MSG_URL . 'js/jRate.min.js', 'all');
		
				wp_enqueue_script('edd_rate_script');
		
				wp_register_style('edd_message-css',WBCOM_EDD_DASH_MSG_URL.'css/style.css', 'all');
		
				wp_enqueue_style('edd_message-css');
			}
	}
	
	add_action('wp_enqueue_scripts', 'add_edd_message_style_script');
	
	/*Funtion to filter the buttons from the tiny mice editor*/
	if( !function_exists( 'edd_message_editor_buttons' ) )
	{
		function edd_message_editor_buttons( $buttons, $editor_id ) {
			
			return array( 'bold', 'italic', 'underline', 'bullist', 'numlist', 'link', 'unlink', 'forecolor', 'undo', 'redo'  );
		}
	}
	
	/* Function to add message input form with file upload 
	Display all the conversation thread with avatar and name
	*/ 
	if( !function_exists( 'add_user_comment_edd' ) )
	{
		function add_user_comment_edd()
		{
			global $edd_receipt_args;
			$meta_data	= edd_get_payment_meta_cart_details( $edd_receipt_args['id'], true );
			$user		= edd_get_payment_meta_user_info( $edd_receipt_args['id'] );
			$vendor		= get_post_field( 'post_author', $meta_data[0]['id'] );
			if( $user['id'] == get_current_user_id() || $vendor == get_current_user_id() )
			{
				$all_msg		= get_conversation_by_pay_id( $edd_receipt_args['id'] );
				$html			= '<div id="add_user_comment" class="add_user_comment">';
				$html			.= do_action( 'show_insert_message' );
				$thread_status 	= get_post_meta( $edd_receipt_args['id'], 'edd_user_order_thread', 'close' );
					if( $thread_status != 'close' )
					{
					  $html .= '<h3>' . __( 'Service Attachment', WBCOM_EDD_DASH_MSG_TEXT_DOMIAN ) . '</h3>
					  <form method="post" action="" enctype="multipart/form-data">';
					  ob_start();
					  $settings = array( 'media_buttons' => false, 'editor_height' => '150px', 'teeny' => true, 'quicktags'=>false );
					  add_filter( 'teeny_mce_buttons', 'edd_message_editor_buttons', 10, 2 );
					  wp_editor( '', 'message', $settings );
					  
					  $html .= ob_get_clean();
					  
					  $html .= '<input type="file" multiple name="attach[]" id="edd_message_attachment" style="display:none;" /><label for="edd_message_attachment" class="edd_message_attachment">' . __( 'Add Files', WBCOM_EDD_DASH_MSG_TEXT_DOMIAN ) . '</label><input type="submit" name="add_comment" value="Add" class="submit-msg" /><div id="edd_files_names"></div><input type="hidden" value="' . $vendor . '" name="edd_vendor"><input type="hidden" value="' . $edd_receipt_args['id'] . '" name="purchase_id">' . wp_nonce_field( 'edd_message_action', 'edd_message_nonce_field', false, false ) . '</form>';
					}
				  $html .= $all_msg . '</div>';
			}
			return $html;
		}
	}
	
	add_shortcode( 'add_user_comment_edd', 'add_user_comment_edd' );
	 
	if( !function_exists( 'edd_insert_true_msg' ) )
	{
		function edd_insert_true_msg()
		{
			return "Message inserted";
		} 
	}
	
	if( !function_exists( 'edd_insert_false_msg' ) )
	{
		function edd_insert_false_msg()
		{
			return "Message not inserted";
		}
	}
	
	/* Function to insert the user message in the table 
	There are two condition to add message first to add normal thread message and multiple attachment
	second to add the thread close message and rating for the user for the particlar order*/
	
	if( !function_exists( 'edd_add_user_message' ) )
	{
		function edd_add_user_message()
		{
			global $wpdb;
			/* start of the condition one */
			
			if ( 'POST' == $_SERVER['REQUEST_METHOD'] && !empty( $_POST['add_comment'] ) && isset( $_POST['add_comment'] ) )
				{
					if( wp_verify_nonce( $_POST['edd_message_nonce_field'], 'edd_message_action' ) )
					{
						$table_name		= $wpdb->prefix . 'edd_dashboard_message';
						$meta_data		= edd_get_payment_meta_cart_details( $_POST['purchase_id'], true );
						$vendor_check	= get_post_field( 'post_author', $meta_data[0]['id'] );
						$vendor			= intval( $_POST['edd_vendor'] );
						$user_id		= get_current_user_id();
						$purchase_id	= intval( $_POST['purchase_id'] );
						$message		= wp_kses_post( $_POST['message'] );
						$attach			= '';
						
						if( $vendor == $vendor_check )
						{
							if ( $_FILES ) { 
							include_once ABSPATH . 'wp-admin/includes/media.php';
							include_once ABSPATH . 'wp-admin/includes/file.php';
							include_once ABSPATH . 'wp-admin/includes/image.php';
								foreach ( $_FILES['attach']['name'] as $f => $name ) {     
									if ( $_FILES['attach']['error'][ $f ] == 0 ) {
										$file['name']		=	$name;
										$file['type']		=	$_FILES['attach']['type'][ $f ];
										$file['tmp_name']	=	$_FILES['attach']['tmp_name'][ $f ];
										$file['error']		=	$_FILES['attach']['error'][ $f ];
										$file['size']		=	$_FILES['attach']['size'][ $f ];
										$upload 			=	wp_handle_upload( $file, array( 'test_form' => false ) );
										if( !isset( $upload['error'] ) && isset( $upload['file'] ) ) { 
											$filetype   = wp_check_filetype( basename( $upload['file'] ), null );
											$title      = $file['name'];
											$ext        = strrchr( $title, '.');
											$title      = ( $ext !== false ) ? substr( $title, 0, -strlen( $ext ) ) : $title;
											$attachment = array(
												'guid'           	=> $upload['url'], 
												'post_mime_type'    => $filetype['type'],
												'post_title'        => addslashes( $title ),
												'post_content'      => '',
												'post_status'       => 'inherit'
											);
								
											$attach[]  = wp_insert_attachment( $attachment, $upload['file'] );
										}
									}
								}
							}
							//insert the single message with attachment*/
							$wpdb->insert( 
								$table_name, 
								array( 
									'author_id'		=> $vendor, 
									'user_id'		=> $user_id,
									'purchase_id'	=> $purchase_id,
									'message'		=> $message,
									'msg_time'		=> date( "Y-m-d H:i:s" ),
									'attachment'	=> serialize( $attach ),
								), 
								array( 
									'%d',
									'%d', 
									'%d', 
									'%s', 
									'%s', 
									'%s', 
								) 
							);
							add_action( 'show_insert_message', 'edd_insert_true_msg' );
						}
						else
						{
							add_action( 'show_insert_message', 'edd_insert_false_msg' );
						}
					}
					
				}
				/* End of condition one*/
				/* Second condition start to add close message and rating*/
				if ( 'POST' == $_SERVER['REQUEST_METHOD'] && !empty( $_POST['add_close'] ) && isset( $_POST['add_close'] ) )
				{
					if( wp_verify_nonce( $_POST['edd_close_thread_nonce_field'], 'edd_close_thread' ) )
					{
						$table_name		= $wpdb->prefix . 'edd_dashboard_message';
						$meta_data		= edd_get_payment_meta_cart_details( $_POST['purchase_id'], true );
						$vendor_check	= get_post_field( 'post_author', $meta_data[0]['id'] );
						$vendor			= intval( $_POST['edd_vendor'] );
						$user_id		= get_current_user_id();
						$purchase_id	= intval( $_POST['purchase_id'] );
						$rating			= floatval( $_POST['rating'] );
						$message		= wp_kses_post( $_POST['rate_message'] );
						$wpdb->insert( 
								$table_name, 
								array( 
									'author_id'		=> $vendor, 
									'user_id'		=> $user_id,
									'purchase_id'	=> $purchase_id,
									'message'		=> $message,
									'msg_time'		=> date( "Y-m-d H:i:s" ),
									'attachment'	=> serialize( $attach ),
								), 
								array( 
									'%d',
									'%d', 
									'%d', 
									'%s', 
									'%s', 
									'%s',
								) 
							);
						$rating_arr = get_user_meta( $vendor, 'edd_user_order_rating', true );
						$rating_arr[ $purchase_id ] = $rating;
						update_user_meta( $vendor, 'edd_user_order_rating', $rating_arr );
						update_post_meta( $purchase_id, 'edd_user_order_thread', 'close' );
					}
					
				}
			/*End of second condition*/
		}
	}
	
	/*Function to get all the conversation related to a single service id*/	
	if( !function_exists( 'get_conversation_by_pay_id' ) )
	{
		function get_conversation_by_pay_id( $id )
		{
			global $wpdb, $edd_receipt_args;
			$cart		= edd_get_payment_meta_cart_details( $id, true );
			$message	= get_post_meta( $cart[0]['id'], '_fes_edd_initial_message', true );
			$table_name	= $wpdb->prefix . 'edd_dashboard_message';
			$msg		= $wpdb->get_results( "SELECT * FROM $table_name WHERE purchase_id = $id ORDER BY msg_time DESC" );
			$html		='<div class="edd-messages-list">';
			/* Display initial message*/
			if( $message && $message != "" && empty($msg) )
			{
				$post		= get_post( $cart[0]['id'] );
				$user_data	= get_userdata( $post->post_author );
				$html		.= '<div class="each-edd-message">';
				$html		.= '<div class="each-edd-message-avatar">';
				$html		.= get_avatar( $post->post_author, '50').'<br><label class="name">' . $user_data->data->user_login.'</label>';
				$html		.= '</div>';
				$html		.= '<div class="each-edd-message-right-cont">';
				$html		.= '<div class="each-edd-message-msg">';
				$html		.= $message;
				$html		.= '</div>';
				$html		.= '</div>';
				$html		.= '<div style="clear:both;"></div>';
				$html		.= '</div>';
			}
			if( $msg )
			{
				foreach( $msg as $each )
				{
					if( $each->attachment!="" )
					{
						$filehtml	= "";
						$attach		= unserialize( $each->attachment );
						if( !empty( $attach ) )
						{
							foreach( $attach as $file )
							{
								$filehtml .= '<a href="'.wp_get_attachment_url($file).'" target="_blank">'.get_the_title($file).'</a>';
							}
						}
					}
					$user_data	= get_userdata( $each->user_id );
					$html		.= '<div class="each-edd-message">';
					$html		.= '<div class="each-edd-message-avatar">';
					$html		.= get_avatar( $each->user_id, '50').'<br><label class="name">'.$user_data->data->user_login.'</label>';
					$html		.= '</div>';
					$html		.= '<div class="each-edd-message-right-cont">';
					$html		.= '<div class="each-edd-message-msg">';
					$html		.= $each->message;
					$html		.= '</div>';
					$html		.= '<div class="each-edd-message-attach"><div class="attach-head">' . __( 'Attachments', WBCOM_EDD_DASH_MSG_TEXT_DOMIAN ) . '</div>';
					$html		.= $filehtml;
					$html		.= '</div>';
					$html		.= '</div>';
					$html		.= '<div style="clear:both;"></div>';
					$html		.= '</div>';
				}	
				if( $message && $message != "" )
				{
					$post		= get_post( $cart[0]['id'] );
					$user_data	= get_userdata( $post->post_author );
					$html		.= '<div class="each-edd-message">';
					$html		.= '<div class="each-edd-message-avatar">';
					$html		.= get_avatar( $post->post_author, '50').'<br><label class="name">' . $user_data->data->user_login.'</label>';
					$html		.= '</div>';
					$html		.= '<div class="each-edd-message-right-cont">';
					$html		.= '<div class="each-edd-message-msg">';
					$html		.= $message;
					$html		.= '</div>';
					$html		.= '</div>';
					$html		.= '<div style="clear:both;"></div>';
					$html		.= '</div>';
				}
				$meta_data		= edd_get_payment_meta_cart_details( $id, true );
				$vendor			= get_post_field( 'post_author', $meta_data[0]['id']);
				$user_id		= get_current_user_id();
				$thread_status 	= get_post_meta( $id, 'edd_user_order_thread', true );
				if( ($vendor != $user_id ) && ( $thread_status != 'close' ) )
				{
					$html .= '<div class="edd-close-thread-cont"> <a href="javascript:void();" class="edd-close-thread">Close Thread</a></div>';	
					$html .= '<div style="clear:both;"></div>';
					$html .= '<div id="dialog-form">
								<p class="validateTips">' . __( 'All form fields are required.', WBCOM_EDD_DASH_MSG_TEXT_DOMIAN ) . '</p>
								  <form method="post" action="">
									<fieldset>
									  <label for="name">' . __( 'Message', WBCOM_EDD_DASH_MSG_TEXT_DOMIAN ) . '</label>';
									  ob_start();
									  $settings = array( 'media_buttons' => false, 'editor_height' => '150px', 'teeny' => true, 'quicktags' => false);
									  add_filter( 'teeny_mce_buttons', 'edd_message_editor_buttons', 10, 2 );
									  wp_editor( '', 'rate_message', $settings);
									  $html .= ob_get_clean();
									  $html .= '<label for="rating">' . __( 'Rating', WBCOM_EDD_DASH_MSG_TEXT_DOMIAN ) . '</label>
												  <div id="jRate" style="height:30px;"></div>
												  <input type="hidden" value="1" name="rating" id="rating">'. wp_nonce_field( 'edd_close_thread', 'edd_close_thread_nonce_field', false, false ) . '<input type="hidden" value="'.$vendor.'" name="edd_vendor"><input type="hidden" value="'.$id.'" name="purchase_id"><input type="submit" class="edd-close-thread" name="add_close" value="Close">
									</fieldset>
								  </form>
								</div>';
				}
			}
			
					$html .= '</div>';
			return $html;
		}
	}
	
	add_action( 'init', 'edd_add_user_message' );
	?>
