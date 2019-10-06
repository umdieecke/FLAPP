jQuery(function ($) {
	/**
	 * _|_|_|_|  _|_|_|    _|_|_|    _|_|_|    _|_|_|  _|    _|  _|        _|    _|  _|_|_|_|  _|_|_|_|_|
	 * _|        _|    _|    _|    _|        _|        _|    _|  _|        _|    _|  _|            _|
	 * _|_|_|    _|_|_|      _|      _|_|    _|        _|_|_|_|  _|        _|    _|  _|_|_|        _|
	 * _|        _|    _|    _|          _|  _|        _|    _|  _|        _|    _|  _|            _|
	 * _|        _|    _|  _|_|_|  _|_|_|      _|_|_|  _|    _|  _|_|_|_|    _|_|    _|            _|
	 **/

	/* Classfile for better developer experience
	 * Frischluft Medien OG, 2016-08-22
	 * based on umdieecke.lib
	 * for documentation see docs folder
	 *******************************************************************
	 * Rules:
	 * Max-function-lines: x Lines of Code between brackets.
	 * Max-function-statements: x Statements, delimited by a semicolon.
	 *
	 * Please stick to the credo to retain readability and structure.
	 */

	"use strict";
	var FL = function (config) {
		this.config = config || {};
	};

	/*****************************
	 *  Config-dependent Methods
	 ****************************/


	/*****************************
	 *  Static Methods
	 ****************************/
	/*======= trigger scrolling manually ======= */
	FL.scrollVertical = function (targetPosition, duration) {
		duration = parseInt(duration);
		if (isNaN(duration)) duration = 400;
		$("html, body").stop().animate({
			scrollTop: targetPosition
		}, duration);
	};

	/*======= decrypt a given string =======*/
	FL.decryptText = function (s) {
		var n = 0;
		var r = "";
		for (var i = 0; i < s.length; i++) {
			n = s.charCodeAt(i);
			if (n >= 8364) {
				n = 128;
			}
			r += String.fromCharCode(n - 1);
		}
		return r;
	};


	/* ==========================================
	 Send an ajax Request via post;
	 Add data-object, a callback-function and error_handling-function as parameter
	 ========================================== */
	FL.postRequest = function (url, data, successHandlanger, errorHandlanger, completeHandlanger) {
		$.ajax({
			type: "POST",
			url: url,
			//headers: headers || {}, --> Wird eingebaut, sobald wir es das erste mal benötigen.
			dataType: "json",
			data: data,
			success: function (response) {
				if (FL.helpers.isFunction(successHandlanger))
					successHandlanger(response);
			},
			error: function (response) {
				if (FL.helpers.isFunction(errorHandlanger))
					errorHandlanger(response);
			},
			complete: function (response) {
				if (FL.helpers.isFunction(completeHandlanger))
					completeHandlanger(response);
			}
		})
	};

	/*****************************
	 *  Helpers
	 ****************************/
	FL.helpers = {};
	FL.helpers.replaceDotByComma = function (s) {
		s = String(s);
		return s.replaceAll(".", ",");
	};

	FL.helpers.getPercentageStrFromNumber = function (n) {
		return (n * 100).toFixed(2) + "%";
	};

	FL.helpers.inheritObject = function (d, b) {
		for (var p in b)
			if (b.hasOwnProperty(p))
				d[p] = b[p];
		function __() {
			this.constructor = d;
		}

		__.prototype = b.prototype;
		d.prototype = new __();
	};

	FL.helpers.isLocalStorageEnabled = function () {
		return (typeof(Storage) !== "undefined");
	};

	FL.helpers.getIndexOfObjectInArrayByPropertyvalue = function (array, attr, value) {
		for (var i = 0; i < array.length; i++) {
			if (array[i][attr] == value)
				return i;
		}
		return -1;
	};

	FL.helpers.isFunction = function (functionToCheck) {
		var getType = {};
		return functionToCheck && getType.toString.call(functionToCheck) === '[object Function]';
	};

	FL.helpers.isString = function (s) {
		return typeof s === 'string' || s instanceof String
	};

	FL.helpers.capitalizeFirstLetter = function (string) {
		return string.charAt(0).toUpperCase() + string.slice(1);
	};


	/*****************************
	 *  Other useful Methods
	 ****************************/
	String.prototype.replaceAll = function (stringToFind, stringToReplace) {
		if (stringToFind == stringToReplace) return this;
		var temp = this;
		var index = temp.indexOf(stringToFind);
		while (index != -1) {
			temp = temp.replace(stringToFind, stringToReplace);
			index = temp.indexOf(stringToFind);
		}
		return temp;
	};

	/*****************************
	 *  Starters
	 ****************************/
	/*======= decrypting hrefs =======*/
	$(".decrypt-href").each(function () {
		var hrefAttr = $(this).attr("href");
		var slicePos = hrefAttr.search(/:.+/);
		var linkMode = hrefAttr.slice(0, slicePos + 1);
		var encryptedText = hrefAttr.slice(slicePos + 1);
		var decryptedText = decryptText(encryptedText);

		$(this).attr("href", linkMode + decryptedText);
	});

	/*======= creating mail link text from format servus [at] umdieecke [dot] at =======*/
	$(".create-mail-link-text").each(function () {
		var currentText = $(this).text();
		var convertedText = currentText.replace("[at]", "@").replace("[dot]", ".").replace(/\s/g, "");
		$(this).text(convertedText);
	});

	/*======= enabling smooth scrolling for anchors =======*/
	$("a[href^='#']").on('click', function (e) {
		var href = $(this).attr('href');
		if ($(href).length == 1) {
			var duration = $(this).attr("data-scroll-duration") ? $(this).attr("data-scroll-duration") : undefined;
			scrollVertical($(href).offset().top, duration);
			e.preventDefault();
		}
	});

	/*****************************
	 *  jQuery extensions
	 ****************************/
	var unidentifiedFieldCounter = 0;
	jQuery.fn.extend({
		/*======= get field identifier like name, id,... =======*/
		getIdentifier: function () {
			var $field = $(this);
			var identifier = $field.attr("id");
			if (identifier == undefined || identifier == null || identifier == "")
				identifier = $field.attr("name");
			if (identifier == undefined || identifier == null || identifier == "")
				identifier = $field.closest("*[id]").attr("id") + "_field_" + unidentifiedFieldCounter++;
			if (identifier == undefined || identifier == null || identifier == "")
				identifier = $field.closest("*[name]").attr("name") + "_field_" + unidentifiedFieldCounter++;
			if (identifier == undefined || identifier == null || identifier == "")
				identifier = "_field_" + unidentifiedFieldCounter;
			return identifier;
		},
		/*======= get set of all input values inside a container =======*/
		getInputValues: function () {
			var inputData = {};
			var $form = $(this);
			$form.find("input[type='hidden'], input[type='text'], input[type='number'], input[type='email'], input[type='phone'], select, textarea, input[type='checkbox']").each(function () {
				if ($(this).is("input[type='checkbox']"))
					inputData[$(this).getIdentifier()] = String($(this).is(":checked"));
				else
					inputData[$(this).getIdentifier()] = $(this).val();
			});
			return inputData;
		}
	});

	/*****************************
	 *  go public
	 ****************************/
	window.FL = FL;
}(jQuery));