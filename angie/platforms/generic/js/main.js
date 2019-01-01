/*
 * ANGIE - The site restoration script for backup archives created by Akeeba Backup and Akeeba Solo
 *
 * @package   angie
 * @copyright Copyright (c)2009-2019 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   http://www.gnu.org/copyleft/gpl.html GNU/GPL v3 or later
 */

/**
 * Initialization. Runs as soon as the DOM is ready.
 */
akeeba.System.documentReady(function ()
{
    // Disable the mobile navigation
    document.getElementById('mobileMenuToggler').setAttribute('disabled', 'disabled');

    // Start the AJAX calls in a way which doesn't make some older browsers choke
    setTimeout(mainGetPage, 500);
});

/**
 * Ask the server to render and send the main page area content.
 */
function mainGetPage() {
    request_data = {
        'view': 'main',
        'task': 'main',
        'layout': 'init',
        'format': 'raw'
    };
    akeebaAjax.callRaw(request_data, mainGotPage, mainGotPage);
}

/**
 * Got the rendered main page area. Let's show it to the user and activate the UI.
 *
 * @param html
 */
function mainGotPage(html) {
    // Put the main content on the page
    $('#mainContent').html(html);

    // Re-enable the mobile navigation (we had disabled it at the top)
    document.getElementById('mobileMenuToggler').removeAttribute('disabled');
}

/**
 * Open a modal dialog to display the README.html file
 */
function mainOpenReadme()
{
    akeeba.System.modalDialog = akeeba.Modal.open({
        iframe: 'README.html',
        width:   '80%',
        height:  '320'
    });
}