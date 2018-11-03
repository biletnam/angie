/**
 * @package angi4j
 * @copyright Copyright (c)2009-2018 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @author Nicholas K. Dionysopoulos - http://www.dionysopoulos.me
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL v3 or later
 */

/**
 * Initialisation of the page
 */
akeeba.System.documentReady(function () {
	akeeba.System.addEventListener(document.getElementById('removeInstallation'), 'click', function (e) {
		finaliseRemoveInstallation();

		return false;
	});
});

/**
 * Try removing the installation directory using an AJAX request
 *
 * @returns void
 */
function finaliseRemoveInstallation()
{
	// Set up the request
	var data = {
		'view':   'finalise',
		'task':   'cleanup',
		'format': 'json'
	};

	// Start the restoration
	akeebaAjax.callJSON(data, finaliseParseMessage, finaliseError);
}

/**
 * Parse the installation directory cleanup message
 *
 * @param    {string|boolean}  msg  The message received from the server
 *
 * @returns void
 */
function finaliseParseMessage(msg)
{
	if (msg === true)
	{
		open({
			inherit: '#success-dialog',
			width:   '80%'
		});
	}
	else
	{
		open({
			inherit: '#error-dialog',
			width:   '80%'
		});
	}
}

/**
 * Handles error messages during the installation directory cleanup
 *
 * @param   {string}  error_message
 *
 * @returns void
 */
function finaliseError(error_message)
{
	open({
		inherit: '#error-dialog',
		width:   '80%'
	});
}
