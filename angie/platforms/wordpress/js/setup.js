/**
 * @package angi4j
 * @copyright Copyright (c)2009-2018 Nicholas K. Dionysopoulos / Akeeba Ltd
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
		document.forms.setupForm.submit();
		return false;
	});
});

/**
 * Runs whenever the Super User selection changes, displaying the correct SU's parameters on the page
 *
 * @param e
 */
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
}
