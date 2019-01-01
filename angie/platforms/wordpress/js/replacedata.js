/*
 * ANGIE - The site restoration script for backup archives created by Akeeba Backup and Akeeba Solo
 *
 * @package   angie
 * @copyright Copyright (c)2009-2019 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   http://www.gnu.org/copyleft/gpl.html GNU/GPL v3 or later
 */

var akeebaAjaxWP = null;

replacements = {
	resumeTimer: null,
	resume:
				 {
					 enabled:      true,
					 timeout:      10,
					 maxRetries:   3,
					 retry:        0,
					 showWarnings: 0
				 },
	editor:      {},
	strings:     {}
};

replacements.start = function () {
	document.getElementById('replacementsGUI').style.display      = 'none';
	document.getElementById('replacementsProgress').style.display = 'block';

	var elExtraTables = document.getElementById('extraTables');
	var extraTables   = [];

	for (var i = 0; i < elExtraTables.length; i++)
	{
		if (elExtraTables.options[i].selected)
		{
			extraTables.push(elExtraTables.options[i].value);
		}
	}

	var request = {
		'view':         'replacedata',
		'task':         'ajax',
		'method':       'init',
		'format':       'json',
		'replaceFrom':  document.getElementById('replaceFrom').value,
		'replaceTo':    document.getElementById('replaceTo').value,
		'extraTables':  extraTables,
		//'column_size':  document.getElementById('column_size').value,
		'batchSize':    document.getElementById('batchSize').value,
		'min_exec':     document.getElementById('min_exec').value,
		'max_exec':     document.getElementById('max_exec').value,
		'runtime_bias': document.getElementById('runtime_bias').value
	};

	akeebaAjaxWP.callJSON(request,
		replacements.process,
		replacements.onError
	);
};

replacements.process = function (data) {
	// Do we have errors/warnings?
	var error_message    = data.error;
	var warning_messages = data.warnings;

	if (error_message !== undefined && error_message != '')
	{
		try
		{
			console.error('Got an error message');
			console.log(error_message);
		}
		catch (e)
		{
		}

		// Uh-oh! An error has occurred.
		replacements.onError(error_message);

		return;
	}

	if (warning_messages && warning_messages.length > 0)
	{
		try
		{
			console.warn('Got a warning message');
			console.log(warning_messages);
		}
		catch (e)
		{
		}

		replacements.onWarning(warning_messages);
	}

	var elBlinkenLights = document.getElementById('blinkenlights');
	var blinkenSpans    = elBlinkenLights.querySelectorAll('span');
	elBlinkenLights.appendChild(blinkenSpans[0]);
	document.getElementById('replacementsProgressText').innerText = data.msg;

	if (!data.more)
	{
		window.location = document.getElementById('btnNext').href;

		return;
	}

	setTimeout(function () {
		replacements.step();
	}, 100);
};

replacements.step = function () {
	akeebaAjaxWP.callJSON({
			'view':   'replacedata',
			'task':   'ajax',
			'method': 'step',
			'format': 'json'
		},
		replacements.process,
		replacements.onError
	);
};

/**
 * Resume the data replacement step after an AJAX error has occurred.
 */
replacements.resumeReplacement = function () {
	// Make sure the timer is stopped
	replacements.resetRetryTimeoutBar();

	// Hide error and retry panels
	document.getElementById('error-panel').style.display = 'none';
	document.getElementById('retry-panel').style.display = 'none';

	// Show progress
	document.getElementById('replacementsProgress').style.display = 'block';

	// Restart the replacements
	setTimeout(function () {
		replacements.step();
	}, 100);
};

/**
 * Resets the last response timer bar
 */
replacements.resetRetryTimeoutBar = function () {
	clearInterval(replacements.resumeTimer);

	document.getElementById('akeeba-retry-timeout').textContent = replacements.resume.timeout.toFixed(0);
};

/**
 * Starts the timer for the last response timer
 */
replacements.startRetryTimeoutBar = function () {
	var remainingSeconds = replacements.resume.timeout;

	replacements.resumeTimer = setInterval(function () {
		remainingSeconds--;
		document.getElementById('akeeba-retry-timeout').textContent = remainingSeconds.toFixed(0);

		if (remainingSeconds == 0)
		{
			clearInterval(replacements.resumeTimer);
			replacements.resumeReplacement();
		}
	}, 1000);
};

/**
 * Cancel the automatic resumption of the replacement step after an AJAX error has occurred
 */
replacements.cancelResume = function () {
	// Make sure the timer is stopped
	replacements.resetRetryTimeoutBar();

	// Kill the replacement
	var errorMessage = document.getElementById('replacement-error-message-retry').innerHTML;
	replacements.endWithError(errorMessage);
};

replacements.onError = function (message) {
	// If we are past the max retries, die.
	if (replacements.resume.retry >= replacements.resume.maxRetries)
	{
		replacements.endWithError(message);

		return;
	}

	// Make sure the timer is stopped
	replacements.resume.retry++;
	replacements.resetRetryTimeoutBar();

	// Hide progress
	document.getElementById('replacementsProgress').style.display = 'none';
	document.getElementById('error-panel').style.display          = 'none';

	// Setup and show the retry pane
	document.getElementById('replacement-error-message-retry').textContent = message;
	document.getElementById('retry-panel').style.display                   = 'block';

	// Start the countdown
	replacements.startRetryTimeoutBar();
};

replacements.onWarning = function (messages) {
	document.getElementById('warning-panel').style.display = 'block';

	for (var index = 0; index < messages.length; index++)
	{
		var message     = messages[index];
		var elDiv       = document.createElement('div');
		elDiv.innerHTML = message;
		document.getElementById('warnings-list').appendChild(elDiv);
	}
};

/**
 * Terminate the backup with an error
 *
 * @param   message  The error message received
 */
replacements.endWithError = function (message) {
	// Hide progress
	document.getElementById('replacementsProgress').style.display = 'none';
	document.getElementById('retry-panel').style.display          = 'none';

	// Setup and show error pane
	document.getElementById('replacement-error-message').textContent = message;
	document.getElementById('error-panel').style.display             = 'block';
};

replacements.editor.render = function (containerId, keyValueData) {
	// Get the row container from the selector
	var elContainer = document.getElementById(containerId);

	// Store the key-value information as a data property
	akeeba.System.data.set(elContainer, 'keyValueData', keyValueData);

	// Render one GUI row per data row
	for (var valFrom in keyValueData)
	{
		// Skip if the key is a property from the object's prototype
		if (!keyValueData.hasOwnProperty(valFrom))
		{
			continue;
		}

		var valTo = keyValueData[valFrom];

		replacements.editor.renderRow(elContainer, valFrom, valTo);
	}

	// Add the last, empty row
	replacements.editor.renderRow(elContainer, "", "");
};

replacements.editor.renderRow = function (elContainer, valFrom, valTo) {
	var elRow       = document.createElement('div');
	elRow.className = 'keyValueLine akeeba-container--5-5-2';

	var elFromInput         = document.createElement("input");
	elFromInput.className   = "input-100 keyValueFrom";
	elFromInput.type        = 'text';
	elFromInput.title       = replacements.strings["lblKey"];
	elFromInput.placeholder = replacements.strings["lblKey"];
	elFromInput.value       = valFrom;

	var elToInput         = document.createElement("input");
	elToInput.className   = "input-100 keyValueTo";
	elToInput.type        = 'text';
	elToInput.title       = replacements.strings["lblValue"];
	elToInput.placeholder = replacements.strings["lblValue"];
	elToInput.value       = valTo;

	var elDeleteIcon       = document.createElement("span");
	elDeleteIcon.className = 'akion-trash-b';

	var elDeleteButton       = document.createElement("span");
	elDeleteButton.className = 'akeeba-btn--red keyValueButtonDelete';
	elDeleteButton.title     = replacements.strings["lblDelete"];
	elDeleteButton.appendChild(elDeleteIcon);

	var elUpIcon       = document.createElement("span");
	elUpIcon.className = 'akion-chevron-up';

	var elUpButton       = document.createElement("span");
	elUpButton.className = 'akeeba-btn--grey keyValueButtonUp';
	elUpButton.appendChild(elUpIcon);

	var elDownIcon       = document.createElement("span");
	elDownIcon.className = 'akion-chevron-down';

	var elDownButton       = document.createElement("span");
	elDownButton.className = 'akeeba-btn--grey keyValueButtonDown';
	elDownButton.appendChild(elDownIcon);

	var elFromWrapper       = document.createElement("div");
	elFromWrapper.className = 'keyValueFromWrapper';
	elFromWrapper.appendChild(elFromInput);

	var elToWrapper       = document.createElement("div");
	elToWrapper.className = 'keyValueToWrapper';
	elToWrapper.appendChild(elToInput);

	var elButtonsWrapper       = document.createElement("div");
	elButtonsWrapper.className = 'keyValueButtonsWrapper';
	elButtonsWrapper.appendChild(elDeleteButton);
	elButtonsWrapper.appendChild(elUpButton);
	elButtonsWrapper.appendChild(elDownButton);

	akeeba.System.addEventListener(elFromInput, 'blur', function (e) {
		replacements.editor.reflow(elContainer);
	});

	akeeba.System.addEventListener(elToInput, 'blur', function (e) {
		replacements.editor.reflow(elContainer);
	});

	akeeba.System.addEventListener(elDeleteButton, 'click', function (e) {
		elFromInput.value = '';
		elToInput.value   = '';

		replacements.editor.reflow(elContainer);
	});

	akeeba.System.addEventListener(elUpButton, 'click', function (e) {
		var elPrev = this.parentElement.parentElement.previousSibling;

		if (elPrev === null)
		{
			return;
		}

		var elPrevFrom = elPrev.querySelectorAll(".keyValueFrom")[0];
		var elPrevTo   = elPrev.querySelectorAll(".keyValueTo")[0];

		var prevFrom = elPrevFrom.value;
		var prevTo   = elPrevTo.value;

		elPrevFrom.value  = elFromInput.value;
		elPrevTo.value    = elToInput.value;
		elFromInput.value = prevFrom;
		elToInput.value   = prevTo;

		replacements.editor.reflow(elContainer);
	});

	akeeba.System.addEventListener(elDownButton, 'click', function (e) {
		var elNext = this.parentElement.parentElement.nextSibling;

		if (elNext === null)
		{
			return;
		}

		var elNextFrom = elNext.querySelectorAll(".keyValueFrom")[0];
		var elNextTo   = elNext.querySelectorAll(".keyValueTo")[0];

		var nextFrom = elNextFrom.value;
		var nextTo   = elNextTo.value;

		elNextFrom.value  = elFromInput.value;
		elNextTo.value    = elToInput.value;
		elFromInput.value = nextFrom;
		elToInput.value   = nextTo;

		replacements.editor.reflow(elContainer);
	});

	elRow.appendChild(elFromWrapper);
	elRow.appendChild(elToWrapper);
	elRow.appendChild(elButtonsWrapper);
	elContainer.appendChild(elRow);
};

replacements.editor.reflow = function (elContainer) {
	var data        = {};
	var strFrom     = "";
	var strTo       = "";
	var elRows      = elContainer.querySelectorAll('div.keyValueLine');
	var hasEmptyRow = false;

	// Convert rows to a data object

	for (var idx = 0; idx < elRows.length; idx++)
	{
		var elRow = elRows[idx];

		var valFrom = elRow.querySelectorAll(".keyValueFrom")[0].value;
		var valTo   = elRow.querySelectorAll(".keyValueTo")[0].value;

		// If the From value is empty I may have to delete this row
		if (valFrom === '')
		{
			if (idx === (elRows.length - 1))
			{
				// This is the last empty row. Do not remove and set the flag of having a last empty row.
				hasEmptyRow = true;

				continue;
			}


			// This is an empty From in a row other than the last. Remove it.
			elRow.parentNode.removeChild(elRow);

			continue;
		}

		data[valFrom] = valTo;
		strFrom += "\n" + valFrom;
		strTo += "\n" + valTo;
	}

	// If I don't have a last empty row, create one
	if (!hasEmptyRow)
	{
		replacements.editor.renderRow(elContainer, "", "");
	}

	// Store the key-value information as a data property
	akeeba.System.data.set(elContainer, "keyValueData", data);

	var elFrom = document.getElementById('replaceFrom');
	var elTo   = document.getElementById('replaceTo');

	elFrom.value = strFrom.replace(/^\s+/g, "");
	elTo.value   = strTo.replace(/^\s+/g, "");
};

/**
 * Displays the Javascript powered key-value editor
 */
replacements.showEditor = function () {
	var from            = document.getElementById('replaceFrom').value.split("\n");
	var to              = document.getElementById('replaceTo').value.split("\n");
	var extractedValues = {};

	for (var i = 0; i < Math.min(from.length, to.length); i++)
	{
		extractedValues[from[i]] = to[i];
	}

	var editorContainer   = document.getElementById('keyValueEditor');
	var textareaContainer = document.getElementById('textBoxEditor');

	editorContainer.style.display   = "block";
	textareaContainer.style.display = "none";
	replacements.editor.render('keyValueContainer', extractedValues);
};

akeeba.System.documentReady(function () {
	akeebaAjaxWP = new akeebaAjaxConnector('index.php');

	// Hijack the Next button
	akeeba.System.addEventListener('btnNext', 'click', function (e) {
		setTimeout(function () {
			replacements.start();
		}, 100);

		return false;
	});

	akeeba.System.addEventListener('showAdvanced', 'click', function () {
		document.getElementById('showAdvanced').style.display    = 'none';
		document.getElementById('replaceThrottle').style.display = 'block';
	});
});
