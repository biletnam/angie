/**
 * @package angi4j
 * @copyright Copyright (c)2009-2018 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @author Nicholas K. Dionysopoulos - http://www.dionysopoulos.me
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL v3 or later
 */

var setupSuperUsers = {};
var setupDefaultTmpDir = '';
var setupDefaultLogsDir = '';

/**
 * Initialisation of the page
 */
$(document).ready(function(){
	// Hook for the Next button
	akeeba.System.addEventListener('btnNext', 'click', function (e) {
		document.forms.setupForm.submit();
		return false;
	});

	// Hook for the “Override tmp and log paths” checkbox
	akeeba.System.addEventListener('usesitedirs', 'click', function (e) {
		setupOverrideDirectories();
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

function openFTPBrowser()
{
	var hostname  = document.getElementById('ftphost').value;
	var port      = document.getElementById('ftpport').value;
	var username  = document.getElementById('ftpuser').value;
	var password  = document.getElementById('ftppass').value;
	var directory = document.getElementById('fptdir').value;

	if ((port <= 0) || (port >= 65536))
	{
		port = 21;
	}

	var url = 'index.php?view=ftpbrowser&tmpl=component'
		+ '&hostname=' + encodeURIComponent(hostname)
		+ '&port=' + encodeURIComponent(port)
		+ '&username=' + encodeURIComponent(username)
		+ '&password=' + encodeURIComponent(password)
		+ '&directory=' + encodeURIComponent(directory);

	document.getElementById('browseFrame').src = url;

	akeeba.System.data.set(document.getElementById('browseModal'), 'modal', akeeba.Modal.open({
		inherit: '#browseModal',
		width:   '80%'
	}));
}

function useFTPDirectory(path)
{
	document.getElementById('ftpdir').value = path;

	akeeba.System.data.get(document.getElementById('browseModal'), 'modal').close();
}

function setupOverrideDirectories()
{
	document.getElementById('tmppath').value  = setupDefaultTmpDir;
	document.getElementById('logspath').value = setupDefaultLogsDir;
}