module.exports = function( grunt ) {
	'use strict';

	const sass = require( 'node-sass' );

	grunt.initConfig(
		{

			pkg: grunt.file.readJSON( 'package.json' ),

			jshint: {
				options: {
					jshintrc: '.jshintrc'
				},
				all: [
					'Gruntfile.js',
					'assets/js/*.js',
					'assets/js/frontend/*.js',
					'assets/js/admin/*.js',
					'assets/js/plugins/*.js',
					'!assets/js/*.min.js'
				]
			},

			concat: {
				options: {
					stripBanners: true
				},

				backend: {
					src: [
						'assets/js/plugins/jquery-maskedinput.js',
						'assets/js/plugins/transition.js',
						'assets/js/plugins/jquery-blockUI.js',
						'assets/js/plugins/collapse.js',
						'assets/js/plugins/give-ffm-upload.js',
						'assets/js/plugins/jquery-ui-timepicker-addon.js',
						'assets/js/plugins/give-ffm-date-field.js',
						'assets/js/admin/give-ffm-transaction.js',
						'assets/js/admin/give-formbuilder.js',
						'!assets/js/admin/give-ffm-admin.js',
						'!assets/js/*.min.js',
						'!assets/js/**/*.min.js'
					],
					dest: 'assets/js/admin/give-ffm-admin.js'
				},

				frontend: {
					src: [
						'assets/js/plugins/jquery-maskedinput.js',
						'assets/js/plugins/jquery-ui-timepicker-addon.js',
						'assets/js/plugins/jquery-ui-sliderAccess.js',
						'assets/js/plugins/give-ffm-date-field.js',
						'assets/js/frontend/give-ffm.js',
						'assets/js/plugins/give-ffm-upload.js',
						'!assets/js/frontend/give-ffm-frontend.js',
						'!assets/js/*.min.js',
						'!assets/js/**/*.min.js'
					],
					dest: 'assets/js/frontend/give-ffm-frontend.js'
				}
			},
			uglify: {
				options: {
					preserveComments: 'some'
				},
				backend: {
					src: 'assets/js/admin/give-ffm-admin.js',
					dest: 'assets/js/admin/give-ffm-admin.min.js'
				},
				frontend: {
					src: 'assets/js/frontend/give-ffm-frontend.js',
					dest: 'assets/js/frontend/give-ffm-frontend.min.js'
				}
			},
			sass: {
				options: {
					implementation: sass
				},
				dist: {
					options: {
						sourcemap: 'none'
					},
					files: [ {
						'assets/css/give-ffm-backend.css': 'assets/sass/ffm-backend.scss',
						'assets/css/give-ffm-frontend.css': 'assets/sass/ffm-frontend.scss'
					} ]
				}
			},

			cssmin: {
				target: {
					files: [ {
						expand: true,
						cwd: 'assets/css',
						src: [
							'*.css',
							'!*.min.css'
						],
						dest: 'assets/css',
						ext: '.min.css'
					} ]
				}
			},

			watch: {
				sass: {
					files: [
						'assets/sass/*.scss'
					],
					tasks: [ 'css' ]
				},
				js: {
					files: [
						'assets/js/**/*.js',
						'!assets/js/frontend/give-ffm-frontend.js',
						'!assets/js/admin/give-ffm-admin.js',
						'!assets/js/**/*.min.js'
					],
					tasks: [ 'concat', 'uglify' ]
				},
				livereload: {

					// Browser live reloading
					// https://github.com/gruntjs/grunt-contrib-watch#live-reloading
					options: {
						livereload: true
					},
					files: [
						'assets/sass/**/*.scss',
						'assets/js/**/*.js',
						'!assets/js/**/*.min.js'
					]
				}
			}, // end watch
			clean: {
				dist: [
					'assets/css/*.css',
					'!assets/css/give-ffm-datepicker.css',
					'!assets/css/give-ffm-datepicker.min.css',
					'assets/js/*.min.js',
					'assets/js/**/*.min.js',
					'assets/js/admin/give-ffm-admin.js',
					'assets/js/admin/give-ffm-admin.min.js',
					'assets/js/frontend/give-ffm-frontend.js',
					'assets/js/frontend/give-ffm-frontend.min.js'
				]
			},

			checktextdomain: {
				options: {
					text_domain: 'give-form-field-manager',
					keywords: [
						'__:1,2d',
						'_e:1,2d',
						'_x:1,2c,3d',
						'esc_html__:1,2d',
						'esc_html_e:1,2d',
						'esc_html_x:1,2c,3d',
						'esc_attr__:1,2d',
						'esc_attr_e:1,2d',
						'esc_attr_x:1,2c,3d',
						'_ex:1,2c,3d',
						'_n:1,2,4d',
						'_nx:1,2,4c,5d',
						'_n_noop:1,2,3d',
						'_nx_noop:1,2,3c,4d'
					]
				},
				files: {
					src: [
						'**/*.php', // Include all files
						'!node_modules/**' // Exclude node_modules/
					],
					expand: true
				}
			},

			makepot: {
				target: {
					options: {
						domainPath: '/languages/',    // Where to save the POT file.
						exclude: [ 'includes/libraries/.*', '.js' ],
						mainFile: 'give-ffm.php',    // Main project file.
						potFilename: 'give-ffm.pot',    // Name of the POT file.
						potHeaders: {
							poedit: true,                 // Includes common Poedit headers.
							'x-poedit-keywordslist': true // Include a list of all possible gettext functions.
						},
						type: 'wp-plugin',    // Type of project (wp-plugin or wp-theme).
						updateTimestamp: true,    // Whether the POT-Creation-Date should be updated without other changes.
						processPot: function( pot, options ) {
							pot.headers['report-msgid-bugs-to'] = 'https://givewp.com/';
							pot.headers['last-translator']      = 'WP-Translations (http://wp-translations.org/)';
							pot.headers['language-team']        = 'WP-Translations <wpt@wp-translations.org>';
							pot.headers.language             = 'en_US';
							var translation; // Exclude meta data from pot.
							var excluded_meta                       = [
								'Plugin Name of the plugin/theme',
								'Plugin URI of the plugin/theme',
								'Author of the plugin/theme',
								'Author URI of the plugin/theme'
							];
							for ( translation in pot.translations['']) {
								if ( 'undefined' !== typeof pot.translations[''][translation].comments.extracted ) {
									if ( 0 <= excluded_meta.indexOf( pot.translations[''][translation].comments.extracted ) ) {
										console.log( 'Excluded meta: ' + pot.translations[''][translation].comments.extracted );
										delete pot.translations[''][translation];
									}
								}
							}
							return pot;
						}
					}
				}
			} // end makepot

		}
	);

	// Load NPM tasks to be used here
	grunt.loadNpmTasks( 'grunt-contrib-clean' );
	grunt.loadNpmTasks( 'grunt-contrib-jshint' );
	grunt.loadNpmTasks( 'grunt-contrib-concat' );
	grunt.loadNpmTasks( 'grunt-contrib-uglify-es' );
	grunt.loadNpmTasks( 'grunt-sass' );
	grunt.loadNpmTasks( 'grunt-contrib-cssmin' );
	grunt.loadNpmTasks( 'grunt-contrib-watch' );
	grunt.loadNpmTasks( 'grunt-wp-i18n' );
	grunt.loadNpmTasks( 'grunt-checktextdomain' );
	grunt.loadNpmTasks( 'grunt-contrib-copy' );

	// Register tasks
	grunt.registerTask(
		'default', [
			'clean',
			'css',
			'concat',
			'uglify',
			'i18n'
		]
	);

	grunt.registerTask(
		'dev', [
			'watch'
		]
	);

	grunt.registerTask(
		'css', [
			'sass',
			'cssmin'
		]
	);
	grunt.registerTask(
		'i18n', [
			'makepot'
		]
	);
};
