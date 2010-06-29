(function ($)
{
	///////////////////////////////////
	// jQuery validate plug-in
	// Written by Mathieu Bouchard
	// Client-side by Peter El-Khouri
	///////////////////////////////////

	$.fn.validate = function (options)
	{
		var settings = $.extend(
		{
			ajaxURL: null,
			inputKeyUpDelay: 750

		}, options);

		var context = this;
		var visuals;
		var tooltip;

		// private methods
		var bindFieldEvents = function ()
		{
			$(":text, :password, textarea", context).keydown(function ()
			{
				var field = $(this);
				window.clearTimeout($.data(document.body, "timeout"));
				$.data(document.body, "timeout", window.setTimeout(function ()
				{
					validate($(field));
				}, settings.inputKeyUpDelay));

				if (visuals.isAngry(field))
				{
					visuals.reset(field);
					triggerResetEvent(field);
				}
			});

			$(":checkbox, :radio", context).click(function ()
			{
				window.clearTimeout($.data(document.body, "timeout"));
				validate($(this));
			});

			$(context).submit(function ()
			{
				window.clearTimeout($.data(document.body, "timeout"));
				return validate($(this));
			});
		};

		var validate = function (field, submitCallback)
		{
			if (isClientSide())
			{
				return askClient(field, submitCallback);
			}
			else
			{
				return askServer(field, submitCallback);
			}
		};

		var askServer = function (field, submitCallback)
		{
			var affectedFields = getFieldCollection(field);

			visuals.onBeforeValidationCheck(affectedFields);
			triggerLoadEvents(affectedFields);

			$.ajax(
			{
				url: settings.ajaxURL,
				type: "POST",
				async: true,
				data: getParameters(getUniquelyNamedFieldCollection(field)),
				dataType: "xml",
				success: function (data, textStatus) { onResponseFromServer(data, textStatus, field, submitCallback); }
			});

			return false;
		};

		var askClient = function (eventField, submitCallback)
		{
			var fullSuccess = true;
			var affectedFields = getUniquelyNamedFieldCollection(eventField);

			visuals.onBeforeValidationCheck(affectedFields);
			triggerLoadEvents(affectedFields);

			affectedFields.each(function ()
			{
				var field = $(this);
				var failMessages = [];
				var passMessages = [];
				var name = field.attr("name");
				var value = getValue(field);

				var f = new Field(name, value);

				$("span.constraint." + name.stripBrackets(), context).each(function ()
				{
					var type = $("input[name=type]", this).val();
					var against = $("input[name=against]", this).val();
					var min = $("input[name=min]", this).val();
					var max = $("input[name=max]", this).val();
					var fail = $("input[name=fail]", this).val();
					var pass = $("input[name=pass]", this).val();

					f.addConstraint(type, against, min, max);

					if (fail !== undefined)
					{
						failMessages.push(fail);
					}

					if (pass !== undefined)
					{
						passMessages.push(pass);
					}
				});

				var fieldSuccess = f.isValid();
				fullSuccess = fieldSuccess && fullSuccess;

				visuals.onAfterValidationCheck(field, fieldSuccess, failMessages, passMessages);
				triggerResponseEvents(field, fieldSuccess);
			});

			return processSubmit(submitCallback, fullSuccess, eventField);
		};

		var onResponseFromServer = function (data, textStatus, eventField, submitCallback)
		{
			var receivedProperResponse = (textStatus == "success");

			if (receivedProperResponse)
			{
				var nsPrefix = "";
				var rootElement = $(nsPrefix + "constraint-results", data);
				var fullSuccess = rootElement.attr("success") == "true";

				$(nsPrefix + "field", data).each(function ()
				{
					var name = $(this).attr("name");
					var fieldSuccess = $(this).attr("success") == "true";
					var field = $("*[name=" + name.escapeName() + "], *[name=" + (name + "[]").escapeName() + "]", context);
					var failMessages = [];
					var passMessages = [];

					$(nsPrefix + "constraint-result", this).each(function ()
					{
						if ($(this).attr("success") == "false")
						{
							failMessages.push($(this).text());
						}
						else
						{
							passMessages.push($(this).text());
						}
					});

					visuals.onAfterValidationCheck(field, fieldSuccess, failMessages, passMessages);
					triggerResponseEvents(field, fieldSuccess);
				});

				return processSubmit(submitCallback, fullSuccess, eventField);
			}

			return false;
		};

		var processSubmit = function (submitCallback, fullSuccess, eventField)
		{
			if (submitCallback)
			{
				return submitCallback(fullSuccess);
			}
			else
			{
				if (isClientSide())
				{
					return fullSuccess;
				}
				else if (eventField.is("form") && fullSuccess)
				{
					eventField.unbind("submit");
					eventField.trigger("submit");

					return false;
				}
			}
		}

		var getParameters = function (fieldCollection)
		{
			var fieldList = {};

			fieldCollection.each(function ()
			{
				fieldList[this.name.stripBrackets()] = getValue($(this));

				getDependencyFieldCollection(this.name).each(function ()
				{
					fieldList[this.name.stripBrackets()] = getValue($(this));
				});
			});

			return (fieldList);
		};

		var getUniquelyNamedFieldCollection = function (field)
		{
			var fieldCollection = getFieldCollection(field);
			var lookup = [];

			var uniqueFieldCollection = fieldCollection.filter(function ()
			{
				var name = $(this).attr("name");

				if ($.inArray(name, lookup) != -1)
				{
					return (false);
				}
				else
				{
					lookup.push(name);
					return (true);
				}
			});

			return (uniqueFieldCollection);
		};

		var getFieldCollection = function (field)
		{
			var fieldCollection = null;

			if (field.is(":submit") || field.is("form"))
			{
				fieldCollection = $("input[type!='hidden'], textarea, select", context);
			}
			else
			{
				fieldCollection = $("*[name=" + field.attr("name") + "]", context);
			}

			return (fieldCollection);
		};

		var getDependencyFieldCollection = function (name)
		{
			var dependencyFieldCollection = $("input[name=" + (name.stripBrackets() + "--dependency[]").escapeName() + "]", context).map(function ()
			{
				return ($("*[name=" + this.value.escapeName() + "]", context).get());
			});

			return (dependencyFieldCollection);
		};

		var getValue = function (field)
		{
			var val = "NULL";

			if (field.is(":radio") || field.is(":checkbox"))
			{
				//var checkedFields = $( "input[ name=" + field.attr( "name" ).escapeName() + " ]:checked", context );
				// escapeName was removed for the match to occur
				var checkedFields = $("input[name=" + field.attr("name") + "]:checked", context);

				if (checkedFields.length > 0)
				{
					val = checkedFields.map(function ()
					{
						return ($(this).val());
					}).get();
				}
				else
				{
					val = "NULL";
				}
			}
			else if (field.is("select[multiple=true]"))
			{
				val = field.val();

				if (val == null || val == undefined)
				{
					val = "NULL";
				}
			}
			else
			{
				val = field.val();
			}

			if (val == null || val == undefined)
			{
				val = "";
			}

			return (val);
		};

		var triggerLoadEvents = function (fieldCollection)
		{
			fieldCollection.each(function ()
			{
				$(this).trigger("loadstart.validate");
			});
		};

		var triggerResponseEvents = function (field, success)
		{
			field.trigger("loadcomplete.validate");
			field.trigger(success ? "pass.validate" : "fail.validate");
		};

		var triggerResetEvent = function (field)
		{
			field.trigger("reset.validate");
		};

		var isClientSide = function ()
		{
			return settings.ajaxURL === null;
		};

		this.each(function ()
		{
			visuals = new Visuals(context);
			tooltip = new Tooltip(context);

			bindFieldEvents();
		});

		return this;
	};

	///////////////////////////////
	// Constraint class
	///////////////////////////////

	var Constraint = function (type, name, value)
	{
		var against = null;
		var min = null;
		var max = null;

		var isValid = function ()
		{
			switch (type)
			{
				case "regexp":
					return regExp();
					break;
				case "match":
					return match();
					break;
				case "match-field":
					return matchField();
					break;
				case "selected-count":
					return selectedCount();
					break;
				case "range":
					return range();
					break;
				case "email":
					return email();
					break;
				default:
					return false;
			}
		};

		var email = function ()
		{
			var result = /^(([A-Za-z0-9]+_+)|([A-Za-z0-9]+\-+)|([A-Za-z0-9]+\.+)|([A-Za-z0-9]+\++))*[A-Za-z0-9]+@((\w+\-+)|(\w+\.))*\w{1,63}\.[a-zA-Z]{2,6}$/.test(value);

			return result;
		};

		var regExp = function ()
		{
			return new RegExp(against).test(value);
		};

		var match = function ()
		{
			return value === against;
		};

		var matchField = function ()
		{
			// matchField does the same as match because we are comparing two values
			return match();
		};

		var matchFieldMD5 = function ()
		{
			// unsupported because user will never enter an MD5 value
			return true;
		};

		var selectedCount = function ()
		{
			if (value === "NULL")
			{
				value = [];
			}

			return withinRange(value.length, min, max);
		};

		var range = function ()
		{
			return withinRange(value, min, max);
		};

		var withinRange = function (value, min, max)
		{
			if ((min === undefined) && (max !== undefined))
			{
				return value <= max;
			}
			else if ((min !== undefined) && (max === undefined))
			{
				return value >= min;
			}
			else
			{
				return value >= min && value <= max;
			}
		};

		return {
			getType: function () { return type; },
			getName: function () { return name; },
			getValue: function () { return value; },
			setAgainst: function (value) { against = value; },
			setMin: function (value) { min = value; },
			setMax: function (value) { max = value; },
			isValid: function () { return isValid.apply(this, arguments); }
		};
	};

	///////////////////////////////
	// Field class
	///////////////////////////////

	var Field = function (name, value)
	{
		var constraints = [];

		var addConstraint = function (type, against, min, max)
		{
			var c = new Constraint(type, name, value);

			c.setAgainst(against);
			c.setMin(min);
			c.setMax(max);

			constraints.push(c);
		};

		var isValid = function ()
		{
			var valid = true;

			$.each(constraints, function (c, constraint)
			{
				valid = valid && constraint.isValid();
			});

			return valid;
		};

		return {
			getName: function () { return name; },
			getValue: function () { return value; },
			addConstraint: function () { return addConstraint.apply(this, arguments); },
			isValid: function () { return isValid.apply(this, arguments); }
		};
	};

	///////////////////////////////
	// Tooltip class
	///////////////////////////////

	var Tooltip = function (context)
	{
		var inputLocus = null;

		var bindInputEvents = function ()
		{
			$(":input", context).focus(function () { onFormInputFocus(this) });
			$(":input", context).blur(function () { onFormInputBlur(this) });
		};

		var bindConstraintEvents = function ()
		{
			$(":input", context).each(function ()
			{
				$(this).bind("loadstart.validate", onConstraintLoadStart);
				$(this).bind("loadcomplete.validate", onConstraintLoadComplete);
				$(this).bind("pass.validate", onConstraintPass);
				$(this).bind("fail.validate", onConstraintFail);
				$(this).bind("reset.validate", onConstraintReset);
			});
		};

		var onFormInputFocus = function (input)
		{
			inputLocus = input;
			showTooltip();
		};

		var onFormInputBlur = function (input)
		{
			if ($("#validate-tooltip:animated").length == 0)
			{
				inputLocus = input;
				hideTooltip();
			}
		};

		var showTooltip = function ()
		{
			var infoMessages;
			var failMessages;
			var passMessages;
			var messages;

			if (isClientSide())
			{
				var constraintContext = $("span.constraint." + $(inputLocus).attr("name").stripBrackets(), context);
				infoMessages = $("input[class=info]", constraintContext).map(function () { return $(this).val(); }).get();
				failMessages = $("input[class=fail]", constraintContext).map(function () { return $(this).val(); }).get();
				passMessages = $("input[class=pass]", constraintContext).map(function () { return $(this).val(); }).get();
			}
			else
			{
				infoMessages = $(inputLocus).closest("label").find("input[class=info]").map(function () { return $(this).val(); }).get();
				failMessages = $(inputLocus).closest("label").find("input[class=fail]").map(function () { return $(this).val(); }).get();
				passMessages = $(inputLocus).closest("label").find("input[class=pass]").map(function () { return $(this).val(); }).get();
			}

			if (passMessages.length > 0 && failMessages.length == 0)
			{
				// Pass on all constraints, no reaction
				messages = [];
				$("#validate-tooltip").removeClass("angry");
			}
			else if (failMessages.length > 0)
			{
				// One or more failed constraints, get angry
				messages = failMessages;
				$("#validate-tooltip").addClass("angry");
			}
			else if (infoMessages.length > 0)
			{
				// Haven't communicated with server for messages or we've been reset, show info
				messages = infoMessages;
				$("#validate-tooltip").removeClass("angry");
			}
			else
			{
				// Same as above, except there was no info available, so hide tooltip
				messages = [];
				hideTooltip();
			}

			//alert(messages);

			if (messages.length > 0)
			{
				$("#validate-tooltip .validate-tooltip").html(getHTMLMessage(messages));
				$("#validate-tooltip").stop();
				$("#validate-tooltip").css( "opacity", 1.0 );
				$("#validate-tooltip").show();
				$("#validate-tooltip").offset(determineTooltipPosition());
			}
		};

		var getHTMLMessage = function (messages)
		{
			var htmlMessage;

			htmlMessage = $("<ul />");

			$.each(messages, function (key, message)
			{
				htmlMessage.append($("<li />").html(message));
			});

			return htmlMessage;
		};

		var determineTooltipPosition = function ()
		{
			var target;
			var leftOffset;
			var topOffset;

			if ($(inputLocus).is(":checkbox, :radio"))
			{
				target = $(inputLocus).closest("label").find("span");
				leftOffset = 55;
				topOffset = -20;

			}
			else
			{
				target = $(inputLocus);
				leftOffset = 0;
				topOffset = -10;
			}

			if ($.browser.msie && parseInt($.browser.version) < 7)
			{
				topOffset -= 10;
			}

			var pos = target.offset();

			pos.left += (target.outerWidth() - $("#validate-tooltip").width() + leftOffset);
			pos.top -= ($("#validate-tooltip").height() + topOffset);

			return pos;
		};

		var hideTooltip = function ()
		{
			if ($("#validate-tooltip:visible").length > 0)
			{
				$("#validate-tooltip").stop();
				if ($.browser.msie)
				{
					$("#validate-tooltip").hide();
				}
				else
				{
					$("#validate-tooltip").fadeOut();
				}
			}
		};

		var onConstraintLoadStart = function (data)
		{
		};

		var onConstraintLoadComplete = function (data)
		{
		};

		var onConstraintPass = function (data)
		{
			if ($(inputLocus)[0] === $(data.target)[0])
			{
				hideTooltip();
			}
		};

		var onConstraintFail = function (data)
		{
			if ($(inputLocus)[0] === $(data.target)[0])
			{
				showTooltip();
			}
			else
			{
				// Note that this won't work as expected if there is more than one checkbox on the page.
				// This should be fixed for a more long-term solution.
				if ($(data.target).is(":checkbox, :radio"))
				{
					inputLocus = data.target;
					showTooltip();
				}
			}
		};

		var onConstraintReset = function (data)
		{
			showTooltip();
		};

		var isClientSide = function ()
		{
			return $("span.constraint", context).length > 0;
		};

		var construct = function ()
		{
			// This should be revisited, where perhaps this shouldn't be an ID so we can use it on multiple forms on the same page
			if ($("#validate-tooltip").length === 0)
			{
				$("body").append("<div id='validate-tooltip'><div class='validate-tooltip' /></div>");
			}

			bindInputEvents();
			bindConstraintEvents();
		} ();
	};

	///////////////////////////////
	// Visuals class
	///////////////////////////////

	var Visuals = function (context)
	{
		var reset = function (field)
		{
			var closestLabel = field.closest("label");

			closestLabel.removeClass("validate-loading");
			closestLabel.removeClass("validate-success");
			closestLabel.removeClass("validate-fail");

			if (isClientSide())
			{
				var constraintContext = $("span.constraint." + field.attr("name").stripBrackets(), context);
				$("input.fail", constraintContext).removeClass("fail");
				$("input.pass", constraintContext).removeClass("pass");
			}
			else
			{
				$("input[ class='fail' ], input[ class='pass' ]", closestLabel).remove();
			}
		};

		var onBeforeValidationCheck = function (fieldCollection)
		{
			fieldCollection.each(function ()
			{
				reset($(this));

				$(this).closest("label").addClass("validate-loading");
			});
		};

		var onAfterValidationCheck = function (field, valid, failMessages, passMessages)
		{
			var closestLabel = field.closest("label");
			var constraintContext = $("span.constraint." + field.attr("name").stripBrackets(), context);

			closestLabel.removeClass("validate-loading");
			closestLabel.addClass(valid ? "validate-success" : "validate-fail");

			setupMessageInputs(failMessages, valid, "fail", closestLabel, constraintContext);
			setupMessageInputs(passMessages, valid, "pass", closestLabel, constraintContext);
		};

		var setupMessageInputs = function (messages, valid, type, closestLabel, constraintContext)
		{
			var box = null;
			var fail = type === "fail";
			var pass = type === "pass";

			$.each(messages, function (key, message)
			{
				if (isClientSide())
				{
					if ((fail && !valid) || (pass && valid))
					{
						$("input[value=" + message.replace("\"", "\\\"") + "]", constraintContext).addClass(type);
					}
				}
				else
				{
					if ($("input[value=" + message.replace("\"", "\\\"") + "]", closestLabel).length == 0)
					{
						box = $("<input class='" + type + "' type='hidden' />");
						box.val(message);
						closestLabel.append(box);
					}
				}
			});
		};

		var isAngry = function (field)
		{
			return (field.closest("label").hasClass("validate-fail"));
		};

		var isClientSide = function ()
		{
			return $("span.constraint", context).length > 0;
		};

		return {
			reset: function () { return reset.apply(this, arguments); },
			onBeforeValidationCheck: function () { return onBeforeValidationCheck.apply(this, arguments); },
			onAfterValidationCheck: function () { return onAfterValidationCheck.apply(this, arguments); },
			isAngry: function () { return isAngry.apply(this, arguments); }
		};
	};

	String.prototype.stripBrackets = function ()
	{
		return this.replace("[", "").replace("]", "").replace("\\[", "").replace("\\]", "");
	};

	String.prototype.escapeName = function ()
	{
		if (!$.browser.msie)
		{
			return this.replace("[", "\\[").replace("]", "\\]");
		}

		return this;
	};

})(jQuery);