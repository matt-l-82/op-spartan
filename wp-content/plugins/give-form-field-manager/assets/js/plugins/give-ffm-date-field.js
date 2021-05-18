/* globals jQuery, give_ffm_frontend, give_ffm_formbuilder */
var giveFFMDateField = {
	setDatePicker: function() {
		jQuery( 'body' ).on( 'focus', '.give-ffm-date', function() {
			var $this = jQuery( this );
			var giveFFM = jQuery( 'body' ).hasClass( 'wp-admin' ) ? give_ffm_formbuilder : give_ffm_frontend;

			if ( $this.hasClass( 'give-ffm-timepicker' ) ) {
				var giveFFMDate = new Date();
				var giveFFMHours = giveFFMDate.getHours();
				var giveFFMMinutes = giveFFMDate.getMinutes();

				$this.datetimepicker({
					dateFormat: $this.data( 'dateformat' ),
					timeFormat: $this.data( 'timeformat' ),
					hour: giveFFMHours,
					minute: giveFFMMinutes,
					currentText: giveFFM.i18n.timepicker.now,
					closeText: giveFFM.i18n.timepicker.done,
					timeOnlyTitle: giveFFM.i18n.timepicker.choose_time,
					timeText: giveFFM.i18n.timepicker.time,
					hourText: giveFFM.i18n.timepicker.hour,
					minuteText: giveFFM.i18n.timepicker.minute,
				});

				return;
			}

			$this.datepicker({
				dateFormat: $this.data( 'dateformat' )
			});
		});
	}
};
