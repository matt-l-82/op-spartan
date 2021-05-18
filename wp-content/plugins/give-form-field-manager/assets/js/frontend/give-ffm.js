;
var give_ffm_frontend;
(function( $ ) {
	/**
	 * Form validation or not.
	 *
	 * @since 1.2
	 *
	 * @type {boolean}
	 */
	var give_form_validated = false;

	var Give_FFM_Form = {

		/**
		 * Initialize;
		 */
		init: function() {

			var $body = $( 'body' );

			// clone and remove repeated field.
			$body.on( 'click', '.give-form span.ffm-clone-field', this.cloneField );
			$body.on( 'click', '.give-form span.ffm-remove-field', this.removeField );

			// Validate form on submit button clicking.
			$body.on( 'click.FFMevent touchend.FFMevent', 'input[type="submit"].give-submit', this.validateOnSubmit );

			// Polyfill for IE
			// See: https://github.com/impress-org/give-form-field-manager/issues/233
			if (!String.prototype.startsWith) {
				String.prototype.startsWith = function(searchString, position) {
					position = position || 0;
					return this.indexOf(searchString, position) === position;
				};
			}

			this.revealFields();
			this.applyMasks();
			this.attachStateEvents();
			this.restoreState();

			$( 'form.give-form' ).ajaxSuccess( this.resetForm );

			giveFFMDateField.setDatePicker();

			$( document ).ajaxComplete( function( event, xhr, settings ) {
				switch( Give_FFM_Form.get_parameter( 'action', settings.data ) ) {
					case 'give_load_gateway':
						Give_FFM_Form.restoreState();
						break;
				}
			} );

			$( document ).on( 'mfpOpen', function( e ) {
				Give_FFM_Form.resetForm.bind( $('.mfp-content form') )();
			} )

			// Clear storage on donation confirmation
			if ( window.location.href.indexOf( 'donation-confirmation' ) !== -1 ) {
				Give_FFM_Form.storage.clear();
			}
		},

		/**
		 * List of FFM fields to update
		 */
		fieldsList: {},

		/**
		 * Store the form state
		 */
		setFormState: function( e ) {
			const $form = e.target.parentElement.closest('form');
			const formFieldsAttr = $form.querySelector('#give-ffm-section');

			// Bailout if there are no FFM fields
			if ( ! formFieldsAttr || ! formFieldsAttr.dataset['fields'].length ) {
				return;
			}

			const formId = $form.getAttribute('id');
			const previousState = Give_FFM_Form.storage.get('state');
			const serializedData = $( $form ).serializeArray();
			const formFields = formFieldsAttr.dataset['fields'].split('|');

			// Filter form fields, allow only FFM fields
			const formState = serializedData.filter( function( item ) {
				for( const field of formFields ) {
					if ( item.name.indexOf( field ) !== -1 ) {
						return true;
					}
				}
				return false;
			} );

			// Form current state
			const newState = {
				...previousState,
				[ formId ] : formState
			};
			// Store form state
			Give_FFM_Form.storage.set('state', newState);
		},

		/**
		 * Store repeater fields state
		 */
		setRepeaterFieldsState: function( e ) {

			e.target.setAttribute( 'value', e.target.value );

			const form = e.target.parentElement.closest('form');

			if ( ! form ) {
				return;
			}

			const formId = form.getAttribute('id');
			const repeaterTables = form.querySelectorAll('.give-repeater-table');

			repeaterTables.forEach(function(table, i){
				// Store repeater fields table
				Give_FFM_Form.storage.set(`repeater-${ formId }-${ i }`, table.innerHTML );
			});
		},

		/**
		 * Restore stored from state
		 * Used after payment gateway is loaded via ajax
		 */
		restoreState: function() {
			const state = Give_FFM_Form.storage.get('state');
			// Bailout
			if ( ! state ) {
				return;
			}

			for ( const [ formId, formState ] of Object.entries( state ) ) {
				let data = {};
				// Set values
				for (const property of Object.values(formState)) {
					data = {
						...data,
						[property.name]: [ property.value, ...(data[property.name] || [] ) ]
					};
				}

				// Apply state
				for (const [name, value] of Object.entries(data)) {
					// Get all elements from state
					document.querySelectorAll(`[name="${name}"]`)
						.forEach( function( element ){
							switch( element.type  ) {
								case 'checkbox':
								case 'radio':
									element.checked = value.includes( element.value );
									break;
								case 'select-multiple':
									Object.values(element.options).forEach(function( option ){
										option.selected = option.value && value.includes( option.value );
									});
									break;
								default:
									element.value = value[0];
							}
						} );
				}

				// Handle repeater fields
				const repeaterFieldsTables = document.querySelectorAll(`form#${ formId } .give-repeater-table`);

				repeaterFieldsTables.forEach( function( repeaterFieldsTable, i ) {
					const repeaterFieldsTableData = Give_FFM_Form.storage.get(`repeater-${ formId }-${ i }`);

					if ( repeaterFieldsTableData ) {
						repeaterFieldsTable.innerHTML = repeaterFieldsTableData;
					}
				} );
			}
		},

		/**
		 * Attach event on payment method change in order to store the state
		 */
		attachStateEvents: function(){
			document.querySelectorAll('.give-form')
				.forEach(function( form ){
					const formId = form.getAttribute('id');
					// payment method change
					form.addEventListener( 'change', Give_FFM_Form.setFormState, false );
					// repeater fields update
					document.addEventListener('keyup', function(e) {
						if ( e.target.matches(`form#${ formId } .give-repeater-table input`) ) {
							Give_FFM_Form.setRepeaterFieldsState( e );
						}
					}, false);
				});
		},
		/**
		 * Helper methods for working with storage
		 */
		storage: {
			set: function( key, value ) {
				sessionStorage.setItem(`ffm-${ key }`, JSON.stringify( value ) );
			},
			get: function( key ) {
				const value = sessionStorage.getItem( `ffm-${ key }` );

				if ( value ) {
					return JSON.parse( value );
				}
			},
			clear: function() {
				for ( const key of Object.keys( sessionStorage ) ) {
					if ( key.indexOf('ffm-') !== -1 ) {
						sessionStorage.removeItem( key );
					}
				}
			}
		},

		/**
		 * Validate fields on form submit
		 *
		 * @since 1.2.1
		 *
		 * @param e
		 * @returns {boolean}
		 */
		validateOnSubmit: function( e ) {

			var $form   = $( this ).parents( 'form.give-form' );
			var form_id = $( 'input[name="give-form-id"]' ).val();

			// Don't conflict with non-donation form .give-submits
			// Such as the email access form.
			if ( 0 === $form.length ) {
				return true;
			}

			var give_form_validated = Give_FFM_Form.validateForm( $form );

			// Prevents gateways like Stripe Popup from opening.
			if ( ! give_form_validated ) {
				e.stopImmediatePropagation();
				e.preventDefault();
			} else {
				return true;
			}
		},

		/**
		 * Resets fields.
		 */
		resetForm: function() {

			// Reinitialize TinyMCE rich editor.
			$( 'textarea.rich-editor', this ).each( function() {
				var editor_id = $( this ).attr( 'name' );
				tinyMCE.execCommand( 'mceFocus', false, editor_id );
				tinyMCE.execCommand( 'mceRemoveEditor', false, editor_id );
				tinyMCE.execCommand( 'mceAddEditor', false, editor_id );
			} );

			// Reapply input masking.
			Give_FFM_Form.applyMasks();

		},

		/**
		 * Reveal fields.
		 *
		 * When you create form fields and want them hidden until the user makes the donation decision
		 * and clicks "Donate Now" via the Reveal Upon Click option.
		 *
		 * @see: https://github.com/impress-org/give-form-field-manager/issues/59
		 */
		revealFields: function() {

			// Hide fieldset so it's revealed
			$( '.give-display-reveal' ).each( function() {

				var reveal_btn = $( this ).find( '.give-btn-reveal' ),
					fieldset = reveal_btn.nextAll( '#give-ffm-section' );

				fieldset.hide();

				// Attach click handler to the button and this element too.
				reveal_btn.on( 'click', function() {
					fieldset.slideDown();
				} );

			} );

		},

		/**
		 * Mask inputs to enforce formatting.
		 */
		applyMasks: function() {
			// mask phone fields with domestic formatting
			$( 'form.give-form .js-phone-domestic' ).mask( '(999) 999-9999' );
		},

		/**
		 * Clone a field.
		 *
		 * @param e
		 */
		cloneField: function( e ) {
			e.preventDefault();

			var $div = $( this ).closest( 'tr' ),
				items = $div.siblings().addBack().length,
				$clone = $div.clone(),
				maximum_repeat = $( this ).closest( '.give-repeater-table' ).data( 'max-repeat' );

			// Don't display the (+) icon if adding the last row.
			if ( (maximum_repeat - 1) === items ) {
				var $clone_field_btn = $clone.find( '.ffm-clone-field' ).get( 0 );

				$( $clone_field_btn ).css( {
					'opacity': '0.4',
					'color': 'rgba(51, 51, 51, 0.5)'
				} );
				$( $clone_field_btn ).attr( 'data-tooltip', give_ffm_frontend.i18n.repeater.max_rows );
				$( $clone_field_btn ).attr( 'aria-label', give_ffm_frontend.i18n.repeater.max_rows );

			}

			// Add the cloned field.
			if ( maximum_repeat === 0 || items < maximum_repeat ) {
				// clear the inputs
				$clone.find( 'input' ).removeAttr('value').val( '' );
				$clone.find( ':checked' ).attr( 'checked', '' );
				$div.after( $clone );
				// Ensure floating labels works.
				if ( $( this ).closest( '.float-labels-enabled' ) ) {
					give_fl_trigger();
				}
			}

		},

		/**
		 * Remove a field.
		 */
		removeField: function() {

			// check if it's the only item.
			var $parent = $( this ).closest( 'tr' ),
				$table = $parent.closest( 'table' ),
				id = $table.attr( 'id' ),
				items = $parent.siblings().addBack().length;

			if ( items > 1 ) {
				$parent.remove();
			}

			$(document).trigger( 'giveFFMCacheField', [ id ] );
		},

		/**
		 * Validate form.
		 *
		 * @param self
		 * @returns {*}
		 */
		validateForm: function( self ) {

			var temp,
				error = false,
				required = self.find( '[data-required="yes"]' );

			// Remove all initial errors if any.
			Give_FFM_Form.removeErrors( self );
			Give_FFM_Form.removeErrorNotice( self );

			// Loop through required fields.
			required.each( function( i, item ) {

				var data_type = $( item ).data( 'type' ),
					val = '',
					length = 0,
					field;

				switch ( data_type ) {
					case 'rich':
						var name = $( item ).data( 'id' );
						val = $.trim( tinyMCE.get( name ).getContent() );

						if ( val === '' ) {
							error = true;

							// make it warn color
							Give_FFM_Form.markError( item );
						}
						break;

					case 'textarea':
					case 'text':
					case 'tel':
						val = $.trim( $( item ).val() );

						if ( val === '' ) {
							error = true;

							// make it warn color
							Give_FFM_Form.markError( item );
						}
						break;

					case 'select':
						val = $( item ).val();

						if ( ! val || val === '-1' ) {
							error = true;

							// make it warn color
							Give_FFM_Form.markError( item );
						}
						break;

					case 'multiselect':
						val = $( item ).val();

						if ( val === null || val.length === 0 ) {
							error = true;

							// make it warn color
							Give_FFM_Form.markError( item );
						}
						break;

					case 'checkbox':
						field = $( item ).parent().find( 'input[type="checkbox"]:checked' );

						if ( ! field ) {
							error = true;

							// make it warn color
							Give_FFM_Form.markError( item );
						}
						break;

					case 'radio':

						field = $( item ).parent().find( 'input[type="radio"]:checked' );

						if ( ! field ) {
							error = true;

							// make it warn color
							Give_FFM_Form.markError( item );
						}

						break;

					case 'file':
						length = $( item ).next( 'ul' ).children().length;

						if ( ! length ) {
							error = true;

							// make it warn color
							Give_FFM_Form.markError( item );
						}
						break;

					case 'email':
						val = $( item ).val();

						if( '' === val ){
							error = true;
							Give_FFM_Form.markError( item );
						} else if ( '' !== val ) {
							// run the validation
							if ( ! Give_FFM_Form.isValidEmail( val ) ) {
								error = true;
								Give_FFM_Form.markError( item );
							}
						}
						break;

					case 'url':
						val = $( item ).val();

						if ( val !== '' ) {
							// run the validation
							if ( ! Give_FFM_Form.isValidURL( val ) ) {
								error = true;

								Give_FFM_Form.markError( item );
							}
						}
						break;

				}// End switch().

			} );

			// If an error is found, bail out.
			if ( error ) {
				// Add error notice.
				Give_FFM_Form.addErrorNotice( self );
				return false;
			}

			var form_data = self.serialize(),
				rich_texts = [];

			// grab rich texts from TinyMCE.
			$( '.ffm-rich-validation' ).each( function( index, item ) {
				temp = $( item ).data( 'id' );
				var val = $.trim( tinyMCE.get( temp ).getContent() );

				rich_texts.push( temp + '=' + encodeURIComponent( val ) );
			} );

			// Append them to the form var.
			form_data = form_data + '&' + rich_texts.join( '&' );
			return form_data;
		},

		/**
		 * Add Error Notice.
		 *
		 * @param form
		 */
		addErrorNotice: function( form ) {
			var $submit_btn = $( form ).find( '.give-submit' ),
				$total_wrap = $( form ).find( '[id^=give-final-total-wrap]' );

			$submit_btn.attr( 'disabled', false ).val( $(form).find('#give-purchase-button').data( 'before-validation-label' ) ).blur();
			$total_wrap.before( '<div class="ffm-error give_errors"><p class="give_error">' + give_ffm_frontend.error_message + '</p></div>' );
			$( form ).find( '.give-loading-animation' ).fadeOut();
		},

		/**
		 * Remove Error Notice.
		 *
		 * @param form
		 */
		removeErrorNotice: function( form ) {
			$( form ).find( '.ffm-error.give_errors' ).remove();
		},

		/**
		 * Mark Error.
		 *
		 * @param item
		 */
		markError: function( item ) {
			$( item ).closest( '.form-row' ).addClass( 'give-has-error' );
			$( item ).focus();

		},

		/**
		 * Remove Error Notice.
		 *
		 * @param item
		 */
		removeErrors: function( item ) {
			$( item ).find( '.give-has-error' ).removeClass( 'give-has-error' );
		},

		/**
		 * Is Valid Email.
		 *
		 * @param email
		 * @returns {boolean}
		 */
		isValidEmail: function( email ) {
			var pattern = new RegExp( /^((([a-z]|\d|[!#\$%&'\*\+\-\/=\?\^_`{\|}~]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+(\.([a-z]|\d|[!#\$%&'\*\+\-\/=\?\^_`{\|}~]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+)*)|((\x22)((((\x20|\x09)*(\x0d\x0a))?(\x20|\x09)+)?(([\x01-\x08\x0b\x0c\x0e-\x1f\x7f]|\x21|[\x23-\x5b]|[\x5d-\x7e]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(\\([\x01-\x09\x0b\x0c\x0d-\x7f]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]))))*(((\x20|\x09)*(\x0d\x0a))?(\x20|\x09)+)?(\x22)))@((([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.)+(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.?$/i );
			return pattern.test( email );
		},

		/**
		 * Is Valid URL.
		 *
		 * @param url
		 * @returns {boolean}
		 */
		isValidURL: function( url ) {
			var urlregex = new RegExp( '^(http:\/\/www.|https:\/\/www.|ftp:\/\/www.|www.|http:\/\/|https:\/\/){1}([0-9A-Za-z]+\.)' );
			return urlregex.test( url );
		},

		/**
		 * Get specific parameter value from Query string.
		 *
		 * @param {string} parameter Parameter of query string.
		 * @param {object} data Set of data.
		 *
		 * @return boolean
		 */
		get_parameter: function ( parameter, data ) {

			if ( ! parameter ) {
				return false;
			}

			if ( ! data ) {
				data = window.location.href;
			}

			parameter = parameter.replace( /[\[]/, "\\\[" ).replace( /[\]]/, "\\\]" );
			var expr = parameter + "=([^&#]*)";
			var regex = new RegExp( expr );
			var results = regex.exec( data );

			if ( null !== results ) {
				return results[1];
			} else {
				return false;
			}
		}
	};

	$( function() {
		Give_FFM_Form.init();
	} );

})( jQuery );
