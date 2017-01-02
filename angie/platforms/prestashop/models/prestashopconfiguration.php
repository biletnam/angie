<?php
/**
 * @package angi4j
 * @copyright Copyright (C) 2009-2017 Nicholas K. Dionysopoulos. All rights reserved.
 * @author Nicholas K. Dionysopoulos - http://www.dionysopoulos.me
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL v3 or later
 */

defined('_AKEEBA') or die();

class AngieModelPrestashopConfiguration extends AngieModelBaseConfiguration
{
	public function __construct($config = array(), AContainer $container = null)
	{
		// Call the parent constructor
		parent::__construct($config, $container);

		// Load the configuration variables from the session or the default configuration shipped with ANGIE
		$this->configvars = $this->container->session->get('configuration.variables');

		if (empty($this->configvars))
		{
			$this->configvars = $this->getDefaultConfig();
			$realConfig = $this->loadFromFile(APATH_CONFIGURATION . '/config/settings.inc.php');
			$this->configvars = array_merge($this->configvars, $realConfig);

			if (!empty($this->configvars))
			{
				$this->saveToSession();
			}
		}
	}

	/**
     * Returns an associative array with default settings
     *
     * @return array
     */
    public function getDefaultConfig()
    {
        // MySQL settings
        $config['dbname']       = '';
        $config['dbuser']       = '';
        $config['dbpass']       = '';
        $config['dbhost']       = '';
        $config['dbprefix']     = '';

        // Other
        $config['sitename'] = '';

        return $config;
    }

    /**
     * Loads the configuration information from a PHP file
     *
     * @param   string $file The full path to the file
     *
     * @return array
     */
    public function loadFromFile($file)
    {
        $config          = array();

        // PrestaShop configuration file is a simple PHP file, we can't just include it because we
        // could have "funny" surprise
        // The only option is to parse each line and extract the value
        $contents = file_get_contents($file);

        //Ok, now let's start analyzing
        $lines = explode("\n", $contents);

        foreach($lines as $line)
        {
            $line = trim($line);
            $matches = array();

            // Skip commented lines. However it will get the line between a multiline comment, but that's not a problem
            if(strpos($line, '#') === 0 || strpos($line, '//') === 0 || strpos($line, '/*') === 0)
            {
                // skip it
            }
            elseif(strpos($line, 'define(') !== false)
            {
                preg_match('#define\(["\'](.*?)["\']\,\s["\'](.*?)["\']#', $line, $matches);

                if(isset($matches[1]))
                {
                    $key = $matches[1];

                    switch(strtoupper($key))
                    {
                        case '_DB_NAME_' :
                            $config['dbname'] = $matches[2];
                            break;
                        case '_DB_USER_':
                            $config['dbuser'] = $matches[2];
                            break;
                        case '_DB_PASSWD_':
                            $config['dbpass'] = $matches[2];
                            break;
                        case '_DB_SERVER_':
                            $config['dbhost'] = $matches[2];
                            break;
                        case '_DB_PREFIX_':
                            $config['dbprefix'] = $matches[2];
                            break;
                        case '_COOKIE_KEY_':
                            $config['cookiekey'] = $matches[2];
                            break;
                        default:
                            // Do nothing, it's a variable we're not interested in
                            break;
                    }
                }
            }
        }

        $config['sitename'] = $this->getStoreName();

        return $config;
    }

    /**
     * Creates the string that will be put inside the new configuration file.
     * This is a separate function so we can show the content if we're unable to write to the filesystem
     * and ask the user to manually do that.
     */
    public function getFileContents($file = null)
    {
        if(!$file)
        {
            $file = APATH_ROOT.'/config/settings.inc.php';
        }

        $new_config = '';
        $old_config = file_get_contents($file);

        $lines = explode("\n", $old_config);

        foreach($lines as $line)
        {
            $line    = trim($line);
            $matches = array();

            // Skip commented lines. However it will get the line between a multiline comment, but that's not a problem
            if(strpos($line, '#') === 0 || strpos($line, '//') === 0 || strpos($line, '/*') === 0)
            {
                // simply do nothing, we will add the line later
            }
            elseif(strpos($line, 'define(') !== false)
            {
                preg_match('#define\(["\'](.*?)["\']\,#', $line, $matches);

                if(isset($matches[1]))
                {
                    $key = $matches[1];

                    switch(strtoupper($key))
                    {
                        case '_DB_NAME_' :
                            $value = $this->get('dbname');
                            $line = "define('".$key."', '".$value."');";
                            break;
                        case '_DB_USER_':
                            $value = $this->get('dbuser');
                            $line = "define('".$key."', '".$value."');";
                            break;
                        case '_DB_PASSWD_':
                            $value = $this->get('dbpass');
							$value = addcslashes($value, "'\\");
                            $line = "define('".$key."', '".$value."');";
                            break;
                        case '_DB_SERVER_':
                            $value = $this->get('dbhost');
                            $line = "define('".$key."', '".$value."');";
                            break;
                        case '_DB_PREFIX_':
                            $value = $this->get('dbprefix');
                            $line = "define('".$key."', '".$value."');";
                            break;
                        default:
                            // Do nothing, it's a variable we're not interested in
                            break;
                    }
                }
            }

            $new_config .= $line."\n";
        }

        return $new_config;
    }

    /**
     * Writes the new config params inside the wp-config file and the database.
     *
     * @param   string  $file
     *
     * @return bool
     */
    public function writeConfig($file)
    {
        // First of all I'll save the options stored inside the db. In this way, even if
        // the configuration file write fails, the user has only to manually update the
        // config file and he's ready to go.

        $this->updateStore();

        $new_config = $this->getFileContents($file);

        if(!file_put_contents($file, $new_config))
        {
            return false;
        }

        $new_htaccess = $this->getHtaccessContents();

        if(!file_put_contents(APATH_ROOT.'/htaccess.bak', $new_htaccess))
        {
            return false;
        }

        return true;
    }


    public function getHtaccessContents()
    {
        $new_htaccess = '';
        $old_htaccess = file_get_contents(APATH_ROOT.'/htaccess.bak');

        $site = str_replace('https://', '', $this->get('siteurl'));
        $site = str_replace('http://', '', $site);
        list($root, $folder) = explode('/', $site, 2);

        $root   = trim($root, '/');

        if($folder)
        {
            $folder = '/'.trim($folder, '/').'/';
        }
        else
        {
            $folder = '/';
        }

        $lines = explode("\n", $old_htaccess);

        foreach($lines as $line)
        {
            $line    = trim($line);
            $matches = array();

            // Skip commented lines
            if(strpos($line, '#') === 0)
            {
                // simply do nothing, we will add the line later
            }
            else
            {
                // Change server host
                if(preg_match('#\{HTTP_HOST\} \^(.*?)\$#', $line, $matches))
                {
                    $line = str_replace($matches[1], $root, $line);
                }
                // Folder
                elseif(preg_match('#\[E=REWRITEBASE:(.*?)\]#', $line, $matches))
                {
                    $line = str_replace($matches[1], $folder, $line);
                }
                // 404 error
                elseif(preg_match('#ErrorDocument\s404\s(\/.*?\/)#', $line, $matches))
                {
                    $line = str_replace($matches[1], $folder, $line);
                }
            }

            $new_htaccess .= $line."\n";
        }

        return $new_htaccess;
    }

    private function getStoreName()
    {
        $siteName = '';
        $version  = $this->container->session->get('version');

        /** @var AngieModelDatabase $model */
        $model		 = AModel::getAnInstance('Database', 'AngieModel', array(), $this->container);
        $keys		 = $model->getDatabaseNames();
        $firstDbKey	 = array_shift($keys);

        $connectionVars = $model->getDatabaseInfo($firstDbKey);

        $name = $connectionVars->dbtype;
        $options = array(
            'database'	 => $connectionVars->dbname,
            'select'	 => 1,
            'host'		 => $connectionVars->dbhost,
            'user'		 => $connectionVars->dbuser,
            'password'	 => $connectionVars->dbpass,
            'prefix'	 => $connectionVars->prefix
        );

        $db = ADatabaseFactory::getInstance()->getDriver($name, $options);

        // On Prestashop 1.4 we have different tables and column names
        if(version_compare($version, '1.5', 'lt'))
        {
            $id    = 'id_store';
            $table = '#__store';
        }
        else
        {
            $id    = 'id_shop';
            $table = '#__shop';
        }

        // WARNING!! PrestaShop could be configured to support multiple shops.
        // Of course we should prevent the name changing if we're in such scenario.
        // At the moment there is no control, we will add it later
        try
        {
            $query = $db->getQuery(true)
                        ->select('MIN('.$db->qn($id).')')
                        ->from($db->qn($table));
            $shopid = $db->setQuery($query)->loadResult();

            $query = $db->getQuery(true)
                        ->select($db->qn('name'))
                        ->from($db->qn($table))
                        ->where($db->qn($id).' = '.$shopid);

            $siteName = $db->setQuery($query)->loadResult();
        }
        catch(Exception $e)
        {

        }

        return $siteName;
    }

    private function updateStore()
    {
        $version  = $this->container->session->get('version');

        $name = $this->get('dbtype');
        $options = array(
            'database'	 => $this->get('dbname'),
            'select'	 => 1,
            'host'		 => $this->get('dbhost'),
            'user'		 => $this->get('dbuser'),
            'password'	 => $this->get('dbpass'),
            'prefix'	 => $this->get('dbprefix')
        );

        $db = ADatabaseFactory::getInstance()->getDriver($name, $options);

        if(version_compare($version, '1.5', 'lt'))
        {
            $id    = 'id_store';
            $table = '#__store';
        }
        else
        {
            $id    = 'id_shop';
            $table = '#__shop';
        }

        $query = $db->getQuery(true)
                    ->select('MIN('.$db->qn($id).')')
                    ->from($db->qn($table));
        $shopid = $db->setQuery($query)->loadResult();

        // Update the Shop name. We're just updating the first shop, in the future we'll have to
        // take care of multi-shops, too
        $query = $db->getQuery(true)
                    ->update($db->qn($table))
                    ->set($db->qn('name').' = '.$db->q($this->get('sitename')))
                    ->where($db->qn($id).' = '.$db->q($shopid));
        $db->setQuery($query)->execute();

        // Update shop links. Again, we're updating only the first shop
        $site = str_replace('https://', '', $this->get('siteurl'));
        $site = str_replace('http://', '', $site);
        list($root, $folder) = explode('/', $site, 2);

        $root   = trim($root, '/');

        // The URL table is available under PS 1.5 and later
        if (version_compare($version, '1.5', 'gt'))
        {
            if($folder)
            {
                $folder = '/'.trim($folder, '/').'/';
            }
            else
            {
                $folder = '/';
            }

            $query = $db->getQuery(true)
                        ->update($db->qn('#__shop_url'))
                        ->set($db->qn('domain').' = '.$db->q($root))
                        ->set($db->qn('domain_ssl').' = '.$db->q($root))
                        ->set($db->qn('physical_uri').' = '.$db->q($folder))
                        ->where($db->qn('id_shop').' = '.$db->q($shopid));
            $db->setQuery($query)->execute();
        }

        // Prestashop has a big table that holds all the variables, we have to update it, too
        $query = $db->getQuery(true)
                    ->update($db->qn('#__configuration'))
                    ->set($db->qn('value').' = '.$db->q($root))
                    ->where($db->qn('name').' IN('.$db->q('PS_SHOP_DOMAIN').', '.$db->q('PS_SHOP_DOMAIN_SSL').')');
        $db->setQuery($query)->execute();

        $query = $db->getQuery(true)
                    ->update($db->qn('#__configuration'))
                    ->set($db->qn('value').' = '.$db->q($this->get('sitename')))
                    ->where($db->qn('name').' = '.$db->q('PS_SHOP_NAME'));
        $db->setQuery($query)->execute();
    }
}