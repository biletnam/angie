/**
 * @package angi4j
 * @copyright Copyright (c)2009-2019 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @author Nicholas K. Dionysopoulos - http://www.dionysopoulos.me
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL v3 or later
 */

var setupSuperUsers     = {};
var setupDefaultTmpDir  = '';
var setupDefaultLogsDir = '';

/**
 * Toggles the help text on the page.
 *
 * By default we hide the help text underneath each field because it makes the page look busy. When the user clicks on
 * the Show / hide help we make it appear. Click again, it disappears again.
 */
function toggleHelp()
{
	var elHelpTextAll = document.querySelectorAll('.akeeba-help-text');

	for (var i = 0; i < elHelpTextAll.length; i++)
	{
		var elHelp = elHelpTextAll[i];

		if (elHelp.style.display === 'none')
		{
			elHelp.style.display = 'block';

			continue;
		}

		elHelp.style.display = 'none';
	}
}

/**
 * Initialisation of the page
 */
akeeba.System.documentReady(function () {
	// Hook for the Next button
	akeeba.System.addEventListener('btnNext', 'click', function (e) {
		if (akeeba.System.hasClass(document.getElementById('btnNext'), 'akeeba-btn--teal--multisite'))
		{
			return false;
		}

		document.forms.setupForm.submit();

		return false;
	});
});

function setupRunRestoration(key)
{
	// Manipulate the form's hidden fields
	document.forms.setupForm.task.value    = 'applyjson';
	document.forms.setupForm.format.value  = 'json';
	document.forms.setupForm.substep.value = key;

	// Get an object with the form values
	var data     = {};
	var formData = new FormData(document.querySelectorAll('form')[0]);
	formData.forEach(function (value, key) {
		data[key] = value;
	});

	console.debug(formData);
	console.debug(data);

	// Set up the modal dialog
	document.getElementById('restoration-progress').style.display   = 'block';
	document.getElementById('restoration-success').style.display    = 'none';
	document.getElementById('restoration-error').style.display      = 'none';
	document.getElementById('restoration-progress-bar').style.width = '0%';

	// Open the restoration's modal dialog
	akeeba.Modal.open({
		inherit: '#restoration-dialog',
		width:   '80%',
		lock:    true
	});

	// Start the restoration
	setTimeout(function () {
		akeebaAjax.callJSON(data, setupParseRestoration, setupErrorRestoration);
	}, 1000);
}

/**
 * Handles a restoration error message
 */
function setupErrorRestoration(error_message, config)
{
	var elConfig = document.getElementById('restoration-config');
	var elNext   = document.getElementById('nextStep');

	document.getElementById('akeeba-modal-close').style.visibility = 'visible';
	document.getElementById('restoration-progress').style.display  = 'none';
	document.getElementById('restoration-success').style.display   = 'none';
	document.getElementById('restoration-error').style.display     = 'block';
	document.getElementById('restoration-lbl-error').innerHTML     = error_message;
	elConfig.style.display                                         = 'none';
	elNext.style.display                                           = 'none';

	if (config)
	{

		document.getElementById('restoration-lbl-error').style.height = 'auto';
		elConfig.innerHTML                                            = config;
		elConfig.style.display                                        = 'block';
		elNext.style.display                                          = 'block';
	}
}

/**
 * Parses the restoration result message, updates the restoration progress bar
 * and steps through the restoration as necessary.
 */
function setupParseRestoration(msg)
{
	if (msg.error !== '')
	{
		// An error occurred
		setupErrorRestoration(msg.error, msg.showconfig);

		return;
	}

	if (msg.done == 1)
	{
		// The restoration is complete
		document.getElementById('restoration-progress-bar').style.width = '100%';

		setTimeout(function () {
			document.getElementById('akeeba-modal-close').style.visibility  = 'visible';
			document.getElementById('restoration-progress').style.display   = 'none';
			document.getElementById('restoration-success').style.display    = 'block';
			document.getElementById('restoration-error').style.display      = 'none';
			document.getElementById('restoration-progress-bar').style.width = '0%';
		}, 500);
	}
}

function setupBtnSuccessClick(e)
{
	window.location = document.getElementById('btnSkip').href;
}

function setupSuperUserChange(e)
{
	var saID   = document.getElementById('superuserid').value;
	var params = {};

	for (var idx = 0; idx < setupSuperUsers.length; idx++)
	{
		var sa = setupSuperUsers[idx];

		if (sa.id === saID)
		{
			params = sa;

			break;
		}
	}

	document.getElementById('superuseremail').value          = '';
	document.getElementById('superuserpassword').value       = '';
	document.getElementById('superuserpasswordrepeat').value = '';
	document.getElementById('superuseremail').value          = params.email;
	document.getElementById('hash').value                    = '';
}

function setupOverrideDirectories()
{
	document.getElementById('tmppath').value  = setupDefaultTmpDir;
	document.getElementById('logspath').value = setupDefaultLogsDir;
}
