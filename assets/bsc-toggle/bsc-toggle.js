/**
 * @package basecondition/components
 * @author Joachim Doerr
 * @copyright (C) mail@doerr-softwaredevelopment.com
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/*! ========================================================================
 * Bootstrap BSCToggle: bootstrap-toggle.js v2.2.0
 * http://www.bootstraptoggle.com
 * ========================================================================
 * Copyright 2014 Min Hur, The New York Times Company
 * Licensed under MIT
 * ======================================================================== */


 +function ($) {
 	'use strict';

	// TOGGLE PUBLIC CLASS DEFINITION
	// ==============================

	var BSCToggle = function (element, options) {
		this.$element  = $(element)
		this.options   = $.extend({}, this.defaults(), options)
		this.render()
	}

	BSCToggle.VERSION  = '2.2.0'

	BSCToggle.DEFAULTS = {
		on: 'On',
		off: 'Off',
		onstyle: 'primary',
		offstyle: 'default',
		size: 'normal',
		style: '',
		width: null,
		height: null
	}

	BSCToggle.prototype.defaults = function() {
		return {
			on: this.$element.attr('data-on') || BSCToggle.DEFAULTS.on,
			off: this.$element.attr('data-off') || BSCToggle.DEFAULTS.off,
			onstyle: this.$element.attr('data-onstyle') || BSCToggle.DEFAULTS.onstyle,
			offstyle: this.$element.attr('data-offstyle') || BSCToggle.DEFAULTS.offstyle,
			size: this.$element.attr('data-size') || BSCToggle.DEFAULTS.size,
			style: this.$element.attr('data-style') || BSCToggle.DEFAULTS.style,
			width: this.$element.attr('data-width') || BSCToggle.DEFAULTS.width,
			height: this.$element.attr('data-height') || BSCToggle.DEFAULTS.height
		}
	}

	BSCToggle.prototype.render = function () {
		this._onstyle = 'btn-' + this.options.onstyle
		this._offstyle = 'btn-' + this.options.offstyle
		var size = this.options.size === 'large' ? 'btn-lg'
			: this.options.size === 'small' ? 'btn-sm'
			: this.options.size === 'mini' ? 'btn-xs'
			: ''
		var $toggleOn = $('<label class="btn">').html(this.options.on)
			.addClass(this._onstyle + ' ' + size)
		var $toggleOff = $('<label class="btn">').html(this.options.off)
			.addClass(this._offstyle + ' ' + size + ' active')
		var $toggleHandle = $('<span class="bsc-toggle-handle btn btn-default">')
			.addClass(size)
		var $toggleGroup = $('<div class="bsc-toggle-group">')
			.append($toggleOn, $toggleOff, $toggleHandle)
		var $toggle = $('<div class="bsc-toggle btn" data-bsc-toggle="toggle">')
			.addClass( this.$element.prop('checked') ? this._onstyle : this._offstyle+' off' )
			.addClass(size).addClass(this.options.style)

		this.$element.wrap($toggle)
		$.extend(this, {
			$toggle: this.$element.parent(),
			$toggleOn: $toggleOn,
			$toggleOff: $toggleOff,
			$toggleGroup: $toggleGroup
		})
		this.$toggle.append($toggleGroup)

		var width = this.options.width || Math.max($toggleOn.outerWidth(), $toggleOff.outerWidth())+($toggleHandle.outerWidth()/2)
		var height = this.options.height || Math.max($toggleOn.outerHeight(), $toggleOff.outerHeight())
		$toggleOn.addClass('bsc-toggle-on')
		$toggleOff.addClass('bsc-toggle-off')
		this.$toggle.css({ width: width, height: height })
		if (this.options.height) {
			$toggleOn.css('line-height', $toggleOn.height() + 'px')
			$toggleOff.css('line-height', $toggleOff.height() + 'px')
		}
		this.update(true)
		this.trigger(true)
	}

	BSCToggle.prototype.toggle = function () {
		if (this.$element.prop('checked')) this.off()
		else this.on()
	}

	BSCToggle.prototype.on = function (silent) {
		if (this.$element.prop('disabled')) return false
		this.$toggle.removeClass(this._offstyle + ' off').addClass(this._onstyle)
		this.$element.prop('checked', true)
		if (!silent) this.trigger()
	}

	BSCToggle.prototype.off = function (silent) {
		if (this.$element.prop('disabled')) return false
		this.$toggle.removeClass(this._onstyle).addClass(this._offstyle + ' off')
		this.$element.prop('checked', false)
		if (!silent) this.trigger()
	}

	BSCToggle.prototype.enable = function () {
		this.$toggle.removeAttr('disabled')
		this.$element.prop('disabled', false)
	}

	BSCToggle.prototype.disable = function () {
		this.$toggle.attr('disabled', 'disabled')
		this.$element.prop('disabled', true)
	}

	BSCToggle.prototype.update = function (silent) {
		if (this.$element.prop('disabled')) this.disable()
		else this.enable()
		if (this.$element.prop('checked')) this.on(silent)
		else this.off(silent)
	}

	BSCToggle.prototype.trigger = function (silent) {
		this.$element.off('change.bsc.toggle')
		if (!silent) this.$element.change()
		this.$element.on('change.bsc.toggle', $.proxy(function() {
			this.update()
		}, this))
	}

	BSCToggle.prototype.destroy = function() {
		this.$element.off('change.bsc.toggle')
		this.$toggleGroup.remove()
		this.$element.removeData('bsc.toggle')
		this.$element.unwrap()
	}

	// TOGGLE PLUGIN DEFINITION
	// ========================

	function Plugin(option) {
		return this.each(function () {
			var $this   = $(this)
			var data    = $this.data('bsc.toggle')
			var options = typeof option == 'object' && option

			if (!data) $this.data('bsc.toggle', (data = new BSCToggle(this, options)))
			if (typeof option == 'string' && data[option]) data[option]()
		})
	}

	var old = $.fn.baseconditionToggle

	$.fn.baseconditionToggle             = Plugin
	$.fn.baseconditionToggle.Constructor = BSCToggle

	// TOGGLE NO CONFLICT
	// ==================

	$.fn.toggle.noConflict = function () {
		$.fn.baseconditionToggle = old
		return this
	}

	// TOGGLE DATA-API
	// ===============

	$(function() {
		$('input[type=checkbox][data-bsc-toggle^=toggle]').baseconditionToggle()
	})

	$(document).on('click.bsc.toggle', 'div[data-bsc-toggle^=toggle]', function(e) {
		var $checkbox = $(this).find('input[type=checkbox]')
		$checkbox.baseconditionToggle('toggle')
		e.preventDefault()
	})

}(jQuery);
