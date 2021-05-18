<?php
/**
 * AJAX Functions
 *
 * Process the AJAX actions.
 *
 * @package Give - PDF Receipts
 * @since   2.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Get template data.
 *
 * This function receives the AJAX request from the TinyMCE builder and then passes either the admin created template that's saved in the CPT or it will pass a default template from the give-pdf-receipts/templates directory.
 *
 */
function get_builder_content() {

	if ( empty( $_POST['template_id'] ) || ! current_user_can( 'read' ) ) {
		wp_die( 'Improper permissions passed.' );
	}

	$template_id       = give_clean( $_POST['template_id'] );
	$template_location = give_clean( $_POST['template_location'] );
	$template_name     = give_clean( $_POST['template_name'] );

	// Check Security.
	$nonce_value = 'file' === $template_location ? sanitize_title( $template_name ) : $template_id;
	check_admin_referer( "give_can_edit_template_{$nonce_value}", 'nonce' );

	if ( 'file' === $template_location ) {
		// Get file contents and replace assets URL
		$template = str_replace( '%assets_url%', GIVE_PDF_PLUGIN_URL . 'assets', file_get_contents( $template_id ) );
	} else {
		// Pull save template from posts.
		$template = get_post( $template_id );
		if ( 'give_pdf_template' !== $template->post_type ) {
            wp_send_json_error( 'An error occurred verifying the post type' );
		}
		$template_name = $template->post_title;
		$template      = $template->post_content;
	}

	wp_send_json_success( [
        'post_title'   => $template_name,
        'post_content' => $template
    ] );
}

add_action( 'wp_ajax_get_builder_content', 'get_builder_content' );


/**
 * Delete PDF Receipt Templates.
 *
 * @since 2.1
 */
function delete_customized_pdf_template() {
	if (
		empty( $_POST['template_id'] )
		|| ! current_user_can( 'delete_posts' )
	) {
		wp_die();
	}

	$template_id = absint( $_POST['template_id'] );

	check_admin_referer( "give_can_edit_template_{$template_id}", 'nonce' );

	/* @var WP_Post $template */
	$template = get_post( $template_id );

    if ( ! $template ) {
        wp_send_json_error( 'An error occurred while trying to load the template' );
    }

    if ( 'give_pdf_template' !== $template->post_type ) {
        wp_send_json_error( 'An error occurred verifying the post type' );
	}

	$template = wp_delete_post( $template->ID, true );

    // Just deleting the template won't be enough.
    // We also have to delete all metadata related to the PDF Receipt add-on of each Form which is using this template.
    // By deleting this metadata, the form will fallback to the global PDF receipt settings.

    // Get all Donations forms by PDF receipt template ID.
    $query = new Give_Forms_Query([
        'meta_query' => [
            [
                'key'   => 'give_pdf_receipt_template',
                'value' => $template->ID,
            ]
        ]
    ]);

    if ( $forms = $query->get_forms() ) {

        global $wpdb;

        foreach( $forms as $form ) {
            // Delete the form meta related to PDF receipt add-on.
            $wpdb->query("
                DELETE FROM {$wpdb->prefix}give_formmeta 
                WHERE form_id = {$form->ID} 
                AND meta_key LIKE '%give_pdf_%'
            ");
            // Set default global PDF template ID.
            Give()->form_meta->add_meta( $form->ID, 'give_pdf_receipt_template', give_get_option( 'give_pdf_receipt_template' ) );
        }
    }

	wp_send_json_success( [
		'post_title'   => $template->post_title,
		'post_content' => $template->post_content,
	] );
}

add_action( 'wp_ajax_delete_pdf_template', 'delete_customized_pdf_template' );
