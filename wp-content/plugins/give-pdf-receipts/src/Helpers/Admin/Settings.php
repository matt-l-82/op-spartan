<?php
namespace GivePdfReceipt\Helpers\Admin;

use Give_Admin_Settings;

class Settings{
	/**
	 * Save template setting.
	 * Note: only for internal use.
	 *
	 * This function handles saving template settings for donation form and global settings.
	 * If form id is not empty then it will save setting to donation form and otherwise save setting to global settings.
	 *
	 * @param int $formId
	 */
	public static function SaveCustomTemplate( $formId ){
		// Get request values
		$templateId      = isset( $_POST['give_pdf_receipt_template'] ) ? $_POST['give_pdf_receipt_template'] : '';
		$templateName    = isset( $_POST['give_pdf_receipt_template_name'] ) ? give_clean( $_POST['give_pdf_receipt_template_name'] ) : '';
		$templateContent = isset( $_POST['give_pdf_builder'] ) ? wp_kses_post( $_POST['give_pdf_builder'] ) : '';

		// Sanity check: Template ID can't be empty.
		if ( empty( $templateId ) ) {
			return;
		}

		$existingTemplate = get_post( $templateId );

		$post = array(
			'post_title'     => $templateName,
			'post_content'   => $templateContent,
			'post_type'      => 'Give_PDF_Template',
			'ping_status'    => 'closed',
			'comment_status' => 'closed',
			'post_status'    => 'publish',
		);

		// Add or update template.
		if ( 'create_new' === $templateId || empty( $existingTemplate ) ) {
			$templateId = wp_insert_post( $post );

		} else {
			// Disable modify default templates.
			switch ( $existingTemplate->post_status ) {
				case 'draft':
					// Create new template when title modified
					if ( $existingTemplate->post_title !== $templateName ) {
						$templateId = wp_insert_post( $post );
					}
					break;
				case 'publish':
                    // Don't updfate the global template content if this is per form template
                    if ( $formId ) {
                        unset( $post['post_content'] );
                    }
                    $post['ID'] = $templateId;
                    wp_update_post( $post );
                    break;
			}
		}

		// Set error.
		if( is_wp_error( $templateId ) ) {
			$errorCode = $templateId->get_error_code();
			Give_Admin_Settings::add_error( $errorCode, $templateId->get_error_message( $errorCode ) );

			return;
		}

		// Store template id in global parameter to render setting correctly.
		$GLOBALS['give_pdf_receipt_template_id'] = $templateId;

		// Save template setting form form.
		if( $formId ) {
			Give()->form_meta->update_meta( $formId, 'give_pdf_receipt_template', $templateId );
			return;
		}

		// Ensure the selected template is set in global settings.
		give_update_option( 'give_pdf_receipt_template', $templateId );
	}
}
