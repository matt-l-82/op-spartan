;/**
 * Form Field Builder - JS
 *
 * Handles form builder client side (JS) functionality.
 *
 * @package     Give_FFM
 * @copyright   Copyright (c) 2015, GiveWP
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

/* globals give_ffm_formbuilder */ // <- here for good measure
( function( $ ) {

	var $formEditor = $( 'ul#give-form-fields-editor' );

	var Editor = {

		init: function() {

			this.makeSortable();

			// collapse all
			$( 'button.ffm-collapse' ).on( 'click', this.collapseEditFields );

			// add field click
			$( '.give-form-fields-buttons' ).on( 'click', 'button', this.addNewField );

			// remove form field
			$formEditor.on( 'click', '.item-delete', this.removeFormField );

			// on blur event: set meta key
			$formEditor.on( 'blur', '.js-ffm-field-label', this.setMetaKey );

			$formEditor.on( 'blur', '.js-ffm-meta-key', this.updateMetaKey );
			$formEditor.on( 'blur', '.js-ffm-meta-key', this.setEmailTag );
			$( '.js-ffm-meta-key', $formEditor ).blur();

			// Place the data attribute containing the reserved meta keys.
			Editor.resetReservedMetaKeys();

			$( '#submitdiv' ).on( 'click', '#publish', this.validateOnPublish );
			$( window ).on( 'keypress', this.validateOnPublish );

			// on change event: checkbox|radio fields
			$formEditor.on(
				'change', '.give-form-fields-sub-fields input[type=text]', function() {
					$( this ).prev( 'input[type=checkbox], input[type=radio]' ).val( $( this ).val() );
				}
			);

			// on change event: checkbox field for enabling/disabling ffm fields.
			$formEditor.on( 'change', '.hide-field-label input', this.showHideFFMFields );

			// on change event: checkbox|radio fields
			$formEditor.on(
				'click', 'input[type=checkbox].multicolumn', function() {
					var $self   = $( this ),
						$parent = $self.closest( '.give-form-fields-rows' );

					if ( $self.is( ':checked' ) ) {
						$parent.next().hide().next().hide();
						$parent.siblings( '.column-names' ).show();
					} else {
						$parent.next().show().next().show();
						$parent.siblings( '.column-names' ).hide();
					}
				}
			);

			// clone and remove repeated field
			$formEditor.on( 'click', '.ffm-clone-field', this.cloneField );
			$formEditor.on( 'click', '.ffm-remove-field', this.removeField );

			$formEditor.on( 'click', '.give-icon-locked-anchor', this.unlock_meta_key );

			// Add new duplicate field.
			$formEditor.on( 'click', '.give_ffm_field_duplicate_icon', this.duplicateField );

			// show hide ffm fields on the export donations page
			$( document ).on(
				'give_export_donations_form_response', function( ev, response ) {

					/**
					 * FFM Fields
					 */
					var ffm_fields = (
						'undefined' !== typeof response.ffm_fields &&
					null !== response.ffm_fields
					) ? response.ffm_fields : '';

					if ( ffm_fields ) {

						var ffm_field_list = $( '.give-export-donations-ffm ul' );

						// Loop through FFM fields & output
						$( ffm_fields ).each(
							function( index, value ) {

								// Repeater sections.
								var repeater_sections = (
									'undefined' !== typeof value.repeaters
								) ? value.repeaters : '';

								if ( repeater_sections ) {

									ffm_field_list.closest( 'tr' ).removeClass( 'give-hidden' );

									var parent_title = '';

									// Repeater section field.
									$( repeater_sections ).each(
										function( index, value ) {
											if ( parent_title !== value.parent_title ) {
												ffm_field_list.append( '<li class="give-export-donation-checkbox-remove repeater-section-title" data-parent-meta="' + value.parent_meta + '"><label for="give-give-donations-ffm-field-' + value.parent_meta + '"><input type="checkbox" name="give_give_donations_export_parent[' + value.parent_meta + ']" id="give-give-donations-ffm-field-' + value.parent_meta + '">' + value.parent_title + '</label></li>' );
											}
											parent_title = value.parent_title;
											ffm_field_list.append( '<li class="give-export-donation-checkbox-remove repeater-section repeater-section-' + value.parent_meta + '"><label for="give-give-donations-ffm-field-' + value.subkey + '"><input type="checkbox" name="give_give_donations_export_option[' + value.subkey + ']" id="give-give-donations-ffm-field-' + value.subkey + '">' + value.label + '</label></li>' );
										}
									);
								}

								// Repeater sections.
								var single_repeaters = (
									'undefined' !== typeof value.single
								) ? value.single : '';

								if ( single_repeaters ) {

									ffm_field_list.closest( 'tr' ).removeClass( 'give-hidden' );

									// Repeater section field.
									$( single_repeaters ).each(
										function( index, value ) {
											ffm_field_list.append( '<li class="give-export-donation-checkbox-remove"><label for="give-give-donations-ffm-field-' + value.subkey + '"><input type="checkbox" name="give_give_donations_export_option[' + value.metakey + ']" id="give-give-donations-ffm-field-' + value.subkey + '">' + value.label + '</label> </li>' );
										}
									);
								}
							}
						);
					}
				}
			);
		},

		unlock_meta_key: function( e ) {

			var user_input = confirm( give_ffm_formbuilder.notify_meta_key_lock );

			if ( user_input ) {
				$( this ).closest( '.give-meta-key-wrap' ).find( 'input[type="text"]' ).removeAttr( 'readonly' );
				$( this ).closest( '.give-meta-key-wrap' ).find( 'input[type="text"]' ).removeAttr( 'disabled' );
				$( this ).remove();
			}

			e.preventDefault();
		},

		/**
		 * Make Sortable
		 */
		makeSortable: function() {
			$formEditor = $( 'ul#give-form-fields-editor' );

			if ( $formEditor ) {
				$formEditor.sortable(
					{
						placeholder: 'sortable-placeholder',
						handle: '> .ffm-legend',
						distance: 5
					}
				);
			}
		},

		/**
		 * Add New Field
		 *
		 * @param e
		 */
		addNewField: function( e ) {
			e.preventDefault();

			$( '.ffm-loading' ).fadeIn();

			var $self       = $( this ),
				$formEditor = $( 'ul#give-form-fields-editor' ),
				$metaBox    = $( '#ffm-metabox-editor' ),
				name        = $self.data( 'name' ),
				type        = $self.data( 'type' ),
				data        = {
					name: name,
					type: type,
					order: $formEditor.find( 'li' ).length + 1,
					action: 'give-form-fields_add_el'
				};

			$.post(
				ajaxurl, data, function( res ) {
					$formEditor.append( res );
					Editor.makeSortable();
					$( '.ffm-loading' ).fadeOut(); // hide loading
					$( '.ffm-no-fields' ).hide(); // hide no fields placeholder
				}
			);
		},

		/**
		 * Remove Form Field
		 *
		 * @param e
		 */
		removeFormField: function( e ) {
			e.preventDefault();

			if ( confirm( 'Are you sure you want to remove this form field?' ) ) {
				$( this ).closest( 'li' ).fadeOut(
					function() {
						$( this ).remove();
					}
				);
			}
		},

		/**
		 * Clone Field
		 *
		 * @param e
		 */
		cloneField: function( e ) {
			e.preventDefault();

			var $div   = $( this ).closest( 'div' );
			var $clone = $div.clone();

			// clear the inputs
			$clone.find( 'input' ).val( '' );
			$clone.find( ':checked' ).attr( 'checked', '' );
			$div.after( $clone );
		},

		/**
		 * Remove Field
		 */
		removeField: function() {

			// check if it's the only item
			var $parent = $( this ).closest( 'div' );
			var items   = $parent.siblings().andSelf().length;

			if ( 1 < items ) {
				$parent.remove();
			}
		},

		updateMetaKey: function() {
			var metaKey      = $( this ).val();
			var previousKey  = $( this ).attr( 'data-previouskey' );
			var reservedKeys = $formEditor.attr( 'data-reserved' );

			if ( 'undefined' === typeof reservedKeys ) {
				return;
			}

			reservedKeys = reservedKeys.split( ',' );

			if ( previousKey !== metaKey && -1 < reservedKeys.indexOf( previousKey ) ) {
				metaKey = Editor.updateMetaKeyToUnique( metaKey );
				reservedKeys[ reservedKeys.indexOf( previousKey ) ] = metaKey;
			}

			$( this ).val( metaKey );
			$formEditor.attr( 'data-reserved', reservedKeys );
			$( this ).attr( 'data-previouskey', metaKey );
		},

		/**
		 * Set Meta Key
		 */
		setMetaKey: function() {
			var $self = $( this ),
				$fieldLabel, $metaKey;

			if ( $self.hasClass( 'js-ffm-field-label' ) ) {
				$fieldLabel = $self;
				$metaKey    = $self.closest( '.give-form-fields-rows' ).next().find( '.js-ffm-meta-key' );
			} else if ( $self.hasClass( 'js-ffm-meta-key' ) ) {
				$fieldLabel = $self.closest( '.give-form-fields-rows' ).prev().find( '.js-ffm-field-label' );
				$metaKey    = $self;
			} else {
				return false;
			}

			// only set meta key if input exists and is empty
			if ( $metaKey.length && ! $metaKey.val() ) {

				var val = $fieldLabel.val();

				// Remove HTMl from string.
				var temp = document.createElement( 'div' );
				temp.innerHTML = val;
				val = temp.innerText.trim() // remove leading and trailing whitespace.
					.toLowerCase() // convert to lowercase.
					.replace( /[\s\-]/g, '_' ) // replace spaces and - with _.
					.replace( /[^a-z0-9_]/g, '' ); // remove all chars except lowercase, numeric, or _.

				if ( 195 < val.length ) {
					val = val.substring( 0, 195 );
				}

				val = Editor.updateMetaKeyToUnique( val );

				if ( $metaKey.val() !== val ) {
					$metaKey.attr( 'data-previouskey', val );
					$metaKey.val( val ).blur();
				}

			}
		},

		resetReservedMetaKeys: function() {

			var $reservedNames = [ 'address', 'comment' ];
			var $fieldsCount   = $formEditor.children( 'li' ).length;

			if ( 0 < $fieldsCount ) {
				$formEditor.children( 'li' ).each(
					function() {
						var $metaKeyValue = $( this ).find( '.js-ffm-meta-key' ).val();
						if ( '' !== $metaKeyValue ) {
							$reservedNames.push( $metaKeyValue );
						}
					}
				);
			}

			// Update reserved names to data attributes.
			$formEditor.attr( 'data-reserved', $reservedNames );
		},

		updateMetaKeyToUnique: function( $metaKey ) {
			var $suffix,
				$separator     = '_',
				$formattedKey = $metaKey,
				$reservedNames = $formEditor.attr( 'data-reserved' ),
				rest = $metaKey.substring( 0, $metaKey.lastIndexOf( $separator ) ),
				last = $metaKey.substring( $metaKey.lastIndexOf( $separator ) + 1, $metaKey.length );

			// do not run if Meta Key is blank.
			if ( '' === $metaKey ) {
				Editor.resetReservedMetaKeys();
				return '';
			}

			// Create the reserved names array from string.
			$reservedNames = $reservedNames.split( ',' );

			if ( -1 < $reservedNames.indexOf( $metaKey ) ) {
				$suffix  = ! isNaN( last ) ? parseInt( last ) : 0;

				if ( ! isNaN( last ) ) {
					$metaKey = rest ? rest : last;
				}

				$suffix++;

				$formattedKey = $metaKey + '_' + $suffix;

				while ( -1 < $reservedNames.indexOf( $formattedKey ) ) {
					$suffix++;
					$formattedKey = $metaKey + '_' + $suffix;
				}
			}

			// Ensure to assign the new formatted key to the reserved names list.
			$reservedNames.push( $formattedKey );
			$formEditor.attr( 'data-reserved', $reservedNames.join( ',' ) );

			return $formattedKey;
		},

		/**
		 * Set Meta Key
		 */
		setEmailTag: function() {
			var $parent = $( this ).closest( '.give-form-fields-holder' );

			$( '.give-form-field-email-tag-field', $parent ).val( '{meta_donation_' + $( this ).val() + '}' );
		},

		/**
		 * Collapse
		 *
		 * @param e
		 */
		collapseEditFields: function( e ) {
			e.preventDefault();

			$( 'ul#give-form-fields-editor' ).children( 'li' ).find( '.collapse' ).collapse( 'toggle' );
		},

		/**
		 * This function will validate to restrict duplicate or reserved meta keys submission.
		 *
		 * @param {object} e
		 *
		 * @since 1.4.2
		 */
		validateOnPublish: function( e ) {

			var duplicateList = [];
			var uniqueList    = [];

			if (
				(
					13 === e.keyCode ||
					'click' === e.type
				) &&
				0 < $formEditor.length
			) {

				// get all Meta Key values in array and sort alphabetically
				var reservedNames = $( '#give-form-fields-editor' ).find( '.js-ffm-meta-key' ).map(
					function() {
						return $( this ).val();
					}
				).sort().toArray();

				$.each(
					reservedNames, function( i, value ) {
						if ( -1 === $.inArray( value, uniqueList ) ) {
							uniqueList.push( value );
						} else {
							duplicateList.push( value );
						}
					}
				);

				if ( 0 < duplicateList.length ) {
					alert( give_ffm_formbuilder.general_key_error );
					e.preventDefault();
					return false;
				}
			}
		},

		/**
		 * Sets the label title for enabled/disabled fields.
		 */
		showHideFFMFields: function() {
			if ( this.checked ) {
				$( this ).closest( '.hide-field-label' ).attr( 'title', give_ffm_formbuilder.hidden_field_enable );
			} else {
				$( this ).closest( '.hide-field-label' ).attr( 'title', give_ffm_formbuilder.hidden_field_disable );
			}
		},

		/**
		 * Duplicate field.
		 */
		duplicateField: function( el ) {
			var selected_field_id = el.currentTarget.id.split( '_' )[ 3 ],
				$this             = $( this ),
				$parent_li        = $this.parents( 'li' ),
				$form_editor      = $( 'ul#give-form-fields-editor' ),
				$ffm_next_id      = $form_editor.find( 'li' ).length + 1,
				rows              = $parent_li.clone();

			// Update name attribute value for each input.
			rows.find( 'input' ).each(
				function() {
					if ( this.hasAttribute( 'name' ) ) {
						$( this ).attr( 'name', this.name.replace( /\d+/, $ffm_next_id ) );
					}

					// Create new meta key for duplicate field.
					if ( $( this ).hasClass( 'js-ffm-meta-key' ) ) {

						// Remove readonly attr.
						$( this ).removeAttr( 'readonly' );

						// Unlocked.
						$( this ).next( 'a.give-icon-locked-anchor' ).remove();

						if ( '' === $( this ).attr( 'value' ) ) {
							$( this ).attr( 'value', $ffm_next_id );
							return;
						}

						$( this ).attr( 'value', $( this ).attr( 'value' ) + '_' + $ffm_next_id );

					}

					// Update email tag.
					if ( $( this ).hasClass( 'give-form-field-email-tag-field' ) ) {
						var meta_key_value = $( this ).parents( 'li' ).find( '.js-ffm-meta-key' ).attr( 'value' );
						$( this ).attr( 'value', '{meta_donation_' + meta_key_value + '}' );
					}
				}
			);

			// Update name attribute value for each select.
			rows.find( 'select' ).each(
				function( index ) {
					if ( this.hasAttribute( 'name' ) ) {
						$( this ).attr( 'name', this.name.replace( /\d+/, $ffm_next_id ) );
					}

					// Set select option value for cloned field.
					$( this ).val( $parent_li.find( 'select' ).eq( index ).val() );

				}
			);

			// Update name attribute value for each textarea.
			rows.find( 'textarea' ).each(
				function() {
					if ( this.hasAttribute( 'name' ) ) {
						$( this ).attr( 'name', this.name.replace( /\d+/, $ffm_next_id ) );
					}
				}
			);

			// Update attribute value for each anchor tag.
			rows.find( 'a' ).each(
				function() {
					if ( this.hasAttribute( 'aria-controls' ) ) {
						$( this ).attr( 'aria-controls', $( this ).attr( 'aria-controls' ).replace( /\d+/, $ffm_next_id ) );
					}

					if ( this.hasAttribute( 'id' ) ) {
						$( this ).attr( 'id', this.id.replace( /\d+/, $ffm_next_id ) );
					}

					// Update `href` with new field number.
					if ( this.hasAttribute( 'href' ) ) {
						$( this ).attr( 'href', $( this ).attr( 'href' ).replace( /\d+/, $ffm_next_id ) );
					}

					// Update `data-field-id`.
					if ( this.hasAttribute( 'data-field-id' ) ) {
						$( this ).attr( 'data-field-id', $( this ).attr( 'data-field-id' ).replace( /\d+/, $ffm_next_id ) );
					}
				}
			);

			rows.find( 'div#form-field-item-settings-' + selected_field_id ).attr( 'id', $( 'div#form-field-item-settings-' + selected_field_id, rows ).attr( 'id' ).replace( /\d+/, $ffm_next_id ) );

			rows.appendTo( $form_editor );
		}
	};

	// on DOM ready
	$( function() {
		giveFFMDateField.setDatePicker();
		Editor.init();
	});

}( jQuery ) );


/**
 * This JS is releated to repeatation fields
 *
 * @since 1.2.1
 */
jQuery(
	function( $ ) {
		var give_ffm = {
			init: function() {
				$( 'body' ).on( 'click', 'span.ffm-clone-field', this.cloneField );
				$( 'body' ).on( 'click', 'span.ffm-remove-field', this.removeField );
			},
			cloneField: function( e ) {
				e.preventDefault();
				var $div   = $( this ).closest( 'tr' );
				var $clone = $div.clone();

				// clear the inputs
				$clone.find( 'input' ).val( '' );
				$clone.find( ':checked' ).attr( 'checked', '' );
				$div.after( $clone );
			},

			removeField: function() {

				// check if it's the only item
				var $parent = $( this ).closest( 'tr' );
				var items   = $parent.siblings().andSelf().length;

				if ( 1 < items ) {
					$parent.remove();
				}
			}
		};

		give_ffm.init();
	}
);
