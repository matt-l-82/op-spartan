/*!
    jQuery Masked Input Plugin
    Copyright (c) 2007 - 2015 Josh Bush (digitalbush.com)
    Licensed under the MIT license (http://digitalbush.com/projects/masked-input-plugin/#license)
    Version: 1.4.1
*/
!function(factory) {
    "function" == typeof define && define.amd ? define([ "jquery" ], factory) : factory("object" == typeof exports ? require("jquery") : jQuery);
}(function($) {
    var caretTimeoutId, ua = navigator.userAgent, iPhone = /iphone/i.test(ua), chrome = /chrome/i.test(ua), android = /android/i.test(ua);
    $.mask = {
        definitions: {
            "9": "[0-9]",
            a: "[A-Za-z]",
            "*": "[A-Za-z0-9]"
        },
        autoclear: !0,
        dataName: "rawMaskFn",
        placeholder: "_"
    }, $.fn.extend({
        caret: function(begin, end) {
            var range;
            if (0 !== this.length && !this.is(":hidden")) return "number" == typeof begin ? (end = "number" == typeof end ? end : begin, 
            this.each(function() {
                this.setSelectionRange ? this.setSelectionRange(begin, end) : this.createTextRange && (range = this.createTextRange(), 
                range.collapse(!0), range.moveEnd("character", end), range.moveStart("character", begin), 
                range.select());
            })) : (this[0].setSelectionRange ? (begin = this[0].selectionStart, end = this[0].selectionEnd) : document.selection && document.selection.createRange && (range = document.selection.createRange(), 
            begin = 0 - range.duplicate().moveStart("character", -1e5), end = begin + range.text.length), 
            {
                begin: begin,
                end: end
            });
        },
        unmask: function() {
            return this.trigger("unmask");
        },
        mask: function(mask, settings) {
            var input, defs, tests, partialPosition, firstNonMaskPos, lastRequiredNonMaskPos, len, oldVal;
            if (!mask && this.length > 0) {
                input = $(this[0]);
                var fn = input.data($.mask.dataName);
                return fn ? fn() : void 0;
            }
            return settings = $.extend({
                autoclear: $.mask.autoclear,
                placeholder: $.mask.placeholder,
                completed: null
            }, settings), defs = $.mask.definitions, tests = [], partialPosition = len = mask.length, 
            firstNonMaskPos = null, $.each(mask.split(""), function(i, c) {
                "?" == c ? (len--, partialPosition = i) : defs[c] ? (tests.push(new RegExp(defs[c])), 
                null === firstNonMaskPos && (firstNonMaskPos = tests.length - 1), partialPosition > i && (lastRequiredNonMaskPos = tests.length - 1)) : tests.push(null);
            }), this.trigger("unmask").each(function() {
                function tryFireCompleted() {
                    if (settings.completed) {
                        for (var i = firstNonMaskPos; lastRequiredNonMaskPos >= i; i++) if (tests[i] && buffer[i] === getPlaceholder(i)) return;
                        settings.completed.call(input);
                    }
                }
                function getPlaceholder(i) {
                    return settings.placeholder.charAt(i < settings.placeholder.length ? i : 0);
                }
                function seekNext(pos) {
                    for (;++pos < len && !tests[pos]; ) ;
                    return pos;
                }
                function seekPrev(pos) {
                    for (;--pos >= 0 && !tests[pos]; ) ;
                    return pos;
                }
                function shiftL(begin, end) {
                    var i, j;
                    if (!(0 > begin)) {
                        for (i = begin, j = seekNext(end); len > i; i++) if (tests[i]) {
                            if (!(len > j && tests[i].test(buffer[j]))) break;
                            buffer[i] = buffer[j], buffer[j] = getPlaceholder(j), j = seekNext(j);
                        }
                        writeBuffer(), input.caret(Math.max(firstNonMaskPos, begin));
                    }
                }
                function shiftR(pos) {
                    var i, c, j, t;
                    for (i = pos, c = getPlaceholder(pos); len > i; i++) if (tests[i]) {
                        if (j = seekNext(i), t = buffer[i], buffer[i] = c, !(len > j && tests[j].test(t))) break;
                        c = t;
                    }
                }
                function androidInputEvent() {
                    var curVal = input.val(), pos = input.caret();
                    if (oldVal && oldVal.length && oldVal.length > curVal.length) {
                        for (checkVal(!0); pos.begin > 0 && !tests[pos.begin - 1]; ) pos.begin--;
                        if (0 === pos.begin) for (;pos.begin < firstNonMaskPos && !tests[pos.begin]; ) pos.begin++;
                        input.caret(pos.begin, pos.begin);
                    } else {
                        for (checkVal(!0); pos.begin < len && !tests[pos.begin]; ) pos.begin++;
                        input.caret(pos.begin, pos.begin);
                    }
                    tryFireCompleted();
                }
                function blurEvent() {
                    checkVal(), input.val() != focusText && input.change();
                }
                function keydownEvent(e) {
                    if (!input.prop("readonly")) {
                        var pos, begin, end, k = e.which || e.keyCode;
                        oldVal = input.val(), 8 === k || 46 === k || iPhone && 127 === k ? (pos = input.caret(), 
                        begin = pos.begin, end = pos.end, end - begin === 0 && (begin = 46 !== k ? seekPrev(begin) : end = seekNext(begin - 1), 
                        end = 46 === k ? seekNext(end) : end), clearBuffer(begin, end), shiftL(begin, end - 1), 
                        e.preventDefault()) : 13 === k ? blurEvent.call(this, e) : 27 === k && (input.val(focusText), 
                        input.caret(0, checkVal()), e.preventDefault());
                    }
                }
                function keypressEvent(e) {
                    if (!input.prop("readonly")) {
                        var p, c, next, k = e.which || e.keyCode, pos = input.caret();
                        if (!(e.ctrlKey || e.altKey || e.metaKey || 32 > k) && k && 13 !== k) {
                            if (pos.end - pos.begin !== 0 && (clearBuffer(pos.begin, pos.end), shiftL(pos.begin, pos.end - 1)), 
                            p = seekNext(pos.begin - 1), len > p && (c = String.fromCharCode(k), tests[p].test(c))) {
                                if (shiftR(p), buffer[p] = c, writeBuffer(), next = seekNext(p), android) {
                                    var proxy = function() {
                                        $.proxy($.fn.caret, input, next)();
                                    };
                                    setTimeout(proxy, 0);
                                } else input.caret(next);
                                pos.begin <= lastRequiredNonMaskPos && tryFireCompleted();
                            }
                            e.preventDefault();
                        }
                    }
                }
                function clearBuffer(start, end) {
                    var i;
                    for (i = start; end > i && len > i; i++) tests[i] && (buffer[i] = getPlaceholder(i));
                }
                function writeBuffer() {
                    input.val(buffer.join(""));
                }
                function checkVal(allow) {
                    var i, c, pos, test = input.val(), lastMatch = -1;
                    for (i = 0, pos = 0; len > i; i++) if (tests[i]) {
                        for (buffer[i] = getPlaceholder(i); pos++ < test.length; ) if (c = test.charAt(pos - 1), 
                        tests[i].test(c)) {
                            buffer[i] = c, lastMatch = i;
                            break;
                        }
                        if (pos > test.length) {
                            clearBuffer(i + 1, len);
                            break;
                        }
                    } else buffer[i] === test.charAt(pos) && pos++, partialPosition > i && (lastMatch = i);
                    return allow ? writeBuffer() : partialPosition > lastMatch + 1 ? settings.autoclear || buffer.join("") === defaultBuffer ? (input.val() && input.val(""), 
                    clearBuffer(0, len)) : writeBuffer() : (writeBuffer(), input.val(input.val().substring(0, lastMatch + 1))), 
                    partialPosition ? i : firstNonMaskPos;
                }
                var input = $(this), buffer = $.map(mask.split(""), function(c, i) {
                    return "?" != c ? defs[c] ? getPlaceholder(i) : c : void 0;
                }), defaultBuffer = buffer.join(""), focusText = input.val();
                input.data($.mask.dataName, function() {
                    return $.map(buffer, function(c, i) {
                        return tests[i] && c != getPlaceholder(i) ? c : null;
                    }).join("");
                }), input.one("unmask", function() {
                    input.off(".mask").removeData($.mask.dataName);
                }).on("focus.mask", function() {
                    if (!input.prop("readonly")) {
                        clearTimeout(caretTimeoutId);
                        var pos;
                        focusText = input.val(), pos = checkVal(), caretTimeoutId = setTimeout(function() {
                            input.get(0) === document.activeElement && (writeBuffer(), pos == mask.replace("?", "").length ? input.caret(0, pos) : input.caret(pos));
                        }, 10);
                    }
                }).on("blur.mask", blurEvent).on("keydown.mask", keydownEvent).on("keypress.mask", keypressEvent).on("input.mask paste.mask", function() {
                    input.prop("readonly") || setTimeout(function() {
                        var pos = checkVal(!0);
                        input.caret(pos), tryFireCompleted();
                    }, 0);
                }), chrome && android && input.off("input.mask").on("input.mask", androidInputEvent), 
                checkVal();
            });
        }
    });
});
+function ( $ ) {
	'use strict';

	// CSS TRANSITION SUPPORT (Shoutout: http://www.modernizr.com/)
	// ============================================================

	function transitionEnd() {
		var el = document.createElement( 'bootstrap' )

		var transEndEventNames = {
			WebkitTransition: 'webkitTransitionEnd',
			MozTransition   : 'transitionend',
			OTransition     : 'oTransitionEnd otransitionend',
			transition      : 'transitionend'
		}

		for ( var name in transEndEventNames ) {
			if ( el.style[name] !== undefined ) {
				return {end: transEndEventNames[name]}
			}
		}

		return false // explicit for ie8 (  ._.)
	}

	// http://blog.alexmaccaw.com/css-transitions
	$.fn.emulateTransitionEnd = function ( duration ) {
		var called = false
		var $el = this
		$( this ).one( 'bsTransitionEnd', function () {
			called = true
		} )
		var callback = function () {
			if ( !called ) $( $el ).trigger( $.support.transition.end )
		}
		setTimeout( callback, duration )
		return this
	}

	$( function () {
		$.support.transition = transitionEnd()

		if ( !$.support.transition ) return

		$.event.special.bsTransitionEnd = {
			bindType    : $.support.transition.end,
			delegateType: $.support.transition.end,
			handle      : function ( e ) {
				if ( $( e.target ).is( this ) ) return e.handleObj.handler.apply( this, arguments )
			}
		}
	} )

}( jQuery );

/*!
 * jQuery blockUI plugin
 * Version 2.70.0-2014.11.23
 * Requires jQuery v1.7 or later
 *
 * Examples at: http://malsup.com/jquery/block/
 * Copyright (c) 2007-2013 M. Alsup
 * Dual licensed under the MIT and GPL licenses:
 * http://www.opensource.org/licenses/mit-license.php
 * http://www.gnu.org/licenses/gpl.html
 *
 * Thanks to Amir-Hossein Sobhi for some excellent contributions!
 */

;
(function () {
	/*jshint eqeqeq:false curly:false latedef:false */
	"use strict";

	function setup( $ ) {
		$.fn._fadeIn = $.fn.fadeIn;

		var noOp = $.noop || function () {
			};

		// this bit is to ensure we don't call setExpression when we shouldn't (with extra muscle to handle
		// confusing userAgent strings on Vista)
		var msie = /MSIE/.test( navigator.userAgent );
		var ie6 = /MSIE 6.0/.test( navigator.userAgent ) && !/MSIE 8.0/.test( navigator.userAgent );
		var mode = document.documentMode || 0;
		var setExpr = $.isFunction( document.createElement( 'div' ).style.setExpression );

		// global $ methods for blocking/unblocking the entire page
		$.blockUI = function ( opts ) {
			install( window, opts );
		};
		$.unblockUI = function ( opts ) {
			remove( window, opts );
		};

		// convenience method for quick growl-like notifications  (http://www.google.com/search?q=growl)
		$.growlUI = function ( title, message, timeout, onClose ) {
			var $m = $( '<div class="growlUI"></div>' );
			if ( title ) $m.append( '<h1>' + title + '</h1>' );
			if ( message ) $m.append( '<h2>' + message + '</h2>' );
			if ( timeout === undefined ) timeout = 3000;

			// Added by konapun: Set timeout to 30 seconds if this growl is moused over, like normal toast notifications
			var callBlock = function ( opts ) {
				opts = opts || {};

				$.blockUI( {
					message    : $m,
					fadeIn     : typeof opts.fadeIn !== 'undefined' ? opts.fadeIn : 700,
					fadeOut    : typeof opts.fadeOut !== 'undefined' ? opts.fadeOut : 1000,
					timeout    : typeof opts.timeout !== 'undefined' ? opts.timeout : timeout,
					centerY    : false,
					showOverlay: false,
					onUnblock  : onClose,
					css        : $.blockUI.defaults.growlCSS
				} );
			};

			callBlock();
			var nonmousedOpacity = $m.css( 'opacity' );
			$m.mouseover( function () {
				callBlock( {
					fadeIn : 0,
					timeout: 30000
				} );

				var displayBlock = $( '.blockMsg' );
				displayBlock.stop(); // cancel fadeout if it has started
				displayBlock.fadeTo( 300, 1 ); // make it easier to read the message by removing transparency
			} ).mouseout( function () {
				$( '.blockMsg' ).fadeOut( 1000 );
			} );
			// End konapun additions
		};

		// plugin method for blocking element content
		$.fn.block = function ( opts ) {
			if ( this[0] === window ) {
				$.blockUI( opts );
				return this;
			}
			var fullOpts = $.extend( {}, $.blockUI.defaults, opts || {} );
			this.each( function () {
				var $el = $( this );
				if ( fullOpts.ignoreIfBlocked && $el.data( 'blockUI.isBlocked' ) )
					return;
				$el.unblock( {fadeOut: 0} );
			} );

			return this.each( function () {
				if ( $.css( this, 'position' ) == 'static' ) {
					this.style.position = 'relative';
					$( this ).data( 'blockUI.static', true );
				}
				this.style.zoom = 1; // force 'hasLayout' in ie
				install( this, opts );
			} );
		};

		// plugin method for unblocking element content
		$.fn.unblock = function ( opts ) {
			if ( this[0] === window ) {
				$.unblockUI( opts );
				return this;
			}
			return this.each( function () {
				remove( this, opts );
			} );
		};

		$.blockUI.version = 2.70; // 2nd generation blocking at no extra cost!

		// override these in your code to change the default behavior and style
		$.blockUI.defaults = {
			// message displayed when blocking (use null for no message)
			message: '<h1>Please wait...</h1>',

			title    : null,		// title string; only used when theme == true
			draggable: true,	// only used when theme == true (requires jquery-ui.js to be loaded)

			theme: false, // set to true to use with jQuery UI themes

			// styles for the message when blocking; if you wish to disable
			// these and use an external stylesheet then do this in your code:
			// $.blockUI.defaults.css = {};
			css: {
				padding        : 0,
				margin         : 0,
				width          : '30%',
				top            : '40%',
				left           : '35%',
				textAlign      : 'center',
				color          : '#000',
				border         : '3px solid #aaa',
				backgroundColor: '#fff',
				cursor         : 'wait'
			},

			// minimal style set used when themes are used
			themedCSS: {
				width: '30%',
				top  : '40%',
				left : '35%'
			},

			// styles for the overlay
			overlayCSS: {
				backgroundColor: '#000',
				opacity        : 0.6,
				cursor         : 'wait'
			},

			// style to replace wait cursor before unblocking to correct issue
			// of lingering wait cursor
			cursorReset: 'default',

			// styles applied when using $.growlUI
			growlCSS: {
				width                  : '350px',
				top                    : '10px',
				left                   : '',
				right                  : '10px',
				border                 : 'none',
				padding                : '5px',
				opacity                : 0.6,
				cursor                 : 'default',
				color                  : '#fff',
				backgroundColor        : '#000',
				'-webkit-border-radius': '10px',
				'-moz-border-radius'   : '10px',
				'border-radius'        : '10px'
			},

			// IE issues: 'about:blank' fails on HTTPS and javascript:false is s-l-o-w
			// (hat tip to Jorge H. N. de Vasconcelos)
			/*jshint scripturl:true */
			iframeSrc: /^https/i.test( window.location.href || '' ) ? 'javascript:false' : 'about:blank',

			// force usage of iframe in non-IE browsers (handy for blocking applets)
			forceIframe: false,

			// z-index for the blocking overlay
			baseZ: 1000,

			// set these to true to have the message automatically centered
			centerX: true, // <-- only effects element blocking (page block controlled via css above)
			centerY: true,

			// allow body element to be stetched in ie6; this makes blocking look better
			// on "short" pages.  disable if you wish to prevent changes to the body height
			allowBodyStretch: true,

			// enable if you want key and mouse events to be disabled for content that is blocked
			bindEvents: true,

			// be default blockUI will supress tab navigation from leaving blocking content
			// (if bindEvents is true)
			constrainTabKey: true,

			// fadeIn time in millis; set to 0 to disable fadeIn on block
			fadeIn: 200,

			// fadeOut time in millis; set to 0 to disable fadeOut on unblock
			fadeOut: 400,

			// time in millis to wait before auto-unblocking; set to 0 to disable auto-unblock
			timeout: 0,

			// disable if you don't want to show the overlay
			showOverlay: true,

			// if true, focus will be placed in the first available input field when
			// page blocking
			focusInput: true,

			// elements that can receive focus
			focusableElements: ':input:enabled:visible',

			// suppresses the use of overlay styles on FF/Linux (due to performance issues with opacity)
			// no longer needed in 2012
			// applyPlatformOpacityRules: true,

			// callback method invoked when fadeIn has completed and blocking message is visible
			onBlock: null,

			// callback method invoked when unblocking has completed; the callback is
			// passed the element that has been unblocked (which is the window object for page
			// blocks) and the options that were passed to the unblock call:
			//	onUnblock(element, options)
			onUnblock: null,

			// callback method invoked when the overlay area is clicked.
			// setting this will turn the cursor to a pointer, otherwise cursor defined in overlayCss will be used.
			onOverlayClick: null,

			// don't ask; if you really must know: http://groups.google.com/group/jquery-en/browse_thread/thread/36640a8730503595/2f6a79a77a78e493#2f6a79a77a78e493
			quirksmodeOffsetHack: 4,

			// class name of the message block
			blockMsgClass: 'blockMsg',

			// if it is already blocked, then ignore it (don't unblock and reblock)
			ignoreIfBlocked: false
		};

		// private data and functions follow...

		var pageBlock = null;
		var pageBlockEls = [];

		function install( el, opts ) {
			var css, themedCSS;
			var full = (el == window);
			var msg = (opts && opts.message !== undefined ? opts.message : undefined);
			opts = $.extend( {}, $.blockUI.defaults, opts || {} );

			if ( opts.ignoreIfBlocked && $( el ).data( 'blockUI.isBlocked' ) )
				return;

			opts.overlayCSS = $.extend( {}, $.blockUI.defaults.overlayCSS, opts.overlayCSS || {} );
			css = $.extend( {}, $.blockUI.defaults.css, opts.css || {} );
			if ( opts.onOverlayClick )
				opts.overlayCSS.cursor = 'pointer';

			themedCSS = $.extend( {}, $.blockUI.defaults.themedCSS, opts.themedCSS || {} );
			msg = msg === undefined ? opts.message : msg;

			// remove the current block (if there is one)
			if ( full && pageBlock )
				remove( window, {fadeOut: 0} );

			// if an existing element is being used as the blocking content then we capture
			// its current place in the DOM (and current display style) so we can restore
			// it when we unblock
			if ( msg && typeof msg != 'string' && (msg.parentNode || msg.jquery) ) {
				var node = msg.jquery ? msg[0] : msg;
				var data = {};
				$( el ).data( 'blockUI.history', data );
				data.el = node;
				data.parent = node.parentNode;
				data.display = node.style.display;
				data.position = node.style.position;
				if ( data.parent )
					data.parent.removeChild( node );
			}

			$( el ).data( 'blockUI.onUnblock', opts.onUnblock );
			var z = opts.baseZ;

			// blockUI uses 3 layers for blocking, for simplicity they are all used on every platform;
			// layer1 is the iframe layer which is used to supress bleed through of underlying content
			// layer2 is the overlay layer which has opacity and a wait cursor (by default)
			// layer3 is the message content that is displayed while blocking
			var lyr1, lyr2, lyr3, s;
			if ( msie || opts.forceIframe )
				lyr1 = $( '<iframe class="blockUI" style="z-index:' + (z++) + ';display:none;border:none;margin:0;padding:0;position:absolute;width:100%;height:100%;top:0;left:0" src="' + opts.iframeSrc + '"></iframe>' );
			else
				lyr1 = $( '<div class="blockUI" style="display:none"></div>' );

			if ( opts.theme )
				lyr2 = $( '<div class="blockUI blockOverlay ui-widget-overlay" style="z-index:' + (z++) + ';display:none"></div>' );
			else
				lyr2 = $( '<div class="blockUI blockOverlay" style="z-index:' + (z++) + ';display:none;border:none;margin:0;padding:0;width:100%;height:100%;top:0;left:0"></div>' );

			if ( opts.theme && full ) {
				s = '<div class="blockUI ' + opts.blockMsgClass + ' blockPage ui-dialog ui-widget ui-corner-all" style="z-index:' + (z + 10) + ';display:none;position:fixed">';
				if ( opts.title ) {
					s += '<div class="ui-widget-header ui-dialog-titlebar ui-corner-all blockTitle">' + (opts.title || '&nbsp;') + '</div>';
				}
				s += '<div class="ui-widget-content ui-dialog-content"></div>';
				s += '</div>';
			}
			else if ( opts.theme ) {
				s = '<div class="blockUI ' + opts.blockMsgClass + ' blockElement ui-dialog ui-widget ui-corner-all" style="z-index:' + (z + 10) + ';display:none;position:absolute">';
				if ( opts.title ) {
					s += '<div class="ui-widget-header ui-dialog-titlebar ui-corner-all blockTitle">' + (opts.title || '&nbsp;') + '</div>';
				}
				s += '<div class="ui-widget-content ui-dialog-content"></div>';
				s += '</div>';
			}
			else if ( full ) {
				s = '<div class="blockUI ' + opts.blockMsgClass + ' blockPage" style="z-index:' + (z + 10) + ';display:none;position:fixed"></div>';
			}
			else {
				s = '<div class="blockUI ' + opts.blockMsgClass + ' blockElement" style="z-index:' + (z + 10) + ';display:none;position:absolute"></div>';
			}
			lyr3 = $( s );

			// if we have a message, style it
			if ( msg ) {
				if ( opts.theme ) {
					lyr3.css( themedCSS );
					lyr3.addClass( 'ui-widget-content' );
				}
				else
					lyr3.css( css );
			}

			// style the overlay
			if ( !opts.theme /*&& (!opts.applyPlatformOpacityRules)*/ )
				lyr2.css( opts.overlayCSS );
			lyr2.css( 'position', full ? 'fixed' : 'absolute' );

			// make iframe layer transparent in IE
			if ( msie || opts.forceIframe )
				lyr1.css( 'opacity', 0.0 );

			//$([lyr1[0],lyr2[0],lyr3[0]]).appendTo(full ? 'body' : el);
			var layers = [lyr1, lyr2, lyr3], $par = full ? $( 'body' ) : $( el );
			$.each( layers, function () {
				this.appendTo( $par );
			} );

			if ( opts.theme && opts.draggable && $.fn.draggable ) {
				lyr3.draggable( {
					handle: '.ui-dialog-titlebar',
					cancel: 'li'
				} );
			}

			// ie7 must use absolute positioning in quirks mode and to account for activex issues (when scrolling)
			var expr = setExpr && (!$.support.boxModel || $( 'object,embed', full ? null : el ).length > 0);
			if ( ie6 || expr ) {
				// give body 100% height
				if ( full && opts.allowBodyStretch && $.support.boxModel )
					$( 'html,body' ).css( 'height', '100%' );

				// fix ie6 issue when blocked element has a border width
				if ( (ie6 || !$.support.boxModel) && !full ) {
					var t = sz( el, 'borderTopWidth' ), l = sz( el, 'borderLeftWidth' );
					var fixT = t ? '(0 - ' + t + ')' : 0;
					var fixL = l ? '(0 - ' + l + ')' : 0;
				}

				// simulate fixed position
				$.each( layers, function ( i, o ) {
					var s = o[0].style;
					s.position = 'absolute';
					if ( i < 2 ) {
						if ( full )
							s.setExpression( 'height', 'Math.max(document.body.scrollHeight, document.body.offsetHeight) - (jQuery.support.boxModel?0:' + opts.quirksmodeOffsetHack + ') + "px"' );
						else
							s.setExpression( 'height', 'this.parentNode.offsetHeight + "px"' );
						if ( full )
							s.setExpression( 'width', 'jQuery.support.boxModel && document.documentElement.clientWidth || document.body.clientWidth + "px"' );
						else
							s.setExpression( 'width', 'this.parentNode.offsetWidth + "px"' );
						if ( fixL ) s.setExpression( 'left', fixL );
						if ( fixT ) s.setExpression( 'top', fixT );
					}
					else if ( opts.centerY ) {
						if ( full ) s.setExpression( 'top', '(document.documentElement.clientHeight || document.body.clientHeight) / 2 - (this.offsetHeight / 2) + (blah = document.documentElement.scrollTop ? document.documentElement.scrollTop : document.body.scrollTop) + "px"' );
						s.marginTop = 0;
					}
					else if ( !opts.centerY && full ) {
						var top = (opts.css && opts.css.top) ? parseInt( opts.css.top, 10 ) : 0;
						var expression = '((document.documentElement.scrollTop ? document.documentElement.scrollTop : document.body.scrollTop) + ' + top + ') + "px"';
						s.setExpression( 'top', expression );
					}
				} );
			}

			// show the message
			if ( msg ) {
				if ( opts.theme )
					lyr3.find( '.ui-widget-content' ).append( msg );
				else
					lyr3.append( msg );
				if ( msg.jquery || msg.nodeType )
					$( msg ).show();
			}

			if ( (msie || opts.forceIframe) && opts.showOverlay )
				lyr1.show(); // opacity is zero
			if ( opts.fadeIn ) {
				var cb = opts.onBlock ? opts.onBlock : noOp;
				var cb1 = (opts.showOverlay && !msg) ? cb : noOp;
				var cb2 = msg ? cb : noOp;
				if ( opts.showOverlay )
					lyr2._fadeIn( opts.fadeIn, cb1 );
				if ( msg )
					lyr3._fadeIn( opts.fadeIn, cb2 );
			}
			else {
				if ( opts.showOverlay )
					lyr2.show();
				if ( msg )
					lyr3.show();
				if ( opts.onBlock )
					opts.onBlock.bind( lyr3 )();
			}

			// bind key and mouse events
			bind( 1, el, opts );

			if ( full ) {
				pageBlock = lyr3[0];
				pageBlockEls = $( opts.focusableElements, pageBlock );
				if ( opts.focusInput )
					setTimeout( focus, 20 );
			}
			else
				center( lyr3[0], opts.centerX, opts.centerY );

			if ( opts.timeout ) {
				// auto-unblock
				var to = setTimeout( function () {
					if ( full )
						$.unblockUI( opts );
					else
						$( el ).unblock( opts );
				}, opts.timeout );
				$( el ).data( 'blockUI.timeout', to );
			}
		}

		// remove the block
		function remove( el, opts ) {
			var count;
			var full = (el == window);
			var $el = $( el );
			var data = $el.data( 'blockUI.history' );
			var to = $el.data( 'blockUI.timeout' );
			if ( to ) {
				clearTimeout( to );
				$el.removeData( 'blockUI.timeout' );
			}
			opts = $.extend( {}, $.blockUI.defaults, opts || {} );
			bind( 0, el, opts ); // unbind events

			if ( opts.onUnblock === null ) {
				opts.onUnblock = $el.data( 'blockUI.onUnblock' );
				$el.removeData( 'blockUI.onUnblock' );
			}

			var els;
			if ( full ) // crazy selector to handle odd field errors in ie6/7
				els = $( 'body' ).children().filter( '.blockUI' ).add( 'body > .blockUI' );
			else
				els = $el.find( '>.blockUI' );

			// fix cursor issue
			if ( opts.cursorReset ) {
				if ( els.length > 1 )
					els[1].style.cursor = opts.cursorReset;
				if ( els.length > 2 )
					els[2].style.cursor = opts.cursorReset;
			}

			if ( full )
				pageBlock = pageBlockEls = null;

			if ( opts.fadeOut ) {
				count = els.length;
				els.stop().fadeOut( opts.fadeOut, function () {
					if ( --count === 0 )
						reset( els, data, opts, el );
				} );
			}
			else
				reset( els, data, opts, el );
		}

		// move blocking element back into the DOM where it started
		function reset( els, data, opts, el ) {
			var $el = $( el );
			if ( $el.data( 'blockUI.isBlocked' ) )
				return;

			els.each( function ( i, o ) {
				// remove via DOM calls so we don't lose event handlers
				if ( this.parentNode )
					this.parentNode.removeChild( this );
			} );

			if ( data && data.el ) {
				data.el.style.display = data.display;
				data.el.style.position = data.position;
				data.el.style.cursor = 'default'; // #59
				if ( data.parent )
					data.parent.appendChild( data.el );
				$el.removeData( 'blockUI.history' );
			}

			if ( $el.data( 'blockUI.static' ) ) {
				$el.css( 'position', 'static' ); // #22
			}

			if ( typeof opts.onUnblock == 'function' )
				opts.onUnblock( el, opts );

			// fix issue in Safari 6 where block artifacts remain until reflow
			var body = $( document.body ), w = body.width(), cssW = body[0].style.width;
			body.width( w - 1 ).width( w );
			body[0].style.width = cssW;
		}

		// bind/unbind the handler
		function bind( b, el, opts ) {
			var full = el == window, $el = $( el );

			// don't bother unbinding if there is nothing to unbind
			if ( !b && (full && !pageBlock || !full && !$el.data( 'blockUI.isBlocked' )) )
				return;

			$el.data( 'blockUI.isBlocked', b );

			// don't bind events when overlay is not in use or if bindEvents is false
			if ( !full || !opts.bindEvents || (b && !opts.showOverlay) )
				return;

			// bind anchors and inputs for mouse and key events
			var events = 'mousedown mouseup keydown keypress keyup touchstart touchend touchmove';
			if ( b )
				$( document ).bind( events, opts, handler );
			else
				$( document ).unbind( events, handler );

			// former impl...
			//		var $e = $('a,:input');
			//		b ? $e.bind(events, opts, handler) : $e.unbind(events, handler);
		}

		// event handler to suppress keyboard/mouse events when blocking
		function handler( e ) {
			// allow tab navigation (conditionally)
			if ( e.type === 'keydown' && e.keyCode && e.keyCode == 9 ) {
				if ( pageBlock && e.data.constrainTabKey ) {
					var els = pageBlockEls;
					var fwd = !e.shiftKey && e.target === els[els.length - 1];
					var back = e.shiftKey && e.target === els[0];
					if ( fwd || back ) {
						setTimeout( function () {
							focus( back );
						}, 10 );
						return false;
					}
				}
			}
			var opts = e.data;
			var target = $( e.target );
			if ( target.hasClass( 'blockOverlay' ) && opts.onOverlayClick )
				opts.onOverlayClick( e );

			// allow events within the message content
			if ( target.parents( 'div.' + opts.blockMsgClass ).length > 0 )
				return true;

			// allow events for content that is not being blocked
			return target.parents().children().filter( 'div.blockUI' ).length === 0;
		}

		function focus( back ) {
			if ( !pageBlockEls )
				return;
			var e = pageBlockEls[back === true ? pageBlockEls.length - 1 : 0];
			if ( e )
				e.focus();
		}

		function center( el, x, y ) {
			var p = el.parentNode, s = el.style;
			var l = ((p.offsetWidth - el.offsetWidth) / 2) - sz( p, 'borderLeftWidth' );
			var t = ((p.offsetHeight - el.offsetHeight) / 2) - sz( p, 'borderTopWidth' );
			if ( x ) s.left = l > 0 ? (l + 'px') : '0';
			if ( y ) s.top = t > 0 ? (t + 'px') : '0';
		}

		function sz( el, p ) {
			return parseInt( $.css( el, p ), 10 ) || 0;
		}

	}


	/*global define:true */
	if ( typeof define === 'function' && define.amd && define.amd.jQuery ) {
		define( ['jquery'], setup );
	} else {
		setup( jQuery );
	}

})();

+function ( $ ) {
	'use strict';

	// COLLAPSE PUBLIC CLASS DEFINITION
	// ================================

	var Collapse = function ( element, options ) {
		this.$element = $( element )
		this.options = $.extend( {}, Collapse.DEFAULTS, options )
		this.$trigger = $( '[data-toggle="collapse"][href="#' + element.id + '"],' +
			'[data-toggle="collapse"][data-target="#' + element.id + '"]' )
		this.transitioning = null

		if ( this.options.parent ) {
			this.$parent = this.getParent()
		} else {
			this.addAriaAndCollapsedClass( this.$element, this.$trigger )
		}

		if ( this.options.toggle ) this.toggle()
	}

	Collapse.VERSION = '3.3.5'

	Collapse.TRANSITION_DURATION = 350

	Collapse.DEFAULTS = {
		toggle: true
	}

	Collapse.prototype.dimension = function () {
		var hasWidth = this.$element.hasClass( 'width' )
		return hasWidth ? 'width' : 'height'
	}

	Collapse.prototype.show = function () {
		if ( this.transitioning || this.$element.hasClass( 'in' ) ) return

		var activesData
		var actives = this.$parent && this.$parent.children( '.panel' ).children( '.in, .collapsing' )

		if ( actives && actives.length ) {
			activesData = actives.data( 'bs.collapse' )
			if ( activesData && activesData.transitioning ) return
		}

		var startEvent = $.Event( 'show.bs.collapse' )
		this.$element.trigger( startEvent )
		if ( startEvent.isDefaultPrevented() ) return

		if ( actives && actives.length ) {
			Plugin.call( actives, 'hide' )
			activesData || actives.data( 'bs.collapse', null )
		}

		var dimension = this.dimension()

		this.$element
			.removeClass( 'collapse' )
			.addClass( 'collapsing' )[dimension]( 0 )
			.attr( 'aria-expanded', true )

		this.$trigger
			.removeClass( 'collapsed' )
			.attr( 'aria-expanded', true )

		this.transitioning = 1

		var complete = function () {
			this.$element
				.removeClass( 'collapsing' )
				.addClass( 'collapse in' )[dimension]( '' )
			this.transitioning = 0
			this.$element
				.trigger( 'shown.bs.collapse' )
		}

		if ( !$.support.transition ) return complete.call( this )

		var scrollSize = $.camelCase( ['scroll', dimension].join( '-' ) )

		this.$element
			.one( 'bsTransitionEnd', $.proxy( complete, this ) )
			.emulateTransitionEnd( Collapse.TRANSITION_DURATION )[dimension]( this.$element[0][scrollSize] )
	}

	Collapse.prototype.hide = function () {
		if ( this.transitioning || !this.$element.hasClass( 'in' ) ) return

		var startEvent = $.Event( 'hide.bs.collapse' )
		this.$element.trigger( startEvent )
		if ( startEvent.isDefaultPrevented() ) return

		var dimension = this.dimension()

		this.$element[dimension]( this.$element[dimension]() )[0].offsetHeight

		this.$element
			.addClass( 'collapsing' )
			.removeClass( 'collapse in' )
			.attr( 'aria-expanded', false )

		this.$trigger
			.addClass( 'collapsed' )
			.attr( 'aria-expanded', false )

		this.transitioning = 1

		var complete = function () {
			this.transitioning = 0
			this.$element
				.removeClass( 'collapsing' )
				.addClass( 'collapse' )
				.trigger( 'hidden.bs.collapse' )
		}

		if ( !$.support.transition ) return complete.call( this )

		this.$element
			[dimension]( 0 )
			.one( 'bsTransitionEnd', $.proxy( complete, this ) )
			.emulateTransitionEnd( Collapse.TRANSITION_DURATION )
	}

	Collapse.prototype.toggle = function () {
		this[this.$element.hasClass( 'in' ) ? 'hide' : 'show']()
	}

	Collapse.prototype.getParent = function () {
		return $( this.options.parent )
			.find( '[data-toggle="collapse"][data-parent="' + this.options.parent + '"]' )
			.each( $.proxy( function ( i, element ) {
				var $element = $( element )
				this.addAriaAndCollapsedClass( getTargetFromTrigger( $element ), $element )
			}, this ) )
			.end()
	}

	Collapse.prototype.addAriaAndCollapsedClass = function ( $element, $trigger ) {
		var isOpen = $element.hasClass( 'in' )

		$element.attr( 'aria-expanded', isOpen )
		$trigger
			.toggleClass( 'collapsed', !isOpen )
			.attr( 'aria-expanded', isOpen )
	}

	function getTargetFromTrigger( $trigger ) {
		var href
		var target = $trigger.attr( 'data-target' )
			|| (href = $trigger.attr( 'href' )) && href.replace( /.*(?=#[^\s]+$)/, '' ) // strip for ie7

		return $( target )
	}


	// COLLAPSE PLUGIN DEFINITION
	// ==========================

	function Plugin( option ) {
		return this.each( function () {
			var $this = $( this )
			var data = $this.data( 'bs.collapse' )
			var options = $.extend( {}, Collapse.DEFAULTS, $this.data(), typeof option == 'object' && option )

			if ( !data && options.toggle && /show|hide/.test( option ) ) options.toggle = false
			if ( !data ) $this.data( 'bs.collapse', (data = new Collapse( this, options )) )
			if ( typeof option == 'string' ) data[option]()
		} )
	}

	var old = $.fn.collapse

	$.fn.collapse = Plugin
	$.fn.collapse.Constructor = Collapse


	// COLLAPSE NO CONFLICT
	// ====================

	$.fn.collapse.noConflict = function () {
		$.fn.collapse = old
		return this
	}


	// COLLAPSE DATA-API
	// =================

	$( document ).on( 'click.bs.collapse.data-api', '[data-toggle="collapse"]', function ( e ) {
		var $this = $( this )

		if ( !$this.attr( 'data-target' ) ) e.preventDefault()

		var $target = getTargetFromTrigger( $this )
		var data = $target.data( 'bs.collapse' )
		var option = data ? 'toggle' : $this.data()

		Plugin.call( $target, option )
	} )

}( jQuery );

var give_ffm_frontend;

(function ( $ ) {

	window.Give_FFM_Uploader = function ( browse_button, container, max, type, allowed_type, max_file_size ) {
		this.container = container;
		this.browse_button = browse_button;
		this.max = max || 1;
		this.count = $( '#' + container ).find( '.ffm-attachment-list > li' ).length; //count how many items are there
		//if no element found on the page, bail out
		if ( !$( '#' + browse_button ).length ) {
			return;
		}

		//instantiate the uploader
		this.uploader = new plupload.Uploader( {
			runtimes        : 'html5,html4',
			browse_button   : browse_button,
			container       : container,
			multipart       : true,
			multipart_params: {
				action: 'ffm_file_upload'
			},
			multiple_queues : false,
			multi_selection : false,
			urlstream_upload: true,
			file_data_name  : 'ffm_file',
			max_file_size   : max_file_size + 'kb',
			url             : give_ffm_frontend.plupload.url + '&type=' + type,
			flash_swf_url   : give_ffm_frontend.flash_swf_url,
			filters         : [{
				title     : 'Allowed Files',
				extensions: allowed_type
			}]
		} );

		//attach event handlers
		this.uploader.bind( 'Init', $.proxy( this, 'init' ) );
		this.uploader.bind( 'FilesAdded', $.proxy( this, 'added' ) );
		this.uploader.bind( 'QueueChanged', $.proxy( this, 'upload' ) );
		this.uploader.bind( 'UploadProgress', $.proxy( this, 'progress' ) );
		this.uploader.bind( 'Error', $.proxy( this, 'error' ) );
		this.uploader.bind( 'FileUploaded', $.proxy( this, 'uploaded' ) );

		this.uploader.init();

		$( '#' + container ).on( 'click', 'a.attachment-delete', $.proxy( this.removeAttachment, this ) );
	};

	Give_FFM_Uploader.prototype = {

		init: function ( up, params ) {
			this.showHide();
		},

		showHide: function () {

			if ( this.count >= this.max ) {

				$( '#' + this.container ).find( '.file-selector' ).hide();

				return;
			}

			$( '#' + this.container ).find( '.file-selector' ).show();
		},

		added: function ( up, files ) {
			var $container = $( '#' + this.container ).find( '.ffm-attachment-upload-filelist' );

			this.count += 1;
			this.showHide();

			$.each( files, function ( i, file ) {
				$container.append(
					'<div class="upload-item" id="' + file.id + '"><div class="progress progress-striped active"><div class="bar"></div></div><div class="filename original">' +
					file.name + ' (' + plupload.formatSize( file.size ) + ') <b></b>' +
					'</div></div>' );
			} );

			up.refresh(); // Reposition Flash/Silverlight
			up.start();
		},

		upload: function ( uploader ) {
			this.uploader.start();
		},

		progress: function ( up, file ) {
			var item = $( '#' + file.id );

			$( '.bar', item ).css( {width: file.percent + '%'} );
			$( '.percent', item ).html( file.percent + '%' );
		},

		error: function ( up, error ) {
			$( '#' + this.container ).find( '#' + error.file.id ).remove();
			alert( 'Error #' + error.code + ': ' + error.message );

			this.count -= 1;
			this.showHide();
			this.uploader.refresh();
		},

		uploaded: function ( up, file, response ) {
			 //var res = $.parseJSON(response);
			 //console.log( typeof response, typeof response.response);
			 //console.log(response, response.response);

			$( '#' + file.id + " b" ).html( "100%" );
			$( '#' + file.id ).remove();

			if ( response.response !== 'error' ) {
				var $container = $( '#' + this.container ).find( '.ffm-attachment-list' );
				$container.append( response.response );
			} else {
				alert( res.error );
				this.count -= 1;
				this.showHide();
			}
		},

		removeAttachment: function ( e ) {
			e.preventDefault();

			var self = this,
				el = $( e.currentTarget );

			if ( confirm( give_ffm_frontend.confirmMsg ) ) {
				var data = {
					'attach_id': el.data( 'attach_id' ),
					'nonce'    : give_ffm_frontend.nonce,
					'action'   : 'ffm_file_del'
				};

				jQuery.post( give_ffm_frontend.ajaxurl, data, function () {
					el.parent().parent().remove();

					self.count -= 1;
					self.showHide();
					self.uploader.refresh();
				} );
			}
		}
	};
})( jQuery );
/*! jQuery Timepicker Addon - v1.6.3 - 2016-04-20
 * http://trentrichardson.com/examples/timepicker
 * Copyright (c) 2016 Trent Richardson; Licensed MIT */
(function (factory) {
	if (typeof define === 'function' && define.amd) {
		define(['jquery', 'jquery-ui'], factory);
	} else {
		factory(jQuery);
	}
}(function ($) {

	/*
	 * Lets not redefine timepicker, Prevent "Uncaught RangeError: Maximum call stack size exceeded"
	 */
	$.ui.timepicker = $.ui.timepicker || {};
	if ($.ui.timepicker.version) {
		return;
	}

	/*
	 * Extend jQueryUI, get it started with our version number
	 */
	$.extend($.ui, {
		timepicker: {
			version: "1.6.3"
		}
	});

	/*
	 * Timepicker manager.
	 * Use the singleton instance of this class, $.timepicker, to interact with the time picker.
	 * Settings for (groups of) time pickers are maintained in an instance object,
	 * allowing multiple different settings on the same page.
	 */
	var Timepicker = function () {
		this.regional = []; // Available regional settings, indexed by language code
		this.regional[''] = { // Default regional settings
			currentText: 'Now',
			closeText: 'Done',
			amNames: ['AM', 'A'],
			pmNames: ['PM', 'P'],
			timeFormat: 'HH:mm',
			timeSuffix: '',
			timeOnlyTitle: 'Choose Time',
			timeText: 'Time',
			hourText: 'Hour',
			minuteText: 'Minute',
			secondText: 'Second',
			millisecText: 'Millisecond',
			microsecText: 'Microsecond',
			timezoneText: 'Time Zone',
			isRTL: false
		};
		this._defaults = { // Global defaults for all the datetime picker instances
			showButtonPanel: true,
			timeOnly: false,
			timeOnlyShowDate: false,
			showHour: null,
			showMinute: null,
			showSecond: null,
			showMillisec: null,
			showMicrosec: null,
			showTimezone: null,
			showTime: true,
			stepHour: 1,
			stepMinute: 1,
			stepSecond: 1,
			stepMillisec: 1,
			stepMicrosec: 1,
			hour: 0,
			minute: 0,
			second: 0,
			millisec: 0,
			microsec: 0,
			timezone: null,
			hourMin: 0,
			minuteMin: 0,
			secondMin: 0,
			millisecMin: 0,
			microsecMin: 0,
			hourMax: 23,
			minuteMax: 59,
			secondMax: 59,
			millisecMax: 999,
			microsecMax: 999,
			minDateTime: null,
			maxDateTime: null,
			maxTime: null,
			minTime: null,
			onSelect: null,
			hourGrid: 0,
			minuteGrid: 0,
			secondGrid: 0,
			millisecGrid: 0,
			microsecGrid: 0,
			alwaysSetTime: true,
			separator: ' ',
			altFieldTimeOnly: true,
			altTimeFormat: null,
			altSeparator: null,
			altTimeSuffix: null,
			altRedirectFocus: true,
			pickerTimeFormat: null,
			pickerTimeSuffix: null,
			showTimepicker: true,
			timezoneList: null,
			addSliderAccess: false,
			sliderAccessArgs: null,
			controlType: 'slider',
			oneLine: false,
			defaultValue: null,
			parse: 'strict',
			afterInject: null
		};
		$.extend(this._defaults, this.regional['']);
	};

	$.extend(Timepicker.prototype, {
		$input: null,
		$altInput: null,
		$timeObj: null,
		inst: null,
		hour_slider: null,
		minute_slider: null,
		second_slider: null,
		millisec_slider: null,
		microsec_slider: null,
		timezone_select: null,
		maxTime: null,
		minTime: null,
		hour: 0,
		minute: 0,
		second: 0,
		millisec: 0,
		microsec: 0,
		timezone: null,
		hourMinOriginal: null,
		minuteMinOriginal: null,
		secondMinOriginal: null,
		millisecMinOriginal: null,
		microsecMinOriginal: null,
		hourMaxOriginal: null,
		minuteMaxOriginal: null,
		secondMaxOriginal: null,
		millisecMaxOriginal: null,
		microsecMaxOriginal: null,
		ampm: '',
		formattedDate: '',
		formattedTime: '',
		formattedDateTime: '',
		timezoneList: null,
		units: ['hour', 'minute', 'second', 'millisec', 'microsec'],
		support: {},
		control: null,

		/*
		 * Override the default settings for all instances of the time picker.
		 * @param  {Object} settings  object - the new settings to use as defaults (anonymous object)
		 * @return {Object} the manager object
		 */
		setDefaults: function (settings) {
			extendRemove(this._defaults, settings || {});
			return this;
		},

		/*
		 * Create a new Timepicker instance
		 */
		_newInst: function ($input, opts) {
			var tp_inst = new Timepicker(),
				inlineSettings = {},
				fns = {},
				overrides, i;

			for (var attrName in this._defaults) {
				if (this._defaults.hasOwnProperty(attrName)) {
					var attrValue = $input.attr('time:' + attrName);
					if (attrValue) {
						try {
							inlineSettings[attrName] = eval(attrValue);
						} catch (err) {
							inlineSettings[attrName] = attrValue;
						}
					}
				}
			}

			overrides = {
				beforeShow: function (input, dp_inst) {
					if ($.isFunction(tp_inst._defaults.evnts.beforeShow)) {
						return tp_inst._defaults.evnts.beforeShow.call($input[0], input, dp_inst, tp_inst);
					}
				},
				onChangeMonthYear: function (year, month, dp_inst) {
					// Update the time as well : this prevents the time from disappearing from the $input field.
					// tp_inst._updateDateTime(dp_inst);
					if ($.isFunction(tp_inst._defaults.evnts.onChangeMonthYear)) {
						tp_inst._defaults.evnts.onChangeMonthYear.call($input[0], year, month, dp_inst, tp_inst);
					}
				},
				onClose: function (dateText, dp_inst) {
					if (tp_inst.timeDefined === true && $input.val() !== '') {
						tp_inst._updateDateTime(dp_inst);
					}
					if ($.isFunction(tp_inst._defaults.evnts.onClose)) {
						tp_inst._defaults.evnts.onClose.call($input[0], dateText, dp_inst, tp_inst);
					}
				}
			};
			for (i in overrides) {
				if (overrides.hasOwnProperty(i)) {
					fns[i] = opts[i] || this._defaults[i] || null;
				}
			}

			tp_inst._defaults = $.extend({}, this._defaults, inlineSettings, opts, overrides, {
				evnts: fns,
				timepicker: tp_inst // add timepicker as a property of datepicker: $.datepicker._get(dp_inst, 'timepicker');
			});
			tp_inst.amNames = $.map(tp_inst._defaults.amNames, function (val) {
				return val.toUpperCase();
			});
			tp_inst.pmNames = $.map(tp_inst._defaults.pmNames, function (val) {
				return val.toUpperCase();
			});

			// detect which units are supported
			tp_inst.support = detectSupport(
				tp_inst._defaults.timeFormat +
				(tp_inst._defaults.pickerTimeFormat ? tp_inst._defaults.pickerTimeFormat : '') +
				(tp_inst._defaults.altTimeFormat ? tp_inst._defaults.altTimeFormat : ''));

			// controlType is string - key to our this._controls
			if (typeof(tp_inst._defaults.controlType) === 'string') {
				if (tp_inst._defaults.controlType === 'slider' && typeof($.ui.slider) === 'undefined') {
					tp_inst._defaults.controlType = 'select';
				}
				tp_inst.control = tp_inst._controls[tp_inst._defaults.controlType];
			}
			// controlType is an object and must implement create, options, value methods
			else {
				tp_inst.control = tp_inst._defaults.controlType;
			}

			// prep the timezone options
			var timezoneList = [-720, -660, -600, -570, -540, -480, -420, -360, -300, -270, -240, -210, -180, -120, -60,
				0, 60, 120, 180, 210, 240, 270, 300, 330, 345, 360, 390, 420, 480, 525, 540, 570, 600, 630, 660, 690, 720, 765, 780, 840];
			if (tp_inst._defaults.timezoneList !== null) {
				timezoneList = tp_inst._defaults.timezoneList;
			}
			var tzl = timezoneList.length, tzi = 0, tzv = null;
			if (tzl > 0 && typeof timezoneList[0] !== 'object') {
				for (; tzi < tzl; tzi++) {
					tzv = timezoneList[tzi];
					timezoneList[tzi] = { value: tzv, label: $.timepicker.timezoneOffsetString(tzv, tp_inst.support.iso8601) };
				}
			}
			tp_inst._defaults.timezoneList = timezoneList;

			// set the default units
			tp_inst.timezone = tp_inst._defaults.timezone !== null ? $.timepicker.timezoneOffsetNumber(tp_inst._defaults.timezone) :
				((new Date()).getTimezoneOffset() * -1);
			tp_inst.hour = tp_inst._defaults.hour < tp_inst._defaults.hourMin ? tp_inst._defaults.hourMin :
				tp_inst._defaults.hour > tp_inst._defaults.hourMax ? tp_inst._defaults.hourMax : tp_inst._defaults.hour;
			tp_inst.minute = tp_inst._defaults.minute < tp_inst._defaults.minuteMin ? tp_inst._defaults.minuteMin :
				tp_inst._defaults.minute > tp_inst._defaults.minuteMax ? tp_inst._defaults.minuteMax : tp_inst._defaults.minute;
			tp_inst.second = tp_inst._defaults.second < tp_inst._defaults.secondMin ? tp_inst._defaults.secondMin :
				tp_inst._defaults.second > tp_inst._defaults.secondMax ? tp_inst._defaults.secondMax : tp_inst._defaults.second;
			tp_inst.millisec = tp_inst._defaults.millisec < tp_inst._defaults.millisecMin ? tp_inst._defaults.millisecMin :
				tp_inst._defaults.millisec > tp_inst._defaults.millisecMax ? tp_inst._defaults.millisecMax : tp_inst._defaults.millisec;
			tp_inst.microsec = tp_inst._defaults.microsec < tp_inst._defaults.microsecMin ? tp_inst._defaults.microsecMin :
				tp_inst._defaults.microsec > tp_inst._defaults.microsecMax ? tp_inst._defaults.microsecMax : tp_inst._defaults.microsec;
			tp_inst.ampm = '';
			tp_inst.$input = $input;

			if (tp_inst._defaults.altField) {
				tp_inst.$altInput = $(tp_inst._defaults.altField);
				if (tp_inst._defaults.altRedirectFocus === true) {
					tp_inst.$altInput.css({
						cursor: 'pointer'
					}).focus(function () {
						$input.trigger("focus");
					});
				}
			}

			if (tp_inst._defaults.minDate === 0 || tp_inst._defaults.minDateTime === 0) {
				tp_inst._defaults.minDate = new Date();
			}
			if (tp_inst._defaults.maxDate === 0 || tp_inst._defaults.maxDateTime === 0) {
				tp_inst._defaults.maxDate = new Date();
			}

			// datepicker needs minDate/maxDate, timepicker needs minDateTime/maxDateTime..
			if (tp_inst._defaults.minDate !== undefined && tp_inst._defaults.minDate instanceof Date) {
				tp_inst._defaults.minDateTime = new Date(tp_inst._defaults.minDate.getTime());
			}
			if (tp_inst._defaults.minDateTime !== undefined && tp_inst._defaults.minDateTime instanceof Date) {
				tp_inst._defaults.minDate = new Date(tp_inst._defaults.minDateTime.getTime());
			}
			if (tp_inst._defaults.maxDate !== undefined && tp_inst._defaults.maxDate instanceof Date) {
				tp_inst._defaults.maxDateTime = new Date(tp_inst._defaults.maxDate.getTime());
			}
			if (tp_inst._defaults.maxDateTime !== undefined && tp_inst._defaults.maxDateTime instanceof Date) {
				tp_inst._defaults.maxDate = new Date(tp_inst._defaults.maxDateTime.getTime());
			}
			tp_inst.$input.bind('focus', function () {
				tp_inst._onFocus();
			});

			return tp_inst;
		},

		/*
		 * add our sliders to the calendar
		 */
		_addTimePicker: function (dp_inst) {
			var currDT = $.trim((this.$altInput && this._defaults.altFieldTimeOnly) ? this.$input.val() + ' ' + this.$altInput.val() : this.$input.val());

			this.timeDefined = this._parseTime(currDT);
			this._limitMinMaxDateTime(dp_inst, false);
			this._injectTimePicker();
			this._afterInject();
		},

		/*
		 * parse the time string from input value or _setTime
		 */
		_parseTime: function (timeString, withDate) {
			if (!this.inst) {
				this.inst = $.datepicker._getInst(this.$input[0]);
			}

			if (withDate || !this._defaults.timeOnly) {
				var dp_dateFormat = $.datepicker._get(this.inst, 'dateFormat');
				try {
					var parseRes = parseDateTimeInternal(dp_dateFormat, this._defaults.timeFormat, timeString, $.datepicker._getFormatConfig(this.inst), this._defaults);
					if (!parseRes.timeObj) {
						return false;
					}
					$.extend(this, parseRes.timeObj);
				} catch (err) {
					$.timepicker.log("Error parsing the date/time string: " + err +
						"\ndate/time string = " + timeString +
						"\ntimeFormat = " + this._defaults.timeFormat +
						"\ndateFormat = " + dp_dateFormat);
					return false;
				}
				return true;
			} else {
				var timeObj = $.datepicker.parseTime(this._defaults.timeFormat, timeString, this._defaults);
				if (!timeObj) {
					return false;
				}
				$.extend(this, timeObj);
				return true;
			}
		},

		/*
		 * Handle callback option after injecting timepicker
		 */
		_afterInject: function() {
			var o = this.inst.settings;
			if ($.isFunction(o.afterInject)) {
				o.afterInject.call(this);
			}
		},

		/*
		 * generate and inject html for timepicker into ui datepicker
		 */
		_injectTimePicker: function () {
			var $dp = this.inst.dpDiv,
				o = this.inst.settings,
				tp_inst = this,
				litem = '',
				uitem = '',
				show = null,
				max = {},
				gridSize = {},
				size = null,
				i = 0,
				l = 0;

			// Prevent displaying twice
			if ($dp.find("div.ui-timepicker-div").length === 0 && o.showTimepicker) {
				var noDisplay = ' ui_tpicker_unit_hide',
					html = '<div class="ui-timepicker-div' + (o.isRTL ? ' ui-timepicker-rtl' : '') + (o.oneLine && o.controlType === 'select' ? ' ui-timepicker-oneLine' : '') + '"><dl>' + '<dt class="ui_tpicker_time_label' + ((o.showTime) ? '' : noDisplay) + '">' + o.timeText + '</dt>' +
						'<dd class="ui_tpicker_time '+ ((o.showTime) ? '' : noDisplay) + '"><input class="ui_tpicker_time_input" ' + (o.timeInput ? '' : 'disabled') + '/></dd>';

				// Create the markup
				for (i = 0, l = this.units.length; i < l; i++) {
					litem = this.units[i];
					uitem = litem.substr(0, 1).toUpperCase() + litem.substr(1);
					show = o['show' + uitem] !== null ? o['show' + uitem] : this.support[litem];

					// Added by Peter Medeiros:
					// - Figure out what the hour/minute/second max should be based on the step values.
					// - Example: if stepMinute is 15, then minMax is 45.
					max[litem] = parseInt((o[litem + 'Max'] - ((o[litem + 'Max'] - o[litem + 'Min']) % o['step' + uitem])), 10);
					gridSize[litem] = 0;

					html += '<dt class="ui_tpicker_' + litem + '_label' + (show ? '' : noDisplay) + '">' + o[litem + 'Text'] + '</dt>' +
						'<dd class="ui_tpicker_' + litem + (show ? '' : noDisplay) + '"><div class="ui_tpicker_' + litem + '_slider' + (show ? '' : noDisplay) + '"></div>';

					if (show && o[litem + 'Grid'] > 0) {
						html += '<div style="padding-left: 1px"><table class="ui-tpicker-grid-label"><tr>';

						if (litem === 'hour') {
							for (var h = o[litem + 'Min']; h <= max[litem]; h += parseInt(o[litem + 'Grid'], 10)) {
								gridSize[litem]++;
								var tmph = $.datepicker.formatTime(this.support.ampm ? 'hht' : 'HH', {hour: h}, o);
								html += '<td data-for="' + litem + '">' + tmph + '</td>';
							}
						}
						else {
							for (var m = o[litem + 'Min']; m <= max[litem]; m += parseInt(o[litem + 'Grid'], 10)) {
								gridSize[litem]++;
								html += '<td data-for="' + litem + '">' + ((m < 10) ? '0' : '') + m + '</td>';
							}
						}

						html += '</tr></table></div>';
					}
					html += '</dd>';
				}

				// Timezone
				var showTz = o.showTimezone !== null ? o.showTimezone : this.support.timezone;
				html += '<dt class="ui_tpicker_timezone_label' + (showTz ? '' : noDisplay) + '">' + o.timezoneText + '</dt>';
				html += '<dd class="ui_tpicker_timezone' + (showTz ? '' : noDisplay) + '"></dd>';

				// Create the elements from string
				html += '</dl></div>';
				var $tp = $(html);

				// if we only want time picker...
				if (o.timeOnly === true) {
					$tp.prepend('<div class="ui-widget-header ui-helper-clearfix ui-corner-all">' + '<div class="ui-datepicker-title">' + o.timeOnlyTitle + '</div>' + '</div>');
					$dp.find('.ui-datepicker-header, .ui-datepicker-calendar').hide();
				}

				// add sliders, adjust grids, add events
				for (i = 0, l = tp_inst.units.length; i < l; i++) {
					litem = tp_inst.units[i];
					uitem = litem.substr(0, 1).toUpperCase() + litem.substr(1);
					show = o['show' + uitem] !== null ? o['show' + uitem] : this.support[litem];

					// add the slider
					tp_inst[litem + '_slider'] = tp_inst.control.create(tp_inst, $tp.find('.ui_tpicker_' + litem + '_slider'), litem, tp_inst[litem], o[litem + 'Min'], max[litem], o['step' + uitem]);

					// adjust the grid and add click event
					if (show && o[litem + 'Grid'] > 0) {
						size = 100 * gridSize[litem] * o[litem + 'Grid'] / (max[litem] - o[litem + 'Min']);
						$tp.find('.ui_tpicker_' + litem + ' table').css({
							width: size + "%",
							marginLeft: o.isRTL ? '0' : ((size / (-2 * gridSize[litem])) + "%"),
							marginRight: o.isRTL ? ((size / (-2 * gridSize[litem])) + "%") : '0',
							borderCollapse: 'collapse'
						}).find("td").click(function (e) {
							var $t = $(this),
								h = $t.html(),
								n = parseInt(h.replace(/[^0-9]/g), 10),
								ap = h.replace(/[^apm]/ig),
								f = $t.data('for'); // loses scope, so we use data-for

							if (f === 'hour') {
								if (ap.indexOf('p') !== -1 && n < 12) {
									n += 12;
								}
								else {
									if (ap.indexOf('a') !== -1 && n === 12) {
										n = 0;
									}
								}
							}

							tp_inst.control.value(tp_inst, tp_inst[f + '_slider'], litem, n);

							tp_inst._onTimeChange();
							tp_inst._onSelectHandler();
						}).css({
							cursor: 'pointer',
							width: (100 / gridSize[litem]) + '%',
							textAlign: 'center',
							overflow: 'hidden'
						});
					} // end if grid > 0
				} // end for loop

				// Add timezone options
				this.timezone_select = $tp.find('.ui_tpicker_timezone').append('<select></select>').find("select");
				$.fn.append.apply(this.timezone_select,
					$.map(o.timezoneList, function (val, idx) {
						return $("<option />").val(typeof val === "object" ? val.value : val).text(typeof val === "object" ? val.label : val);
					}));
				if (typeof(this.timezone) !== "undefined" && this.timezone !== null && this.timezone !== "") {
					var local_timezone = (new Date(this.inst.selectedYear, this.inst.selectedMonth, this.inst.selectedDay, 12)).getTimezoneOffset() * -1;
					if (local_timezone === this.timezone) {
						selectLocalTimezone(tp_inst);
					} else {
						this.timezone_select.val(this.timezone);
					}
				} else {
					if (typeof(this.hour) !== "undefined" && this.hour !== null && this.hour !== "") {
						this.timezone_select.val(o.timezone);
					} else {
						selectLocalTimezone(tp_inst);
					}
				}
				this.timezone_select.change(function () {
					tp_inst._onTimeChange();
					tp_inst._onSelectHandler();
					tp_inst._afterInject();
				});
				// End timezone options

				// inject timepicker into datepicker
				var $buttonPanel = $dp.find('.ui-datepicker-buttonpane');
				if ($buttonPanel.length) {
					$buttonPanel.before($tp);
				} else {
					$dp.append($tp);
				}

				this.$timeObj = $tp.find('.ui_tpicker_time_input');
				this.$timeObj.change(function () {
					var timeFormat = tp_inst.inst.settings.timeFormat;
					var parsedTime = $.datepicker.parseTime(timeFormat, this.value);
					var update = new Date();
					if (parsedTime) {
						update.setHours(parsedTime.hour);
						update.setMinutes(parsedTime.minute);
						update.setSeconds(parsedTime.second);
						$.datepicker._setTime(tp_inst.inst, update);
					} else {
						this.value = tp_inst.formattedTime;
						this.blur();
					}
				});

				if (this.inst !== null) {
					var timeDefined = this.timeDefined;
					this._onTimeChange();
					this.timeDefined = timeDefined;
				}

				// slideAccess integration: http://trentrichardson.com/2011/11/11/jquery-ui-sliders-and-touch-accessibility/
				if (this._defaults.addSliderAccess) {
					var sliderAccessArgs = this._defaults.sliderAccessArgs,
						rtl = this._defaults.isRTL;
					sliderAccessArgs.isRTL = rtl;

					setTimeout(function () { // fix for inline mode
						if ($tp.find('.ui-slider-access').length === 0) {
							$tp.find('.ui-slider:visible').sliderAccess(sliderAccessArgs);

							// fix any grids since sliders are shorter
							var sliderAccessWidth = $tp.find('.ui-slider-access:eq(0)').outerWidth(true);
							if (sliderAccessWidth) {
								$tp.find('table:visible').each(function () {
									var $g = $(this),
										oldWidth = $g.outerWidth(),
										oldMarginLeft = $g.css(rtl ? 'marginRight' : 'marginLeft').toString().replace('%', ''),
										newWidth = oldWidth - sliderAccessWidth,
										newMarginLeft = ((oldMarginLeft * newWidth) / oldWidth) + '%',
										css = { width: newWidth, marginRight: 0, marginLeft: 0 };
									css[rtl ? 'marginRight' : 'marginLeft'] = newMarginLeft;
									$g.css(css);
								});
							}
						}
					}, 10);
				}
				// end slideAccess integration

				tp_inst._limitMinMaxDateTime(this.inst, true);
			}
		},

		/*
		 * This function tries to limit the ability to go outside the
		 * min/max date range
		 */
		_limitMinMaxDateTime: function (dp_inst, adjustSliders) {
			var o = this._defaults,
				dp_date = new Date(dp_inst.selectedYear, dp_inst.selectedMonth, dp_inst.selectedDay);

			if (!this._defaults.showTimepicker) {
				return;
			} // No time so nothing to check here

			if ($.datepicker._get(dp_inst, 'minDateTime') !== null && $.datepicker._get(dp_inst, 'minDateTime') !== undefined && dp_date) {
				var minDateTime = $.datepicker._get(dp_inst, 'minDateTime'),
					minDateTimeDate = new Date(minDateTime.getFullYear(), minDateTime.getMonth(), minDateTime.getDate(), 0, 0, 0, 0);

				if (this.hourMinOriginal === null || this.minuteMinOriginal === null || this.secondMinOriginal === null || this.millisecMinOriginal === null || this.microsecMinOriginal === null) {
					this.hourMinOriginal = o.hourMin;
					this.minuteMinOriginal = o.minuteMin;
					this.secondMinOriginal = o.secondMin;
					this.millisecMinOriginal = o.millisecMin;
					this.microsecMinOriginal = o.microsecMin;
				}

				if (dp_inst.settings.timeOnly || minDateTimeDate.getTime() === dp_date.getTime()) {
					this._defaults.hourMin = minDateTime.getHours();
					if (this.hour <= this._defaults.hourMin) {
						this.hour = this._defaults.hourMin;
						this._defaults.minuteMin = minDateTime.getMinutes();
						if (this.minute <= this._defaults.minuteMin) {
							this.minute = this._defaults.minuteMin;
							this._defaults.secondMin = minDateTime.getSeconds();
							if (this.second <= this._defaults.secondMin) {
								this.second = this._defaults.secondMin;
								this._defaults.millisecMin = minDateTime.getMilliseconds();
								if (this.millisec <= this._defaults.millisecMin) {
									this.millisec = this._defaults.millisecMin;
									this._defaults.microsecMin = minDateTime.getMicroseconds();
								} else {
									if (this.microsec < this._defaults.microsecMin) {
										this.microsec = this._defaults.microsecMin;
									}
									this._defaults.microsecMin = this.microsecMinOriginal;
								}
							} else {
								this._defaults.millisecMin = this.millisecMinOriginal;
								this._defaults.microsecMin = this.microsecMinOriginal;
							}
						} else {
							this._defaults.secondMin = this.secondMinOriginal;
							this._defaults.millisecMin = this.millisecMinOriginal;
							this._defaults.microsecMin = this.microsecMinOriginal;
						}
					} else {
						this._defaults.minuteMin = this.minuteMinOriginal;
						this._defaults.secondMin = this.secondMinOriginal;
						this._defaults.millisecMin = this.millisecMinOriginal;
						this._defaults.microsecMin = this.microsecMinOriginal;
					}
				} else {
					this._defaults.hourMin = this.hourMinOriginal;
					this._defaults.minuteMin = this.minuteMinOriginal;
					this._defaults.secondMin = this.secondMinOriginal;
					this._defaults.millisecMin = this.millisecMinOriginal;
					this._defaults.microsecMin = this.microsecMinOriginal;
				}
			}

			if ($.datepicker._get(dp_inst, 'maxDateTime') !== null && $.datepicker._get(dp_inst, 'maxDateTime') !== undefined && dp_date) {
				var maxDateTime = $.datepicker._get(dp_inst, 'maxDateTime'),
					maxDateTimeDate = new Date(maxDateTime.getFullYear(), maxDateTime.getMonth(), maxDateTime.getDate(), 0, 0, 0, 0);

				if (this.hourMaxOriginal === null || this.minuteMaxOriginal === null || this.secondMaxOriginal === null || this.millisecMaxOriginal === null) {
					this.hourMaxOriginal = o.hourMax;
					this.minuteMaxOriginal = o.minuteMax;
					this.secondMaxOriginal = o.secondMax;
					this.millisecMaxOriginal = o.millisecMax;
					this.microsecMaxOriginal = o.microsecMax;
				}

				if (dp_inst.settings.timeOnly || maxDateTimeDate.getTime() === dp_date.getTime()) {
					this._defaults.hourMax = maxDateTime.getHours();
					if (this.hour >= this._defaults.hourMax) {
						this.hour = this._defaults.hourMax;
						this._defaults.minuteMax = maxDateTime.getMinutes();
						if (this.minute >= this._defaults.minuteMax) {
							this.minute = this._defaults.minuteMax;
							this._defaults.secondMax = maxDateTime.getSeconds();
							if (this.second >= this._defaults.secondMax) {
								this.second = this._defaults.secondMax;
								this._defaults.millisecMax = maxDateTime.getMilliseconds();
								if (this.millisec >= this._defaults.millisecMax) {
									this.millisec = this._defaults.millisecMax;
									this._defaults.microsecMax = maxDateTime.getMicroseconds();
								} else {
									if (this.microsec > this._defaults.microsecMax) {
										this.microsec = this._defaults.microsecMax;
									}
									this._defaults.microsecMax = this.microsecMaxOriginal;
								}
							} else {
								this._defaults.millisecMax = this.millisecMaxOriginal;
								this._defaults.microsecMax = this.microsecMaxOriginal;
							}
						} else {
							this._defaults.secondMax = this.secondMaxOriginal;
							this._defaults.millisecMax = this.millisecMaxOriginal;
							this._defaults.microsecMax = this.microsecMaxOriginal;
						}
					} else {
						this._defaults.minuteMax = this.minuteMaxOriginal;
						this._defaults.secondMax = this.secondMaxOriginal;
						this._defaults.millisecMax = this.millisecMaxOriginal;
						this._defaults.microsecMax = this.microsecMaxOriginal;
					}
				} else {
					this._defaults.hourMax = this.hourMaxOriginal;
					this._defaults.minuteMax = this.minuteMaxOriginal;
					this._defaults.secondMax = this.secondMaxOriginal;
					this._defaults.millisecMax = this.millisecMaxOriginal;
					this._defaults.microsecMax = this.microsecMaxOriginal;
				}
			}

			if (dp_inst.settings.minTime!==null) {
				var tempMinTime=new Date("01/01/1970 " + dp_inst.settings.minTime);
				if (this.hour<tempMinTime.getHours()) {
					this.hour=this._defaults.hourMin=tempMinTime.getHours();
					this.minute=this._defaults.minuteMin=tempMinTime.getMinutes();
				} else if (this.hour===tempMinTime.getHours() && this.minute<tempMinTime.getMinutes()) {
					this.minute=this._defaults.minuteMin=tempMinTime.getMinutes();
				} else {
					if (this._defaults.hourMin<tempMinTime.getHours()) {
						this._defaults.hourMin=tempMinTime.getHours();
						this._defaults.minuteMin=tempMinTime.getMinutes();
					} else if (this._defaults.hourMin===tempMinTime.getHours()===this.hour && this._defaults.minuteMin<tempMinTime.getMinutes()) {
						this._defaults.minuteMin=tempMinTime.getMinutes();
					} else {
						this._defaults.minuteMin=0;
					}
				}
			}

			if (dp_inst.settings.maxTime!==null) {
				var tempMaxTime=new Date("01/01/1970 " + dp_inst.settings.maxTime);
				if (this.hour>tempMaxTime.getHours()) {
					this.hour=this._defaults.hourMax=tempMaxTime.getHours();
					this.minute=this._defaults.minuteMax=tempMaxTime.getMinutes();
				} else if (this.hour===tempMaxTime.getHours() && this.minute>tempMaxTime.getMinutes()) {
					this.minute=this._defaults.minuteMax=tempMaxTime.getMinutes();
				} else {
					if (this._defaults.hourMax>tempMaxTime.getHours()) {
						this._defaults.hourMax=tempMaxTime.getHours();
						this._defaults.minuteMax=tempMaxTime.getMinutes();
					} else if (this._defaults.hourMax===tempMaxTime.getHours()===this.hour && this._defaults.minuteMax>tempMaxTime.getMinutes()) {
						this._defaults.minuteMax=tempMaxTime.getMinutes();
					} else {
						this._defaults.minuteMax=59;
					}
				}
			}

			if (adjustSliders !== undefined && adjustSliders === true) {
				var hourMax = parseInt((this._defaults.hourMax - ((this._defaults.hourMax - this._defaults.hourMin) % this._defaults.stepHour)), 10),
					minMax = parseInt((this._defaults.minuteMax - ((this._defaults.minuteMax - this._defaults.minuteMin) % this._defaults.stepMinute)), 10),
					secMax = parseInt((this._defaults.secondMax - ((this._defaults.secondMax - this._defaults.secondMin) % this._defaults.stepSecond)), 10),
					millisecMax = parseInt((this._defaults.millisecMax - ((this._defaults.millisecMax - this._defaults.millisecMin) % this._defaults.stepMillisec)), 10),
					microsecMax = parseInt((this._defaults.microsecMax - ((this._defaults.microsecMax - this._defaults.microsecMin) % this._defaults.stepMicrosec)), 10);

				if (this.hour_slider) {
					this.control.options(this, this.hour_slider, 'hour', { min: this._defaults.hourMin, max: hourMax, step: this._defaults.stepHour });
					this.control.value(this, this.hour_slider, 'hour', this.hour - (this.hour % this._defaults.stepHour));
				}
				if (this.minute_slider) {
					this.control.options(this, this.minute_slider, 'minute', { min: this._defaults.minuteMin, max: minMax, step: this._defaults.stepMinute });
					this.control.value(this, this.minute_slider, 'minute', this.minute - (this.minute % this._defaults.stepMinute));
				}
				if (this.second_slider) {
					this.control.options(this, this.second_slider, 'second', { min: this._defaults.secondMin, max: secMax, step: this._defaults.stepSecond });
					this.control.value(this, this.second_slider, 'second', this.second - (this.second % this._defaults.stepSecond));
				}
				if (this.millisec_slider) {
					this.control.options(this, this.millisec_slider, 'millisec', { min: this._defaults.millisecMin, max: millisecMax, step: this._defaults.stepMillisec });
					this.control.value(this, this.millisec_slider, 'millisec', this.millisec - (this.millisec % this._defaults.stepMillisec));
				}
				if (this.microsec_slider) {
					this.control.options(this, this.microsec_slider, 'microsec', { min: this._defaults.microsecMin, max: microsecMax, step: this._defaults.stepMicrosec });
					this.control.value(this, this.microsec_slider, 'microsec', this.microsec - (this.microsec % this._defaults.stepMicrosec));
				}
			}

		},

		/*
		 * when a slider moves, set the internal time...
		 * on time change is also called when the time is updated in the text field
		 */
		_onTimeChange: function () {
			if (!this._defaults.showTimepicker) {
				return;
			}
			var hour = (this.hour_slider) ? this.control.value(this, this.hour_slider, 'hour') : false,
				minute = (this.minute_slider) ? this.control.value(this, this.minute_slider, 'minute') : false,
				second = (this.second_slider) ? this.control.value(this, this.second_slider, 'second') : false,
				millisec = (this.millisec_slider) ? this.control.value(this, this.millisec_slider, 'millisec') : false,
				microsec = (this.microsec_slider) ? this.control.value(this, this.microsec_slider, 'microsec') : false,
				timezone = (this.timezone_select) ? this.timezone_select.val() : false,
				o = this._defaults,
				pickerTimeFormat = o.pickerTimeFormat || o.timeFormat,
				pickerTimeSuffix = o.pickerTimeSuffix || o.timeSuffix;

			if (typeof(hour) === 'object') {
				hour = false;
			}
			if (typeof(minute) === 'object') {
				minute = false;
			}
			if (typeof(second) === 'object') {
				second = false;
			}
			if (typeof(millisec) === 'object') {
				millisec = false;
			}
			if (typeof(microsec) === 'object') {
				microsec = false;
			}
			if (typeof(timezone) === 'object') {
				timezone = false;
			}

			if (hour !== false) {
				hour = parseInt(hour, 10);
			}
			if (minute !== false) {
				minute = parseInt(minute, 10);
			}
			if (second !== false) {
				second = parseInt(second, 10);
			}
			if (millisec !== false) {
				millisec = parseInt(millisec, 10);
			}
			if (microsec !== false) {
				microsec = parseInt(microsec, 10);
			}
			if (timezone !== false) {
				timezone = timezone.toString();
			}

			var ampm = o[hour < 12 ? 'amNames' : 'pmNames'][0];

			// If the update was done in the input field, the input field should not be updated.
			// If the update was done using the sliders, update the input field.
			var hasChanged = (
				hour !== parseInt(this.hour,10) || // sliders should all be numeric
				minute !== parseInt(this.minute,10) ||
				second !== parseInt(this.second,10) ||
				millisec !== parseInt(this.millisec,10) ||
				microsec !== parseInt(this.microsec,10) ||
				(this.ampm.length > 0 && (hour < 12) !== ($.inArray(this.ampm.toUpperCase(), this.amNames) !== -1)) ||
				(this.timezone !== null && timezone !== this.timezone.toString()) // could be numeric or "EST" format, so use toString()
			);

			if (hasChanged) {

				if (hour !== false) {
					this.hour = hour;
				}
				if (minute !== false) {
					this.minute = minute;
				}
				if (second !== false) {
					this.second = second;
				}
				if (millisec !== false) {
					this.millisec = millisec;
				}
				if (microsec !== false) {
					this.microsec = microsec;
				}
				if (timezone !== false) {
					this.timezone = timezone;
				}

				if (!this.inst) {
					this.inst = $.datepicker._getInst(this.$input[0]);
				}

				this._limitMinMaxDateTime(this.inst, true);
			}
			if (this.support.ampm) {
				this.ampm = ampm;
			}

			// Updates the time within the timepicker
			this.formattedTime = $.datepicker.formatTime(o.timeFormat, this, o);
			if (this.$timeObj) {
				if (pickerTimeFormat === o.timeFormat) {
					this.$timeObj.val(this.formattedTime + pickerTimeSuffix);
				}
				else {
					this.$timeObj.val($.datepicker.formatTime(pickerTimeFormat, this, o) + pickerTimeSuffix);
				}
				if (this.$timeObj[0].setSelectionRange) {
					var sPos = this.$timeObj[0].selectionStart;
					var ePos = this.$timeObj[0].selectionEnd;
					this.$timeObj[0].setSelectionRange(sPos, ePos);
				}
			}

			this.timeDefined = true;
			if (hasChanged) {
				this._updateDateTime();
				//this.$input.focus(); // may automatically open the picker on setDate
			}
		},

		/*
		 * call custom onSelect.
		 * bind to sliders slidestop, and grid click.
		 */
		_onSelectHandler: function () {
			var onSelect = this._defaults.onSelect || this.inst.settings.onSelect;
			var inputEl = this.$input ? this.$input[0] : null;
			if (onSelect && inputEl) {
				onSelect.apply(inputEl, [this.formattedDateTime, this]);
			}
		},

		/*
		 * update our input with the new date time..
		 */
		_updateDateTime: function (dp_inst) {
			dp_inst = this.inst || dp_inst;
			var dtTmp = (dp_inst.currentYear > 0?
					new Date(dp_inst.currentYear, dp_inst.currentMonth, dp_inst.currentDay) :
					new Date(dp_inst.selectedYear, dp_inst.selectedMonth, dp_inst.selectedDay)),
				dt = $.datepicker._daylightSavingAdjust(dtTmp),
				//dt = $.datepicker._daylightSavingAdjust(new Date(dp_inst.selectedYear, dp_inst.selectedMonth, dp_inst.selectedDay)),
				//dt = $.datepicker._daylightSavingAdjust(new Date(dp_inst.currentYear, dp_inst.currentMonth, dp_inst.currentDay)),
				dateFmt = $.datepicker._get(dp_inst, 'dateFormat'),
				formatCfg = $.datepicker._getFormatConfig(dp_inst),
				timeAvailable = dt !== null && this.timeDefined;
			this.formattedDate = $.datepicker.formatDate(dateFmt, (dt === null ? new Date() : dt), formatCfg);
			var formattedDateTime = this.formattedDate;

			// if a slider was changed but datepicker doesn't have a value yet, set it
			if (dp_inst.lastVal === "") {
				dp_inst.currentYear = dp_inst.selectedYear;
				dp_inst.currentMonth = dp_inst.selectedMonth;
				dp_inst.currentDay = dp_inst.selectedDay;
			}

			/*
			 * remove following lines to force every changes in date picker to change the input value
			 * Bug descriptions: when an input field has a default value, and click on the field to pop up the date picker.
			 * If the user manually empty the value in the input field, the date picker will never change selected value.
			 */
			//if (dp_inst.lastVal !== undefined && (dp_inst.lastVal.length > 0 && this.$input.val().length === 0)) {
			//	return;
			//}

			if (this._defaults.timeOnly === true && this._defaults.timeOnlyShowDate === false) {
				formattedDateTime = this.formattedTime;
			} else if ((this._defaults.timeOnly !== true && (this._defaults.alwaysSetTime || timeAvailable)) || (this._defaults.timeOnly === true && this._defaults.timeOnlyShowDate === true)) {
				formattedDateTime += this._defaults.separator + this.formattedTime + this._defaults.timeSuffix;
			}

			this.formattedDateTime = formattedDateTime;

			if (!this._defaults.showTimepicker) {
				this.$input.val(this.formattedDate);
			} else if (this.$altInput && this._defaults.timeOnly === false && this._defaults.altFieldTimeOnly === true) {
				this.$altInput.val(this.formattedTime);
				this.$input.val(this.formattedDate);
			} else if (this.$altInput) {
				this.$input.val(formattedDateTime);
				var altFormattedDateTime = '',
					altSeparator = this._defaults.altSeparator !== null ? this._defaults.altSeparator : this._defaults.separator,
					altTimeSuffix = this._defaults.altTimeSuffix !== null ? this._defaults.altTimeSuffix : this._defaults.timeSuffix;

				if (!this._defaults.timeOnly) {
					if (this._defaults.altFormat) {
						altFormattedDateTime = $.datepicker.formatDate(this._defaults.altFormat, (dt === null ? new Date() : dt), formatCfg);
					}
					else {
						altFormattedDateTime = this.formattedDate;
					}

					if (altFormattedDateTime) {
						altFormattedDateTime += altSeparator;
					}
				}

				if (this._defaults.altTimeFormat !== null) {
					altFormattedDateTime += $.datepicker.formatTime(this._defaults.altTimeFormat, this, this._defaults) + altTimeSuffix;
				}
				else {
					altFormattedDateTime += this.formattedTime + altTimeSuffix;
				}
				this.$altInput.val(altFormattedDateTime);
			} else {
				this.$input.val(formattedDateTime);
			}

			this.$input.trigger("change");
		},

		_onFocus: function () {
			if (!this.$input.val() && this._defaults.defaultValue) {
				this.$input.val(this._defaults.defaultValue);
				var inst = $.datepicker._getInst(this.$input.get(0)),
					tp_inst = $.datepicker._get(inst, 'timepicker');
				if (tp_inst) {
					if (tp_inst._defaults.timeOnly && (inst.input.val() !== inst.lastVal)) {
						try {
							$.datepicker._updateDatepicker(inst);
						} catch (err) {
							$.timepicker.log(err);
						}
					}
				}
			}
		},

		/*
		 * Small abstraction to control types
		 * We can add more, just be sure to follow the pattern: create, options, value
		 */
		_controls: {
			// slider methods
			slider: {
				create: function (tp_inst, obj, unit, val, min, max, step) {
					var rtl = tp_inst._defaults.isRTL; // if rtl go -60->0 instead of 0->60
					return obj.prop('slide', null).slider({
						orientation: "horizontal",
						value: rtl ? val * -1 : val,
						min: rtl ? max * -1 : min,
						max: rtl ? min * -1 : max,
						step: step,
						slide: function (event, ui) {
							tp_inst.control.value(tp_inst, $(this), unit, rtl ? ui.value * -1 : ui.value);
							tp_inst._onTimeChange();
						},
						stop: function (event, ui) {
							tp_inst._onSelectHandler();
						}
					});
				},
				options: function (tp_inst, obj, unit, opts, val) {
					if (tp_inst._defaults.isRTL) {
						if (typeof(opts) === 'string') {
							if (opts === 'min' || opts === 'max') {
								if (val !== undefined) {
									return obj.slider(opts, val * -1);
								}
								return Math.abs(obj.slider(opts));
							}
							return obj.slider(opts);
						}
						var min = opts.min,
							max = opts.max;
						opts.min = opts.max = null;
						if (min !== undefined) {
							opts.max = min * -1;
						}
						if (max !== undefined) {
							opts.min = max * -1;
						}
						return obj.slider(opts);
					}
					if (typeof(opts) === 'string' && val !== undefined) {
						return obj.slider(opts, val);
					}
					return obj.slider(opts);
				},
				value: function (tp_inst, obj, unit, val) {
					if (tp_inst._defaults.isRTL) {
						if (val !== undefined) {
							return obj.slider('value', val * -1);
						}
						return Math.abs(obj.slider('value'));
					}
					if (val !== undefined) {
						return obj.slider('value', val);
					}
					return obj.slider('value');
				}
			},
			// select methods
			select: {
				create: function (tp_inst, obj, unit, val, min, max, step) {
					var sel = '<select class="ui-timepicker-select ui-state-default ui-corner-all" data-unit="' + unit + '" data-min="' + min + '" data-max="' + max + '" data-step="' + step + '">',
						format = tp_inst._defaults.pickerTimeFormat || tp_inst._defaults.timeFormat;

					for (var i = min; i <= max; i += step) {
						sel += '<option value="' + i + '"' + (i === val ? ' selected' : '') + '>';
						if (unit === 'hour') {
							sel += $.datepicker.formatTime($.trim(format.replace(/[^ht ]/ig, '')), {hour: i}, tp_inst._defaults);
						}
						else if (unit === 'millisec' || unit === 'microsec' || i >= 10) { sel += i; }
						else {sel += '0' + i.toString(); }
						sel += '</option>';
					}
					sel += '</select>';

					obj.children('select').remove();

					$(sel).appendTo(obj).change(function (e) {
						tp_inst._onTimeChange();
						tp_inst._onSelectHandler();
						tp_inst._afterInject();
					});

					return obj;
				},
				options: function (tp_inst, obj, unit, opts, val) {
					var o = {},
						$t = obj.children('select');
					if (typeof(opts) === 'string') {
						if (val === undefined) {
							return $t.data(opts);
						}
						o[opts] = val;
					}
					else { o = opts; }
					return tp_inst.control.create(tp_inst, obj, $t.data('unit'), $t.val(), o.min>=0 ? o.min : $t.data('min'), o.max || $t.data('max'), o.step || $t.data('step'));
				},
				value: function (tp_inst, obj, unit, val) {
					var $t = obj.children('select');
					if (val !== undefined) {
						return $t.val(val);
					}
					return $t.val();
				}
			}
		} // end _controls

	});

	$.fn.extend({
		/*
		 * shorthand just to use timepicker.
		 */
		timepicker: function (o) {
			o = o || {};
			var tmp_args = Array.prototype.slice.call(arguments);

			if (typeof o === 'object') {
				tmp_args[0] = $.extend(o, {
					timeOnly: true
				});
			}

			return $(this).each(function () {
				$.fn.datetimepicker.apply($(this), tmp_args);
			});
		},

		/*
		 * extend timepicker to datepicker
		 */
		datetimepicker: function (o) {
			o = o || {};
			var tmp_args = arguments;

			if (typeof(o) === 'string') {
				if (o === 'getDate'  || (o === 'option' && tmp_args.length === 2 && typeof (tmp_args[1]) === 'string')) {
					return $.fn.datepicker.apply($(this[0]), tmp_args);
				} else {
					return this.each(function () {
						var $t = $(this);
						$t.datepicker.apply($t, tmp_args);
					});
				}
			} else {
				return this.each(function () {
					var $t = $(this);
					$t.datepicker($.timepicker._newInst($t, o)._defaults);
				});
			}
		}
	});

	/*
	 * Public Utility to parse date and time
	 */
	$.datepicker.parseDateTime = function (dateFormat, timeFormat, dateTimeString, dateSettings, timeSettings) {
		var parseRes = parseDateTimeInternal(dateFormat, timeFormat, dateTimeString, dateSettings, timeSettings);
		if (parseRes.timeObj) {
			var t = parseRes.timeObj;
			parseRes.date.setHours(t.hour, t.minute, t.second, t.millisec);
			parseRes.date.setMicroseconds(t.microsec);
		}

		return parseRes.date;
	};

	/*
	 * Public utility to parse time
	 */
	$.datepicker.parseTime = function (timeFormat, timeString, options) {
		var o = extendRemove(extendRemove({}, $.timepicker._defaults), options || {}),
			iso8601 = (timeFormat.replace(/\'.*?\'/g, '').indexOf('Z') !== -1);

		// Strict parse requires the timeString to match the timeFormat exactly
		var strictParse = function (f, s, o) {

			// pattern for standard and localized AM/PM markers
			var getPatternAmpm = function (amNames, pmNames) {
				var markers = [];
				if (amNames) {
					$.merge(markers, amNames);
				}
				if (pmNames) {
					$.merge(markers, pmNames);
				}
				markers = $.map(markers, function (val) {
					return val.replace(/[.*+?|()\[\]{}\\]/g, '\\$&');
				});
				return '(' + markers.join('|') + ')?';
			};

			// figure out position of time elements.. cause js cant do named captures
			var getFormatPositions = function (timeFormat) {
				var finds = timeFormat.toLowerCase().match(/(h{1,2}|m{1,2}|s{1,2}|l{1}|c{1}|t{1,2}|z|'.*?')/g),
					orders = {
						h: -1,
						m: -1,
						s: -1,
						l: -1,
						c: -1,
						t: -1,
						z: -1
					};

				if (finds) {
					for (var i = 0; i < finds.length; i++) {
						if (orders[finds[i].toString().charAt(0)] === -1) {
							orders[finds[i].toString().charAt(0)] = i + 1;
						}
					}
				}
				return orders;
			};

			var regstr = '^' + f.toString()
						.replace(/([hH]{1,2}|mm?|ss?|[tT]{1,2}|[zZ]|[lc]|'.*?')/g, function (match) {
							var ml = match.length;
							switch (match.charAt(0).toLowerCase()) {
								case 'h':
									return ml === 1 ? '(\\d?\\d)' : '(\\d{' + ml + '})';
								case 'm':
									return ml === 1 ? '(\\d?\\d)' : '(\\d{' + ml + '})';
								case 's':
									return ml === 1 ? '(\\d?\\d)' : '(\\d{' + ml + '})';
								case 'l':
									return '(\\d?\\d?\\d)';
								case 'c':
									return '(\\d?\\d?\\d)';
								case 'z':
									return '(z|[-+]\\d\\d:?\\d\\d|\\S+)?';
								case 't':
									return getPatternAmpm(o.amNames, o.pmNames);
								default:    // literal escaped in quotes
									return '(' + match.replace(/\'/g, "").replace(/(\.|\$|\^|\\|\/|\(|\)|\[|\]|\?|\+|\*)/g, function (m) { return "\\" + m; }) + ')?';
							}
						})
						.replace(/\s/g, '\\s?') +
					o.timeSuffix + '$',
				order = getFormatPositions(f),
				ampm = '',
				treg;

			treg = s.match(new RegExp(regstr, 'i'));

			var resTime = {
				hour: 0,
				minute: 0,
				second: 0,
				millisec: 0,
				microsec: 0
			};

			if (treg) {
				if (order.t !== -1) {
					if (treg[order.t] === undefined || treg[order.t].length === 0) {
						ampm = '';
						resTime.ampm = '';
					} else {
						ampm = $.inArray(treg[order.t].toUpperCase(), $.map(o.amNames, function (x,i) { return x.toUpperCase(); })) !== -1 ? 'AM' : 'PM';
						resTime.ampm = o[ampm === 'AM' ? 'amNames' : 'pmNames'][0];
					}
				}

				if (order.h !== -1) {
					if (ampm === 'AM' && treg[order.h] === '12') {
						resTime.hour = 0; // 12am = 0 hour
					} else {
						if (ampm === 'PM' && treg[order.h] !== '12') {
							resTime.hour = parseInt(treg[order.h], 10) + 12; // 12pm = 12 hour, any other pm = hour + 12
						} else {
							resTime.hour = Number(treg[order.h]);
						}
					}
				}

				if (order.m !== -1) {
					resTime.minute = Number(treg[order.m]);
				}
				if (order.s !== -1) {
					resTime.second = Number(treg[order.s]);
				}
				if (order.l !== -1) {
					resTime.millisec = Number(treg[order.l]);
				}
				if (order.c !== -1) {
					resTime.microsec = Number(treg[order.c]);
				}
				if (order.z !== -1 && treg[order.z] !== undefined) {
					resTime.timezone = $.timepicker.timezoneOffsetNumber(treg[order.z]);
				}


				return resTime;
			}
			return false;
		};// end strictParse

		// First try JS Date, if that fails, use strictParse
		var looseParse = function (f, s, o) {
			try {
				var d = new Date('2012-01-01 ' + s);
				if (isNaN(d.getTime())) {
					d = new Date('2012-01-01T' + s);
					if (isNaN(d.getTime())) {
						d = new Date('01/01/2012 ' + s);
						if (isNaN(d.getTime())) {
							throw "Unable to parse time with native Date: " + s;
						}
					}
				}

				return {
					hour: d.getHours(),
					minute: d.getMinutes(),
					second: d.getSeconds(),
					millisec: d.getMilliseconds(),
					microsec: d.getMicroseconds(),
					timezone: d.getTimezoneOffset() * -1
				};
			}
			catch (err) {
				try {
					return strictParse(f, s, o);
				}
				catch (err2) {
					$.timepicker.log("Unable to parse \ntimeString: " + s + "\ntimeFormat: " + f);
				}
			}
			return false;
		}; // end looseParse

		if (typeof o.parse === "function") {
			return o.parse(timeFormat, timeString, o);
		}
		if (o.parse === 'loose') {
			return looseParse(timeFormat, timeString, o);
		}
		return strictParse(timeFormat, timeString, o);
	};

	/**
	 * Public utility to format the time
	 * @param {string} format format of the time
	 * @param {Object} time Object not a Date for timezones
	 * @param {Object} [options] essentially the regional[].. amNames, pmNames, ampm
	 * @returns {string} the formatted time
	 */
	$.datepicker.formatTime = function (format, time, options) {
		options = options || {};
		options = $.extend({}, $.timepicker._defaults, options);
		time = $.extend({
			hour: 0,
			minute: 0,
			second: 0,
			millisec: 0,
			microsec: 0,
			timezone: null
		}, time);

		var tmptime = format,
			ampmName = options.amNames[0],
			hour = parseInt(time.hour, 10);

		if (hour > 11) {
			ampmName = options.pmNames[0];
		}

		tmptime = tmptime.replace(/(?:HH?|hh?|mm?|ss?|[tT]{1,2}|[zZ]|[lc]|'.*?')/g, function (match) {
			switch (match) {
				case 'HH':
					return ('0' + hour).slice(-2);
				case 'H':
					return hour;
				case 'hh':
					return ('0' + convert24to12(hour)).slice(-2);
				case 'h':
					return convert24to12(hour);
				case 'mm':
					return ('0' + time.minute).slice(-2);
				case 'm':
					return time.minute;
				case 'ss':
					return ('0' + time.second).slice(-2);
				case 's':
					return time.second;
				case 'l':
					return ('00' + time.millisec).slice(-3);
				case 'c':
					return ('00' + time.microsec).slice(-3);
				case 'z':
					return $.timepicker.timezoneOffsetString(time.timezone === null ? options.timezone : time.timezone, false);
				case 'Z':
					return $.timepicker.timezoneOffsetString(time.timezone === null ? options.timezone : time.timezone, true);
				case 'T':
					return ampmName.charAt(0).toUpperCase();
				case 'TT':
					return ampmName.toUpperCase();
				case 't':
					return ampmName.charAt(0).toLowerCase();
				case 'tt':
					return ampmName.toLowerCase();
				default:
					return match.replace(/'/g, "");
			}
		});

		return tmptime;
	};

	/*
	 * the bad hack :/ override datepicker so it doesn't close on select
	 // inspired: http://stackoverflow.com/questions/1252512/jquery-datepicker-prevent-closing-picker-when-clicking-a-date/1762378#1762378
	 */
	$.datepicker._base_selectDate = $.datepicker._selectDate;
	$.datepicker._selectDate = function (id, dateStr) {
		var inst = this._getInst($(id)[0]),
			tp_inst = this._get(inst, 'timepicker'),
			was_inline;

		if (tp_inst && inst.settings.showTimepicker) {
			tp_inst._limitMinMaxDateTime(inst, true);
			was_inline = inst.inline;
			inst.inline = inst.stay_open = true;
			//This way the onSelect handler called from calendarpicker get the full dateTime
			this._base_selectDate(id, dateStr);
			inst.inline = was_inline;
			inst.stay_open = false;
			this._notifyChange(inst);
			this._updateDatepicker(inst);
		} else {
			this._base_selectDate(id, dateStr);
		}
	};

	/*
	 * second bad hack :/ override datepicker so it triggers an event when changing the input field
	 * and does not redraw the datepicker on every selectDate event
	 */
	$.datepicker._base_updateDatepicker = $.datepicker._updateDatepicker;
	$.datepicker._updateDatepicker = function (inst) {

		// don't popup the datepicker if there is another instance already opened
		var input = inst.input[0];
		if ($.datepicker._curInst && $.datepicker._curInst !== inst && $.datepicker._datepickerShowing && $.datepicker._lastInput !== input) {
			return;
		}

		if (typeof(inst.stay_open) !== 'boolean' || inst.stay_open === false) {

			this._base_updateDatepicker(inst);

			// Reload the time control when changing something in the input text field.
			var tp_inst = this._get(inst, 'timepicker');
			if (tp_inst) {
				tp_inst._addTimePicker(inst);
			}
		}
	};

	/*
	 * third bad hack :/ override datepicker so it allows spaces and colon in the input field
	 */
	$.datepicker._base_doKeyPress = $.datepicker._doKeyPress;
	$.datepicker._doKeyPress = function (event) {
		var inst = $.datepicker._getInst(event.target),
			tp_inst = $.datepicker._get(inst, 'timepicker');

		if (tp_inst) {
			if ($.datepicker._get(inst, 'constrainInput')) {
				var ampm = tp_inst.support.ampm,
					tz = tp_inst._defaults.showTimezone !== null ? tp_inst._defaults.showTimezone : tp_inst.support.timezone,
					dateChars = $.datepicker._possibleChars($.datepicker._get(inst, 'dateFormat')),
					datetimeChars = tp_inst._defaults.timeFormat.toString()
							.replace(/[hms]/g, '')
							.replace(/TT/g, ampm ? 'APM' : '')
							.replace(/Tt/g, ampm ? 'AaPpMm' : '')
							.replace(/tT/g, ampm ? 'AaPpMm' : '')
							.replace(/T/g, ampm ? 'AP' : '')
							.replace(/tt/g, ampm ? 'apm' : '')
							.replace(/t/g, ampm ? 'ap' : '') +
						" " + tp_inst._defaults.separator +
						tp_inst._defaults.timeSuffix +
						(tz ? tp_inst._defaults.timezoneList.join('') : '') +
						(tp_inst._defaults.amNames.join('')) + (tp_inst._defaults.pmNames.join('')) +
						dateChars,
					chr = String.fromCharCode(event.charCode === undefined ? event.keyCode : event.charCode);
				return event.ctrlKey || (chr < ' ' || !dateChars || datetimeChars.indexOf(chr) > -1);
			}
		}

		return $.datepicker._base_doKeyPress(event);
	};

	/*
	 * Fourth bad hack :/ override _updateAlternate function used in inline mode to init altField
	 * Update any alternate field to synchronise with the main field.
	 */
	$.datepicker._base_updateAlternate = $.datepicker._updateAlternate;
	$.datepicker._updateAlternate = function (inst) {
		var tp_inst = this._get(inst, 'timepicker');
		if (tp_inst) {
			var altField = tp_inst._defaults.altField;
			if (altField) { // update alternate field too
				var altFormat = tp_inst._defaults.altFormat || tp_inst._defaults.dateFormat,
					date = this._getDate(inst),
					formatCfg = $.datepicker._getFormatConfig(inst),
					altFormattedDateTime = '',
					altSeparator = tp_inst._defaults.altSeparator ? tp_inst._defaults.altSeparator : tp_inst._defaults.separator,
					altTimeSuffix = tp_inst._defaults.altTimeSuffix ? tp_inst._defaults.altTimeSuffix : tp_inst._defaults.timeSuffix,
					altTimeFormat = tp_inst._defaults.altTimeFormat !== null ? tp_inst._defaults.altTimeFormat : tp_inst._defaults.timeFormat;

				altFormattedDateTime += $.datepicker.formatTime(altTimeFormat, tp_inst, tp_inst._defaults) + altTimeSuffix;
				if (!tp_inst._defaults.timeOnly && !tp_inst._defaults.altFieldTimeOnly && date !== null) {
					if (tp_inst._defaults.altFormat) {
						altFormattedDateTime = $.datepicker.formatDate(tp_inst._defaults.altFormat, date, formatCfg) + altSeparator + altFormattedDateTime;
					}
					else {
						altFormattedDateTime = tp_inst.formattedDate + altSeparator + altFormattedDateTime;
					}
				}
				$(altField).val( inst.input.val() ? altFormattedDateTime : "");
			}
		}
		else {
			$.datepicker._base_updateAlternate(inst);
		}
	};

	/*
	 * Override key up event to sync manual input changes.
	 */
	$.datepicker._base_doKeyUp = $.datepicker._doKeyUp;
	$.datepicker._doKeyUp = function (event) {
		var inst = $.datepicker._getInst(event.target),
			tp_inst = $.datepicker._get(inst, 'timepicker');

		if (tp_inst) {
			if (tp_inst._defaults.timeOnly && (inst.input.val() !== inst.lastVal)) {
				try {
					$.datepicker._updateDatepicker(inst);
				} catch (err) {
					$.timepicker.log(err);
				}
			}
		}

		return $.datepicker._base_doKeyUp(event);
	};

	/*
	 * override "Today" button to also grab the time and set it to input field.
	 */
	$.datepicker._base_gotoToday = $.datepicker._gotoToday;
	$.datepicker._gotoToday = function (id) {
		var inst = this._getInst($(id)[0]);
		this._base_gotoToday(id);
		var tp_inst = this._get(inst, 'timepicker');
		if (!tp_inst) {
			return;
		}

		var tzoffset = $.timepicker.timezoneOffsetNumber(tp_inst.timezone);
		var now = new Date();
		now.setMinutes(now.getMinutes() + now.getTimezoneOffset() + parseInt(tzoffset, 10));
		this._setTime(inst, now);
		this._setDate(inst, now);
		tp_inst._onSelectHandler();
	};

	/*
	 * Disable & enable the Time in the datetimepicker
	 */
	$.datepicker._disableTimepickerDatepicker = function (target) {
		var inst = this._getInst(target);
		if (!inst) {
			return;
		}

		var tp_inst = this._get(inst, 'timepicker');
		$(target).datepicker('getDate'); // Init selected[Year|Month|Day]
		if (tp_inst) {
			inst.settings.showTimepicker = false;
			tp_inst._defaults.showTimepicker = false;
			tp_inst._updateDateTime(inst);
		}
	};

	$.datepicker._enableTimepickerDatepicker = function (target) {
		var inst = this._getInst(target);
		if (!inst) {
			return;
		}

		var tp_inst = this._get(inst, 'timepicker');
		$(target).datepicker('getDate'); // Init selected[Year|Month|Day]
		if (tp_inst) {
			inst.settings.showTimepicker = true;
			tp_inst._defaults.showTimepicker = true;
			tp_inst._addTimePicker(inst); // Could be disabled on page load
			tp_inst._updateDateTime(inst);
		}
	};

	/*
	 * Create our own set time function
	 */
	$.datepicker._setTime = function (inst, date) {
		var tp_inst = this._get(inst, 'timepicker');
		if (tp_inst) {
			var defaults = tp_inst._defaults;

			// calling _setTime with no date sets time to defaults
			tp_inst.hour = date ? date.getHours() : defaults.hour;
			tp_inst.minute = date ? date.getMinutes() : defaults.minute;
			tp_inst.second = date ? date.getSeconds() : defaults.second;
			tp_inst.millisec = date ? date.getMilliseconds() : defaults.millisec;
			tp_inst.microsec = date ? date.getMicroseconds() : defaults.microsec;

			//check if within min/max times..
			tp_inst._limitMinMaxDateTime(inst, true);

			tp_inst._onTimeChange();
			tp_inst._updateDateTime(inst);
		}
	};

	/*
	 * Create new public method to set only time, callable as $().datepicker('setTime', date)
	 */
	$.datepicker._setTimeDatepicker = function (target, date, withDate) {
		var inst = this._getInst(target);
		if (!inst) {
			return;
		}

		var tp_inst = this._get(inst, 'timepicker');

		if (tp_inst) {
			this._setDateFromField(inst);
			var tp_date;
			if (date) {
				if (typeof date === "string") {
					tp_inst._parseTime(date, withDate);
					tp_date = new Date();
					tp_date.setHours(tp_inst.hour, tp_inst.minute, tp_inst.second, tp_inst.millisec);
					tp_date.setMicroseconds(tp_inst.microsec);
				} else {
					tp_date = new Date(date.getTime());
					tp_date.setMicroseconds(date.getMicroseconds());
				}
				if (tp_date.toString() === 'Invalid Date') {
					tp_date = undefined;
				}
				this._setTime(inst, tp_date);
			}
		}

	};

	/*
	 * override setDate() to allow setting time too within Date object
	 */
	$.datepicker._base_setDateDatepicker = $.datepicker._setDateDatepicker;
	$.datepicker._setDateDatepicker = function (target, _date) {
		var inst = this._getInst(target);
		var date = _date;
		if (!inst) {
			return;
		}

		if (typeof(_date) === 'string') {
			date = new Date(_date);
			if (!date.getTime()) {
				this._base_setDateDatepicker.apply(this, arguments);
				date = $(target).datepicker('getDate');
			}
		}

		var tp_inst = this._get(inst, 'timepicker');
		var tp_date;
		if (date instanceof Date) {
			tp_date = new Date(date.getTime());
			tp_date.setMicroseconds(date.getMicroseconds());
		} else {
			tp_date = date;
		}

		// This is important if you are using the timezone option, javascript's Date
		// object will only return the timezone offset for the current locale, so we
		// adjust it accordingly.  If not using timezone option this won't matter..
		// If a timezone is different in tp, keep the timezone as is
		if (tp_inst && tp_date) {
			// look out for DST if tz wasn't specified
			if (!tp_inst.support.timezone && tp_inst._defaults.timezone === null) {
				tp_inst.timezone = tp_date.getTimezoneOffset() * -1;
			}
			date = $.timepicker.timezoneAdjust(date, $.timepicker.timezoneOffsetString(-date.getTimezoneOffset()), tp_inst.timezone);
			tp_date = $.timepicker.timezoneAdjust(tp_date, $.timepicker.timezoneOffsetString(-tp_date.getTimezoneOffset()), tp_inst.timezone);
		}

		this._updateDatepicker(inst);
		this._base_setDateDatepicker.apply(this, arguments);
		this._setTimeDatepicker(target, tp_date, true);
	};

	/*
	 * override getDate() to allow getting time too within Date object
	 */
	$.datepicker._base_getDateDatepicker = $.datepicker._getDateDatepicker;
	$.datepicker._getDateDatepicker = function (target, noDefault) {
		var inst = this._getInst(target);
		if (!inst) {
			return;
		}

		var tp_inst = this._get(inst, 'timepicker');

		if (tp_inst) {
			// if it hasn't yet been defined, grab from field
			if (inst.lastVal === undefined) {
				this._setDateFromField(inst, noDefault);
			}

			var date = this._getDate(inst);

			var currDT = null;

			if (tp_inst.$altInput && tp_inst._defaults.altFieldTimeOnly) {
				currDT = tp_inst.$input.val() + ' ' + tp_inst.$altInput.val();
			}
			else if (tp_inst.$input.get(0).tagName !== 'INPUT' && tp_inst.$altInput) {
				/**
				 * in case the datetimepicker has been applied to a non-input tag for inline UI,
				 * and the user has not configured the plugin to display only time in altInput,
				 * pick current date time from the altInput (and hope for the best, for now, until "ER1" is applied)
				 *
				 * @todo ER1. Since altInput can have a totally difference format, convert it to standard format by reading input format from "altFormat" and "altTimeFormat" option values
				 */
				currDT = tp_inst.$altInput.val();
			}
			else {
				currDT = tp_inst.$input.val();
			}

			if (date && tp_inst._parseTime(currDT, !inst.settings.timeOnly)) {
				date.setHours(tp_inst.hour, tp_inst.minute, tp_inst.second, tp_inst.millisec);
				date.setMicroseconds(tp_inst.microsec);

				// This is important if you are using the timezone option, javascript's Date
				// object will only return the timezone offset for the current locale, so we
				// adjust it accordingly.  If not using timezone option this won't matter..
				if (tp_inst.timezone != null) {
					// look out for DST if tz wasn't specified
					if (!tp_inst.support.timezone && tp_inst._defaults.timezone === null) {
						tp_inst.timezone = date.getTimezoneOffset() * -1;
					}
					date = $.timepicker.timezoneAdjust(date, tp_inst.timezone, $.timepicker.timezoneOffsetString(-date.getTimezoneOffset()));
				}
			}
			return date;
		}
		return this._base_getDateDatepicker(target, noDefault);
	};

	/*
	 * override parseDate() because UI 1.8.14 throws an error about "Extra characters"
	 * An option in datapicker to ignore extra format characters would be nicer.
	 */
	$.datepicker._base_parseDate = $.datepicker.parseDate;
	$.datepicker.parseDate = function (format, value, settings) {
		var date;
		try {
			date = this._base_parseDate(format, value, settings);
		} catch (err) {
			// Hack!  The error message ends with a colon, a space, and
			// the "extra" characters.  We rely on that instead of
			// attempting to perfectly reproduce the parsing algorithm.
			if (err.indexOf(":") >= 0) {
				date = this._base_parseDate(format, value.substring(0, value.length - (err.length - err.indexOf(':') - 2)), settings);
				$.timepicker.log("Error parsing the date string: " + err + "\ndate string = " + value + "\ndate format = " + format);
			} else {
				throw err;
			}
		}
		return date;
	};

	/*
	 * override formatDate to set date with time to the input
	 */
	$.datepicker._base_formatDate = $.datepicker._formatDate;
	$.datepicker._formatDate = function (inst, day, month, year) {
		var tp_inst = this._get(inst, 'timepicker');
		if (tp_inst) {
			tp_inst._updateDateTime(inst);
			return tp_inst.$input.val();
		}
		return this._base_formatDate(inst);
	};

	/*
	 * override options setter to add time to maxDate(Time) and minDate(Time). MaxDate
	 */
	$.datepicker._base_optionDatepicker = $.datepicker._optionDatepicker;
	$.datepicker._optionDatepicker = function (target, name, value) {
		var inst = this._getInst(target),
			name_clone;
		if (!inst) {
			return null;
		}

		var tp_inst = this._get(inst, 'timepicker');
		if (tp_inst) {
			var min = null,
				max = null,
				onselect = null,
				overrides = tp_inst._defaults.evnts,
				fns = {},
				prop,
				ret,
				oldVal,
				$target;
			if (typeof name === 'string') { // if min/max was set with the string
				if (name === 'minDate' || name === 'minDateTime') {
					min = value;
				} else if (name === 'maxDate' || name === 'maxDateTime') {
					max = value;
				} else if (name === 'onSelect') {
					onselect = value;
				} else if (overrides.hasOwnProperty(name)) {
					if (typeof (value) === 'undefined') {
						return overrides[name];
					}
					fns[name] = value;
					name_clone = {}; //empty results in exiting function after overrides updated
				}
			} else if (typeof name === 'object') { //if min/max was set with the JSON
				if (name.minDate) {
					min = name.minDate;
				} else if (name.minDateTime) {
					min = name.minDateTime;
				} else if (name.maxDate) {
					max = name.maxDate;
				} else if (name.maxDateTime) {
					max = name.maxDateTime;
				}
				for (prop in overrides) {
					if (overrides.hasOwnProperty(prop) && name[prop]) {
						fns[prop] = name[prop];
					}
				}
			}
			for (prop in fns) {
				if (fns.hasOwnProperty(prop)) {
					overrides[prop] = fns[prop];
					if (!name_clone) { name_clone = $.extend({}, name); }
					delete name_clone[prop];
				}
			}
			if (name_clone && isEmptyObject(name_clone)) { return; }
			if (min) { //if min was set
				if (min === 0) {
					min = new Date();
				} else {
					min = new Date(min);
				}
				tp_inst._defaults.minDate = min;
				tp_inst._defaults.minDateTime = min;
			} else if (max) { //if max was set
				if (max === 0) {
					max = new Date();
				} else {
					max = new Date(max);
				}
				tp_inst._defaults.maxDate = max;
				tp_inst._defaults.maxDateTime = max;
			} else if (onselect) {
				tp_inst._defaults.onSelect = onselect;
			}

			// Datepicker will override our date when we call _base_optionDatepicker when
			// calling minDate/maxDate, so we will first grab the value, call
			// _base_optionDatepicker, then set our value back.
			if(min || max){
				$target = $(target);
				oldVal = $target.datetimepicker('getDate');
				ret = this._base_optionDatepicker.call($.datepicker, target, name_clone || name, value);
				$target.datetimepicker('setDate', oldVal);
				return ret;
			}
		}
		if (value === undefined) {
			return this._base_optionDatepicker.call($.datepicker, target, name);
		}
		return this._base_optionDatepicker.call($.datepicker, target, name_clone || name, value);
	};

	/*
	 * jQuery isEmptyObject does not check hasOwnProperty - if someone has added to the object prototype,
	 * it will return false for all objects
	 */
	var isEmptyObject = function (obj) {
		var prop;
		for (prop in obj) {
			if (obj.hasOwnProperty(prop)) {
				return false;
			}
		}
		return true;
	};

	/*
	 * jQuery extend now ignores nulls!
	 */
	var extendRemove = function (target, props) {
		$.extend(target, props);
		for (var name in props) {
			if (props[name] === null || props[name] === undefined) {
				target[name] = props[name];
			}
		}
		return target;
	};

	/*
	 * Determine by the time format which units are supported
	 * Returns an object of booleans for each unit
	 */
	var detectSupport = function (timeFormat) {
		var tf = timeFormat.replace(/'.*?'/g, '').toLowerCase(), // removes literals
			isIn = function (f, t) { // does the format contain the token?
				return f.indexOf(t) !== -1 ? true : false;
			};
		return {
			hour: isIn(tf, 'h'),
			minute: isIn(tf, 'm'),
			second: isIn(tf, 's'),
			millisec: isIn(tf, 'l'),
			microsec: isIn(tf, 'c'),
			timezone: isIn(tf, 'z'),
			ampm: isIn(tf, 't') && isIn(timeFormat, 'h'),
			iso8601: isIn(timeFormat, 'Z')
		};
	};

	/*
	 * Converts 24 hour format into 12 hour
	 * Returns 12 hour without leading 0
	 */
	var convert24to12 = function (hour) {
		hour %= 12;

		if (hour === 0) {
			hour = 12;
		}

		return String(hour);
	};

	var computeEffectiveSetting = function (settings, property) {
		return settings && settings[property] ? settings[property] : $.timepicker._defaults[property];
	};

	/*
	 * Splits datetime string into date and time substrings.
	 * Throws exception when date can't be parsed
	 * Returns {dateString: dateString, timeString: timeString}
	 */
	var splitDateTime = function (dateTimeString, timeSettings) {
		// The idea is to get the number separator occurrences in datetime and the time format requested (since time has
		// fewer unknowns, mostly numbers and am/pm). We will use the time pattern to split.
		var separator = computeEffectiveSetting(timeSettings, 'separator'),
			format = computeEffectiveSetting(timeSettings, 'timeFormat'),
			timeParts = format.split(separator), // how many occurrences of separator may be in our format?
			timePartsLen = timeParts.length,
			allParts = dateTimeString.split(separator),
			allPartsLen = allParts.length;

		if (allPartsLen > 1) {
			return {
				dateString: allParts.splice(0, allPartsLen - timePartsLen).join(separator),
				timeString: allParts.splice(0, timePartsLen).join(separator)
			};
		}

		return {
			dateString: dateTimeString,
			timeString: ''
		};
	};

	/*
	 * Internal function to parse datetime interval
	 * Returns: {date: Date, timeObj: Object}, where
	 *   date - parsed date without time (type Date)
	 *   timeObj = {hour: , minute: , second: , millisec: , microsec: } - parsed time. Optional
	 */
	var parseDateTimeInternal = function (dateFormat, timeFormat, dateTimeString, dateSettings, timeSettings) {
		var date,
			parts,
			parsedTime;

		parts = splitDateTime(dateTimeString, timeSettings);
		date = $.datepicker._base_parseDate(dateFormat, parts.dateString, dateSettings);

		if (parts.timeString === '') {
			return {
				date: date
			};
		}

		parsedTime = $.datepicker.parseTime(timeFormat, parts.timeString, timeSettings);

		if (!parsedTime) {
			throw 'Wrong time format';
		}

		return {
			date: date,
			timeObj: parsedTime
		};
	};

	/*
	 * Internal function to set timezone_select to the local timezone
	 */
	var selectLocalTimezone = function (tp_inst, date) {
		if (tp_inst && tp_inst.timezone_select) {
			var now = date || new Date();
			tp_inst.timezone_select.val(-now.getTimezoneOffset());
		}
	};

	/*
	 * Create a Singleton Instance
	 */
	$.timepicker = new Timepicker();

	/**
	 * Get the timezone offset as string from a date object (eg '+0530' for UTC+5.5)
	 * @param {number} tzMinutes if not a number, less than -720 (-1200), or greater than 840 (+1400) this value is returned
	 * @param {boolean} iso8601 if true formats in accordance to iso8601 "+12:45"
	 * @return {string}
	 */
	$.timepicker.timezoneOffsetString = function (tzMinutes, iso8601) {
		if (isNaN(tzMinutes) || tzMinutes > 840 || tzMinutes < -720) {
			return tzMinutes;
		}

		var off = tzMinutes,
			minutes = off % 60,
			hours = (off - minutes) / 60,
			iso = iso8601 ? ':' : '',
			tz = (off >= 0 ? '+' : '-') + ('0' + Math.abs(hours)).slice(-2) + iso + ('0' + Math.abs(minutes)).slice(-2);

		if (tz === '+00:00') {
			return 'Z';
		}
		return tz;
	};

	/**
	 * Get the number in minutes that represents a timezone string
	 * @param  {string} tzString formatted like "+0500", "-1245", "Z"
	 * @return {number} the offset minutes or the original string if it doesn't match expectations
	 */
	$.timepicker.timezoneOffsetNumber = function (tzString) {
		var normalized = tzString.toString().replace(':', ''); // excuse any iso8601, end up with "+1245"

		if (normalized.toUpperCase() === 'Z') { // if iso8601 with Z, its 0 minute offset
			return 0;
		}

		if (!/^(\-|\+)\d{4}$/.test(normalized)) { // possibly a user defined tz, so just give it back
			return parseInt(tzString, 10);
		}

		return ((normalized.substr(0, 1) === '-' ? -1 : 1) * // plus or minus
		((parseInt(normalized.substr(1, 2), 10) * 60) + // hours (converted to minutes)
		parseInt(normalized.substr(3, 2), 10))); // minutes
	};

	/**
	 * No way to set timezone in js Date, so we must adjust the minutes to compensate. (think setDate, getDate)
	 * @param  {Date} date
	 * @param  {string} fromTimezone formatted like "+0500", "-1245"
	 * @param  {string} toTimezone formatted like "+0500", "-1245"
	 * @return {Date}
	 */
	$.timepicker.timezoneAdjust = function (date, fromTimezone, toTimezone) {
		var fromTz = $.timepicker.timezoneOffsetNumber(fromTimezone);
		var toTz = $.timepicker.timezoneOffsetNumber(toTimezone);
		if (!isNaN(toTz)) {
			date.setMinutes(date.getMinutes() + (-fromTz) - (-toTz));
		}
		return date;
	};

	/**
	 * Calls `timepicker()` on the `startTime` and `endTime` elements, and configures them to
	 * enforce date range limits.
	 * n.b. The input value must be correctly formatted (reformatting is not supported)
	 * @param  {Element} startTime
	 * @param  {Element} endTime
	 * @param  {Object} options Options for the timepicker() call
	 * @return {jQuery}
	 */
	$.timepicker.timeRange = function (startTime, endTime, options) {
		return $.timepicker.handleRange('timepicker', startTime, endTime, options);
	};

	/**
	 * Calls `datetimepicker` on the `startTime` and `endTime` elements, and configures them to
	 * enforce date range limits.
	 * @param  {Element} startTime
	 * @param  {Element} endTime
	 * @param  {Object} options Options for the `timepicker()` call. Also supports `reformat`,
	 *   a boolean value that can be used to reformat the input values to the `dateFormat`.
	 * @param  {string} method Can be used to specify the type of picker to be added
	 * @return {jQuery}
	 */
	$.timepicker.datetimeRange = function (startTime, endTime, options) {
		$.timepicker.handleRange('datetimepicker', startTime, endTime, options);
	};

	/**
	 * Calls `datepicker` on the `startTime` and `endTime` elements, and configures them to
	 * enforce date range limits.
	 * @param  {Element} startTime
	 * @param  {Element} endTime
	 * @param  {Object} options Options for the `timepicker()` call. Also supports `reformat`,
	 *   a boolean value that can be used to reformat the input values to the `dateFormat`.
	 * @return {jQuery}
	 */
	$.timepicker.dateRange = function (startTime, endTime, options) {
		$.timepicker.handleRange('datepicker', startTime, endTime, options);
	};

	/**
	 * Calls `method` on the `startTime` and `endTime` elements, and configures them to
	 * enforce date range limits.
	 * @param  {string} method Can be used to specify the type of picker to be added
	 * @param  {Element} startTime
	 * @param  {Element} endTime
	 * @param  {Object} options Options for the `timepicker()` call. Also supports `reformat`,
	 *   a boolean value that can be used to reformat the input values to the `dateFormat`.
	 * @return {jQuery}
	 */
	$.timepicker.handleRange = function (method, startTime, endTime, options) {
		options = $.extend({}, {
			minInterval: 0, // min allowed interval in milliseconds
			maxInterval: 0, // max allowed interval in milliseconds
			start: {},      // options for start picker
			end: {}         // options for end picker
		}, options);

		// for the mean time this fixes an issue with calling getDate with timepicker()
		var timeOnly = false;
		if(method === 'timepicker'){
			timeOnly = true;
			method = 'datetimepicker';
		}

		function checkDates(changed, other) {
			var startdt = startTime[method]('getDate'),
				enddt = endTime[method]('getDate'),
				changeddt = changed[method]('getDate');

			if (startdt !== null) {
				var minDate = new Date(startdt.getTime()),
					maxDate = new Date(startdt.getTime());

				minDate.setMilliseconds(minDate.getMilliseconds() + options.minInterval);
				maxDate.setMilliseconds(maxDate.getMilliseconds() + options.maxInterval);

				if (options.minInterval > 0 && minDate > enddt) { // minInterval check
					endTime[method]('setDate', minDate);
				}
				else if (options.maxInterval > 0 && maxDate < enddt) { // max interval check
					endTime[method]('setDate', maxDate);
				}
				else if (startdt > enddt) {
					other[method]('setDate', changeddt);
				}
			}
		}

		function selected(changed, other, option) {
			if (!changed.val()) {
				return;
			}
			var date = changed[method].call(changed, 'getDate');
			if (date !== null && options.minInterval > 0) {
				if (option === 'minDate') {
					date.setMilliseconds(date.getMilliseconds() + options.minInterval);
				}
				if (option === 'maxDate') {
					date.setMilliseconds(date.getMilliseconds() - options.minInterval);
				}
			}

			if (date.getTime) {
				other[method].call(other, 'option', option, date);
			}
		}

		$.fn[method].call(startTime, $.extend({
			timeOnly: timeOnly,
			onClose: function (dateText, inst) {
				checkDates($(this), endTime);
			},
			onSelect: function (selectedDateTime) {
				selected($(this), endTime, 'minDate');
			}
		}, options, options.start));
		$.fn[method].call(endTime, $.extend({
			timeOnly: timeOnly,
			onClose: function (dateText, inst) {
				checkDates($(this), startTime);
			},
			onSelect: function (selectedDateTime) {
				selected($(this), startTime, 'maxDate');
			}
		}, options, options.end));

		checkDates(startTime, endTime);

		selected(startTime, endTime, 'minDate');
		selected(endTime, startTime, 'maxDate');

		return $([startTime.get(0), endTime.get(0)]);
	};

	/**
	 * Log error or data to the console during error or debugging
	 * @param  {Object} err pass any type object to log to the console during error or debugging
	 * @return {void}
	 */
	$.timepicker.log = function () {
		// Older IE (9, maybe 10) throw error on accessing `window.console.log.apply`, so check first.
		if (window.console && window.console.log && window.console.log.apply) {
			window.console.log.apply(window.console, Array.prototype.slice.call(arguments));
		}
	};

	/*
	 * Add util object to allow access to private methods for testability.
	 */
	$.timepicker._util = {
		_extendRemove: extendRemove,
		_isEmptyObject: isEmptyObject,
		_convert24to12: convert24to12,
		_detectSupport: detectSupport,
		_selectLocalTimezone: selectLocalTimezone,
		_computeEffectiveSetting: computeEffectiveSetting,
		_splitDateTime: splitDateTime,
		_parseDateTimeInternal: parseDateTimeInternal
	};

	/*
	 * Microsecond support
	 */
	if (!Date.prototype.getMicroseconds) {
		Date.prototype.microseconds = 0;
		Date.prototype.getMicroseconds = function () { return this.microseconds; };
		Date.prototype.setMicroseconds = function (m) {
			this.setMilliseconds(this.getMilliseconds() + Math.floor(m / 1000));
			this.microseconds = m % 1000;
			return this;
		};
	}

	/*
	 * Keep up with the version
	 */
	$.timepicker.version = "1.6.3";

}));

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

;
(function ($) {

	$(function () {
		// mask phone fields with domestic formatting
		$('.js-phone-domestic').mask('(999) 999-9999');
	});

})(jQuery);

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
