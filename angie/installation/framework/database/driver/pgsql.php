<?php
/**
 * ANGIE - The site restoration script for backup archives created by Akeeba Backup and Akeeba Solo
 *
 * @package   angie
 * @copyright Copyright (c)2009-2019 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   http://www.gnu.org/copyleft/gpl.html GNU/GPL v3 or later
 */

defined('_AKEEBA') or die();

/**
 * This file may contain code from the Joomla! Platform, Copyright (c) 2005 -
 * 2012 Open Source Matters, Inc. This file is NOT part of the Joomla! Platform.
 * It is derivative work and clearly marked as such as per the provisions of the
 * GNU General Public License.
 */

/**
 * PostgreSQL database driver through PDO
 */
class ADatabaseDriverPgsql extends ADatabaseDriverPostgresql
{
	/** @var \PDO The db connection resource */
	protected $connection = null;

	/** @var \PDOStatement The database connection cursor from the last query. */
	protected $cursor;

	/**
	 * @var    string  The database technology family supported, e.g. mysql, mssql
	 */
	public static $dbtech = 'pgsql';

	/** @var array Driver options for PDO */
	protected $driverOptions = array();

	/**
	 * The database driver name
	 *
	 * @var string
	 */
	public $name = 'pgsql';

	/**
	 * Database object constructor
	 *
	 * @param   array $options List of options used to configure the connection
	 *
	 */
	public function __construct($options)
	{
		// Get some basic values from the options.
		$options['host']     = (isset($options['host'])) ? $options['host'] : 'localhost';
		$options['user']     = (isset($options['user'])) ? $options['user'] : 'root';
		$options['password'] = (isset($options['password'])) ? $options['password'] : '';
		$options['database'] = (isset($options['database'])) ? $options['database'] : '';
		$options['port']     = isset($options['port']) ? $options['port'] : 5432;

		// Finalize initialisation.
		parent::__construct($options);
	}

	/**
	 * Destructor.
	 *
	 */
	public function __destruct()
	{
		if (is_object($this->connection))
		{
			$this->disconnect();
		}
	}

	/**
	 * Connects to the database if needed.
	 *
	 * @return  void  Returns void if the database connected successfully.
	 *
	 * @throws  \RuntimeException
	 */
	public function connect()
	{
		if ($this->connected())
		{
			return;
		}
		else
		{
			$this->disconnect();
		}

		// Make sure the postgresql extension for PHP is installed and enabled.
		if (!self::isSupported())
		{
			throw new \RuntimeException('PDO extension is not available.');
		}

		$format = 'pgsql:host=#HOST#;port=#PORT#;dbname=#DBNAME#';
		$replace = array('#HOST#', '#PORT#', '#DBNAME#');
		$with = array($this->options['host'], $this->options['port'], $this->options['database']);

		// Create the connection string:
		$connectionString = str_replace($replace, $with, $format);

		// connect to the server
		try
		{
			$this->connection = new \PDO(
				$connectionString,
				$this->options['user'],
				$this->options['password'],
				$this->driverOptions
			);
		}
		catch (\PDOException $e)
		{
			throw new RuntimeException('Could not connect to PostgreSQL via PDO: ' . $e->getMessage(), 2);
		}

		try
		{
			$this->connection->exec('SET standard_conforming_strings=off;');
			$this->connection->exec('SET escape_string_warning=off;');
		}
		catch (\Exception $e)
		{
		}

		$this->connection->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
		$this->connection->setAttribute(\PDO::ATTR_EMULATE_PREPARES, true);

		if ($this->options['select'] && !empty($this->options['database']))
		{
			$this->select($this->options['database']);
		}

		$this->freeResult();
	}

	/**
	 * Disconnects the database.
	 *
	 * @return  void
	 */
	public function disconnect()
	{
		if (is_object($this->cursor))
		{
			$this->cursor->closeCursor();
		}

		$this->connection = null;
	}

	/**
	 * Method to escape a string for usage in an SQL statement.
	 *
	 * @param   string  $text  The string to be escaped.
	 * @param   boolean $extra Optional parameter to provide extra escaping.
	 *
	 * @return  string  The escaped string.
	 */
	public function escape($text, $extra = false)
	{
		if (is_int($text) || is_float($text))
		{
			return $text;
		}

		$result = substr($this->connection->quote($text), 1, -1);

		if ($extra)
		{
			$result = addcslashes($result, '%_');
		}

		return $result;
	}

	/**
	 * Determines if the connection to the server is active.
	 *
	 * @return    boolean
	 */
	public function connected()
	{
		if (!is_object($this->connection))
		{
			return false;
		}

		return true;
	}

	/**
	 * Method to get the auto-incremented value from the last INSERT statement.
	 *
	 * @return  integer  The value of the auto-increment field from the last inserted row.
	 */
	public function insertid()
	{
		$this->connect();

		// Error suppress this to prevent PDO warning us that the driver doesn't support this operation.
		return @$this->connection->lastInsertId();
	}

	/**
	 * Execute the SQL statement.
	 *
	 * @return  mixed  A database cursor resource on success, boolean false on failure.
	 *
	 * @throws  \RuntimeException
	 */
	public function execute()
	{
		static $isReconnecting = false;

		if (!is_object($this->connection))
		{
			$this->connect();
		}

		$this->freeResult();

		// Take a local copy so that we don't modify the original query and cause issues later
		$query = $this->replacePrefix((string)$this->sql);

		if ($this->limit > 0)
		{
			$query .= ' LIMIT ' . $this->limit;
		}

		if ($this->offset > 0)
		{
			$query .= ' OFFSET ' . $this->offset;
		}

		// Increment the query counter.
		$this->count++;

		// If debugging is enabled then let's log the query.
		if ($this->debug)
		{
			// Add the query to the object queue.
			$this->log[] = $query;
		}

		// Reset the error values.
		$this->errorNum = 0;
		$this->errorMsg = '';

		// Execute the query. Error suppression is used here to prevent warnings/notices that the connection has been lost.
		try
		{
			$this->cursor = $this->connection->query($query);
		}
		catch (\Exception $e)
		{
		}

		// If an error occurred handle it.
		if (!$this->cursor)
		{
			$errorInfo = $this->connection->errorInfo();
			$this->errorNum = $errorInfo[1];
			$this->errorMsg = $errorInfo[2] . ' SQL=' . $query;

			// Check if the server was disconnected.
			if (!$this->connected() && !$isReconnecting)
			{
				$isReconnecting = true;

				try
				{
					// Attempt to reconnect.
					$this->connection = null;
					$this->connect();
				}
					// If connect fails, ignore that exception and throw the normal exception.
				catch (\RuntimeException $e)
				{
					throw new \RuntimeException($this->errorMsg, $this->errorNum);
				}

				// Since we were able to reconnect, run the query again.
				$result = $this->execute();
				$isReconnecting = false;

				return $result;
			}
			// The server was not disconnected.
			else
			{
				throw new \RuntimeException($this->errorMsg, $this->errorNum);
			}
		}

		return $this->cursor;
	}

	/**
	 * Selects the database for use
	 *
	 * @param   string $database Database name to select.
	 *
	 * @return  boolean  Always true
	 */
	public function select($database)
	{
		return true;
	}

	/**
	 * Set the connection to use UTF-8 character encoding.
	 *
	 * @return  boolean  True on success.
	 */
	public function setUTF()
	{
		return true;
	}

	/**
	 * This function return a field value as a prepared string to be used in a SQL statement.
	 *
	 * @param   array  $columns     Array of table's column returned by ::getTableColumns.
	 * @param   string $field_name  The table field's name.
	 * @param   string $field_value The variable value to quote and return.
	 *
	 * @return  string  The quoted string.
	 */
	protected function sqlValue($columns, $field_name, $field_value)
	{
		switch ($columns[$field_name])
		{
			case 'boolean':
				$val = 'NULL';
				if (($field_value == 't') || ($field_value == '1'))
				{
					$val = 'TRUE';
				}
				elseif (($field_value == 'f') || ($field_value == ''))
				{
					$val = 'FALSE';
				}
				break;

			case 'bigint':
			case 'bigserial':
			case 'integer':
			case 'money':
			case 'numeric':
			case 'real':
			case 'smallint':
			case 'serial':
			case 'numeric,':
				$val = strlen($field_value) == 0 ? 'NULL' : $field_value;
				break;

			case 'date':
			case 'timestamp without time zone':
				if (empty($field_value))
				{
					$field_value = $this->getNullDate();
				}

				$val = $this->quote($field_value);

				break;

			default:
				$val = $this->quote($field_value);
				break;
		}

		return $val;
	}

	/**
	 * Method to commit a transaction.
	 *
	 * @return  void
	 *
	 * @throws  \RuntimeException
	 */
	public function transactionCommit()
	{
		$this->connection->commit();
	}

	/**
	 * Method to roll back a transaction.
	 *
	 * @param   string $toSavepoint If present rollback transaction to this savepoint
	 *
	 * @return  void
	 *
	 * @throws  \RuntimeException
	 */
	public function transactionRollback($toSavepoint = null)
	{
		$this->connection->rollBack();
	}

	/**
	 * Method to initialize a transaction.
	 *
	 * @return  void
	 *
	 * @throws  \RuntimeException
	 */
	public function transactionStart()
	{
		$this->connection->beginTransaction();
	}

	/**
	 * Test to see if the PostgreSQL connector is available.
	 *
	 * @return  boolean  True on success, false otherwise.
	 */
	public static function isSupported()
	{
		return defined('PDO::ATTR_DRIVER_NAME');
	}

	/**
	 * Method to fetch a row from the result set cursor as an associative array.
	 *
	 * @param   mixed $cursor The optional result set cursor from which to fetch the row.
	 *
	 * @return  mixed  Either the next row from the result set or false if there are no more rows.
	 *
	 * @since   1.0
	 */
	public function fetchAssoc($cursor = null)
	{
		if (!empty($cursor) && $cursor instanceof \PDOStatement)
		{
			return $cursor->fetch(\PDO::FETCH_ASSOC);
		}

		return false;
	}

	/**
	 * Method to free up the memory used for the result set.
	 *
	 * @param   mixed $cursor The optional result set cursor from which to fetch the row.
	 *
	 * @return  void
	 */
	public function freeResult($cursor = null)
	{
		if ($cursor instanceof \PDOStatement)
		{
			$cursor->closeCursor();
			$cursor = null;
		}

		if ($this->cursor instanceof \PDOStatement)
		{
			$this->cursor->closeCursor();
			$this->cursor = null;
		}
	}


	/**
	 * PDO does not support serialize
	 *
	 * @return  array
	 *
	 * @throws ReflectionException
	 */
	public function __sleep()
	{
		$serializedProperties = [];

		$reflect = new \ReflectionClass($this);

		// Get properties of the current class
		$properties = $reflect->getProperties();

		foreach ($properties as $property)
		{
			// Do not serialize properties that are \PDO
			if ($property->isStatic() == false && !($this->{$property->name} instanceof \PDO))
			{
				array_push($serializedProperties, $property->name);
			}
		}

		return $serializedProperties;
	}

	/**
	 * Wake up after serialization
	 *
	 * @return  void
	 */
	public function __wakeup()
	{
		// Get connection back
		$this->__construct($this->options);
	}
}
