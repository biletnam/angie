<?php
/**
 * ANGIE - The site restoration script for backup archives created by Akeeba Backup and Akeeba Solo
 *
 * @package   angie
 * @copyright Copyright (c)2009-2019 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   http://www.gnu.org/copyleft/gpl.html GNU/GPL v3 or later
 */

defined('_AKEEBA') or die();

class AngieModelWordpressFinalise extends AngieModelBaseFinalise
{
	public function updatehtaccess()
	{
		// Get the .htaccess file to replace. If there is no file to replace we have nothing to do and can return early.
		$fileName = $this->getHtaccessFilePathToChange();

		if (is_null($fileName))
		{
			return true;
		}

		// Load the .htaccess in memory. If it's not readable return early and indicate a failure.
		$contents = @file_get_contents($fileName);

		if ($contents === false)
		{
			return false;
		}

		// Explode its lines
		$lines    = explode("\n", $contents);
		$contents = '';

		/**
		 * WordPress has two different and confusing URLs in its configuration, the Home Address (homeurl) and the
		 * WordPress Address (siteurl). These are counter-intuitive names which cause a massive amount of headaches so
		 * I'll document them here.
		 *
		 * homeurl :: the URL you want your visitors to type in to get to your Homepage.
		 * siteurl ::  the location of your core WordPress files.
		 *
		 * siteurl is typically the same as homeurl UNLESS you have the "site in subdirectory but accessed through
		 * domain root" monstrosity. In this case homeurl would be https://www.example.com but siteurl would be
		 * https://www.example.com/someotherdirectory.
		 *
		 * If you are completely insane you might have homeurl https://www.example.com/something and siteurl set to
		 * https://www.example.com/something/foobar.
		 *
		 * We have to deal with all these crazy cases.
		 */

		/** @var AngieModelWordpressReplacedata $replaceModel */
		/** @var AngieModelWordpressConfiguration $config */
		$replaceModel = AModel::getAnInstance('Replacedata', 'AngieModel', [], $this->container);
		$config       = AModel::getAnInstance('Configuration', 'AngieModel', [], $this->container);

		// Is this a multisite installation?
		$isMultisite = $replaceModel->isMultisite();

		// Get the URL path (relative to domain root) where the new site is installed
		$newHomeURL    = $config->get('homeurl');
		$newHomeURI    = new AUri($newHomeURL);
		$newHomeFolder = $newHomeURI->getPath();
		$newHomeFolder = trim($newHomeFolder, '/\\');

		// Is this a multisite installation inside a subdirectory?
		$multisiteInSubdirectory = $isMultisite && !empty($newHomeFolder);

		// Get the site's URL
		$newCoreFilesURL    = $config->get('siteurl');
		$newCoreFilesURI    = new AUri($newCoreFilesURL);
		$newCoreFilesFolder = $newCoreFilesURI->getPath();
		$newCoreFilesFolder = trim($newCoreFilesFolder, '/\\');

		// Apply replacements
		$replacements = $replaceModel->getDefaultURLReplacements();
		$replaceFrom  = array_keys($replacements);
		$replaceTo    = array_values($replacements);

		if (!empty($replacements))
		{
			$lines = array_map(function($line) use ($replaceFrom, $replaceTo) {
				return str_replace($replaceFrom, $replaceTo, $line);
			}, $lines);
		}

		// Convert the RewriteBase line
		$lines = array_map(function ($line) use($newCoreFilesFolder) {
			// Fix naughty Windows users' doing
			$line = rtrim($line, "\r");

			// Handle the RewriteBase line
			if (strpos(trim($line), 'RewriteBase ') === 0)
			{
				$leftMostPos = strpos($line, 'RewriteBase');
				$leftMostStuff = substr($line, 0, $leftMostPos);

				$line = "{$leftMostStuff}RewriteBase /$newCoreFilesFolder";

				// If the site is hosted on the domain's root
				if (empty($newCoreFilesFolder))
				{
					$line = "{$leftMostStuff}RewriteBase /";
				}

				return $line;
			}

			return $line;
		}, $lines);

		/**
		 * Handle moving from domain root to a subdomain (see https://codex.wordpress.org/htaccess)
		 *
		 * The thing is that WordPress ships by default with a SEF URL rule that redirects all requests to
		 * /index.php. However, this is NOT right UNLESS your homeurl and siteurl differ. In all other cases this
		 * causes your site to fail loading anything but its front page because there's no index.php in the domain's
		 * web root. This has to be changed to have JUST index.php, not /index.php
		 */
		if ($newCoreFilesURL == $newHomeURL)
		{
			$lines = array_map(function ($line) use($newCoreFilesFolder) {
				if (strpos(trim($line), 'RewriteRule . /index.php') === 0)
				{
					return str_replace('/index.php', 'index.php', $line);
				}

				return $line;
			}, $lines);
		}
		/**
		 * Conversely, when homeurl and siteurl differ on your NEW site (but not on the old one) we might have to
		 * change RewriteRule . index.php to RewriteRule . /index.php, otherwise the site would not load correctly.
		 */
		else
		{
			$lines = array_map(function ($line) use($newCoreFilesFolder) {
				if (strpos(trim($line), 'RewriteRule . index.php') === 0)
				{
					return str_replace('index.php', '/index.php', $line);
				}
			}, $lines);
		}

		// If it's a multisite in a subdirectory we may have to convert some .htaccess rules
		if ($multisiteInSubdirectory)
		{
			$lines = array_map(function ($line) {
				$trimLine = trim($line);

				if (strpos($trimLine, 'RewriteRule ^wp-admin$ wp-admin/') === 0)
				{
					$line = str_replace('RewriteRule ^wp-admin$ wp-admin/', 'RewriteRule ^([_0-9a-zA-Z-]+/)?wp-admin$ $1wp-admin/', $line);

					return $line;
				}

				if (strpos($trimLine, 'RewriteRule ^(wp-(content|admin|includes).*) $1') === 0)
				{
					$line = str_replace('RewriteRule ^(wp-(content|admin|includes).*) $1', 'RewriteRule ^([_0-9a-zA-Z-]+/)?(wp-(content|admin|includes).*) $2', $line);

					return $line;
				}

				if (strpos($trimLine, 'RewriteRule ^(.*\.php)$ wp/$1') === 0)
				{
					$line = str_replace('RewriteRule ^(.*\.php)$ wp/$1', 'RewriteRule ^([_0-9a-zA-Z-]+/)?(.*\.php)$ $2', $line);

					return $line;
				}

				return $line;
			}, $lines);
		}

		// Write the new .htaccess. Indicate failure if this is not possible.
		if (!file_put_contents(APATH_ROOT . '/.htaccess', implode("\n", $lines)))
		{
			return false;
		}

		// If the homeurl and siteurl don't match, copy the .htaccess file and index.php in the correct directory
		if ($newCoreFilesURL != $newHomeURL)
		{
			return $this->handleCoreFilesInSubdirectory($newCoreFilesFolder, $newHomeFolder);
		}

		return true;
	}

	/**
	 * Depending on the restoration method (Kickstart, integrated, UNiTE, manual extraction etc) we may have either a
	 * .htaccess file or a htaccess.bak file we need to modify. This method picks the correct one and returns its
	 * full path.
	 *
	 * @return  null|string  The path of the file, null if nothing was found
	 */
	protected function getHtaccessFilePathToChange()
	{
		// Let's build the stack of possible files
		$files = [
			APATH_ROOT . '/.htaccess',
			APATH_ROOT . '/htaccess.bak',
		];

		// Do I want to give more importance to .bak file first?
		if ($this->input->getInt('bak_first', 0))
		{
			rsort($files);
		}

		$fileName = null;

		foreach ($files as $file)
		{
			// Did I find what I'm looking for?
			if (file_exists($file))
			{
				$fileName = $file;

				break;
			}
		}

		return $fileName;
	}

	/**
	 * Some WordPress sites have their core files in a different subdirectory than the one used to access the site.
	 *
	 * For example:
	 *
	 * Home Address (homeurl)      -- typed by visitors to access your site -- https://www.example.com/foobar
	 * WordPress Address (siteurl) -- where WordPress core files are stored -- https://www.example.com/foobar/wordpress_dir
	 *
	 * In these cases we are restoring into the <webRoot>/foobar/wordpress_dir folder and our .htaccess file is there as
	 * well. However, we need to copy the .htaccess in <webRoot>foobar, copy the index.php in <webRoot>foobar and modify
	 * the index.php to load stuff from the <webRoot>/foobar/wordpress_dir subdirectory.
	 *
	 * This method handles these necessary changes.
	 *
	 * @param   string  $newCoreFilesFolder  The relative path where WordPress core files are stored
	 * @param   string  $newHomeFolder       The relative path used to access the site
	 *
	 * @return  bool  False if an error occurred, e.g. an unwriteable file
	 */
	protected function handleCoreFilesInSubdirectory($newCoreFilesFolder, $newHomeFolder)
	{
		if (strpos($newCoreFilesFolder, $newHomeFolder) !== 0)
		{
			// I have no clue where to put the files so I'll do nothing at all :s
			return true;
		}

		// $newHomeFolder is WITHOUT /wordpress_dir (/foobar); $path is the one WITH /wordpress_dir (/foobar/wordpress_dir)
		$newHomeFolder        = ltrim($newHomeFolder, '/\\');
		$newCoreFilesFolder   = ltrim($newCoreFilesFolder, '/\\');
		$homeFolderParts      = explode('/', $newHomeFolder);
		$coreFilesFolderParts = explode('/', $newCoreFilesFolder);

		$numHomeParts         = count($homeFolderParts);
		$coreFilesFolderParts = array_slice($coreFilesFolderParts, $numHomeParts);

		// Relative path from HOME to SITE (WP) root
		$relativeCoreFilesPath = implode('/', $coreFilesFolderParts);

		// How many directories above the root (where we are restoring) is our site's root
		$levelsUp = count($coreFilesFolderParts);

		// Determine the path where the index.php and .htaccess files will be written to
		$targetPath = APATH_ROOT . str_repeat('/..', $levelsUp);
		$targetPath = realpath($targetPath) ? realpath($targetPath) : $targetPath;

		// Copy the .htaccess and index.php files
		if (!@copy(APATH_ROOT . '/.htaccess', $targetPath . '/.htaccess'))
		{
			return false;
		}

		if (!@copy(APATH_ROOT . '/index.php', $targetPath . '/index.php'))
		{
			return false;
		}

		// Edit the index.php file
		$fileName     = $targetPath . '/index.php';
		$fileContents = @file($fileName);

		if (empty($fileContents))
		{
			return false;
		}

		foreach ($fileContents as $index => $line)
		{
			$line = trim($line);

			if (strstr($line, 'wp-blog-header.php') && (strpos($line, 'require') === 0))
			{
				$line = "require( dirname( __FILE__ ) . '/$relativeCoreFilesPath/wp-blog-header.php' );";
			}

			$fileContents[$index] = $line;
		}

		$fileContents = implode("\n", $fileContents);
		@file_put_contents($fileName, $fileContents);

		return true;
	}
}
