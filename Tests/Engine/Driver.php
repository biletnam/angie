<?php
/**
 * @package   angie
 * @copyright Copyright (c)2009-2019 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   http://www.gnu.org/copyleft/gpl.html GNU/GPL v3 or later
 */

namespace Akeeba\ANGIE\Tests\Engine;

class Driver
{

	/** @var string The SQL query string */
	protected $sql = '';

	/** @var int The db server's error number */
	protected $errorNum = 0;

	/** @var string The db server's error string */
	protected $errorMsg = '';

	/** @var string The prefix used in the database, if any */
	protected $table_prefix = '';

	/** @var string The database name */
	protected $database;

	/** @var \mysqli The db conenction resource */
	protected $resource = '';

	/** @var \mysqli_result The internal db cursor */
	protected $cursor = null;

	/** @var int Query's limit */
	protected $limit = 0;

	/** @var int Query's offset */
	protected $offset = 0;

	/** @var string Quote for named objects */
	protected $nameQuote = '';

	/** @var bool Support for UTF-8 */
	protected $utf;

	/**
	 * Database object constructor
	 *
	 * @param    array $options List of options used to configure the connection
	 */
	public function __construct(array $options)
	{
		// Init
		$this->nameQuote = '`';

		$host = array_key_exists('host', $options) ? $options['host'] : 'localhost';
		$port = array_key_exists('port', $options) ? $options['port'] : '';
		$user = array_key_exists('user', $options) ? $options['user'] : '';
		$password = array_key_exists('password', $options) ? $options['password'] : '';
		$socket = null;

		// Figure out if a port is included in the host name
		if (empty($port))
		{
			// Unlike mysql_connect(), mysqli_connect() takes the port and socket
			// as separate arguments. Therefore, we have to extract them from the
			// host string.
			$port = null;
			$socket = null;
			$targetSlot = substr(strstr($host, ":"), 1);

			if (!empty($targetSlot))
			{
				// Get the port number or socket name
				if (is_numeric($targetSlot))
				{
					$port = $targetSlot;
				}
				else
				{
					$socket = $targetSlot;
				}

				// Extract the host name only
				$host = substr($host, 0, strlen($host) - (strlen($targetSlot) + 1));

				// This will take care of the following notation: ":3306"
				if ($host == '')
				{
					$host = 'localhost';
				}
			}
		}

		// finalize initialization
		$prefix = array_key_exists('prefix', $options) ? $options['prefix'] : '';
		$database = array_key_exists('database', $options) ? $options['database'] : '';

		$this->table_prefix = $prefix;
		$this->database = $database;

		// Open the connection
		$this->host = $host;
		$this->user = $user;
		$this->password = $password;
		$this->port = $port;
		$this->socket = $socket;
		$this->database = $database;
		$this->open();
	}

	/**
	 * Database object destructor
	 *
	 * @return bool
	 */
	public function __destruct()
	{
		return $this->close();
	}

	/**
	 * By default, when the object is shutting down, the connection is closed
	 */
	public function _onSerialize()
	{
		$this->close();
	}

	public function __wakeup()
	{
		$this->open();
	}

	public function open()
	{
		if (is_object($this->resource) && ($this->resource instanceof \mysqli))
		{
			if (mysqli_ping($this->resource))
			{
				return;
			}
		}

		// perform a number of fatality checks, then return gracefully
		if (!function_exists('mysqli_connect'))
		{
			$this->errorNum = 1;
			$this->errorMsg = 'The MySQL adapter "mysqli" is not available.';

			return;
		}

		// connect to the server
		if (!($this->resource = @mysqli_connect($this->host, $this->user, $this->password, null, $this->port, $this->socket)))
		{
			$this->errorNum = 2;
			$this->errorMsg = 'Could not connect to MySQL';

			return;
		}

		// Determine utf-8 support
		$this->utf = $this->hasUTF();

		// Set charactersets (needed for MySQL 4.1.2+)
		if ($this->utf)
		{
			$this->setUTF();
		}

		$this->select($this->database);
	}

	public function close()
	{
		$return = false;

		if (is_resource($this->cursor))
		{
			mysqli_free_result($this->cursor);
		}

		if (is_resource($this->resource))
		{
			$return = mysqli_close($this->resource);
		}

		$this->resource = null;

		return $return;
	}

	/**
	 * Select a database for use
	 *
	 * @param    string $database
	 *
	 * @return    boolean True if the database has been successfully selected
	 */
	public function select($database)
	{
		if (!$database)
		{
			return false;
		}

		if (!mysqli_select_db($this->resource, $database))
		{
			if ($this->hasUTF())
			{
				$collate = "DEFAULT COLLATE utf8_general_ci";
			}
			else
			{
				$collate = "";
			}

			$sql = "CREATE DATABASE `$database` $collate";

			if (!mysqli_query($this->resource, $sql))
			{
				$this->errorNum = 4;
				$this->errorMsg = 'Could not create database';

				return true;
			}
			elseif (!mysqli_select_db($this->resource, $database))
			{
				$this->errorNum = 3;
				$this->errorMsg = 'Could not connect to database';

				return false;
			}
		}

		$verParts = explode('.', $this->getVersion());

		if ($verParts[0] == 5)
		{
			$this->setQuery("SET sql_mode = 'HIGH_NOT_PRECEDENCE'");
			$this->query();
			$this->errorMsg = '';
			$this->errorNum = '';
		}

		if ($this->hasUTF())
		{
			$this->setQuery("SET NAMES 'utf8'");
			$this->query();
		}

		return true;
	}

	/**
	 * Determines UTF support
	 *
	 * @return bool
	 */
	public function hasUTF()
	{
		$verParts = explode('.', $this->getVersion());

		return ($verParts[0] == 5 || ($verParts[0] == 4 && $verParts[1] == 1 && (int)$verParts[2] >= 2));
	}

	/**
	 * Custom settings for UTF support
	 */
	public function setUTF()
	{
		mysqli_query($this->resource, "SET NAMES 'utf8'");
	}

	/**
	 * Get a database escaped string
	 *
	 * @param  string $text  The string to be escaped
	 * @param  bool   $extra Optional parameter to provide extra escaping
	 *
	 * @return string
	 */
	public function getEscaped($text, $extra = false)
	{
		$result = @mysqli_real_escape_string($this->resource, $text);

		if ($extra)
		{
			$result = addcslashes($result, '%_');
		}

		return $result;
	}

	/**
	 * Execute the query
	 *
	 * @return mixed A database resource if successful, FALSE if not.
	 */
	public function query()
	{
		$this->open();

		if (is_object($this->cursor))
		{
			@mysqli_free_result($this->cursor);
		}

		// Take a local copy so that we don't modify the original query and cause issues later
		$sql = $this->sql;

		if ($this->limit > 0 || $this->offset > 0)
		{
			$sql .= ' LIMIT ' . $this->offset . ', ' . $this->limit;
		}

		$this->errorNum = 0;
		$this->errorMsg = '';
		$this->cursor = mysqli_query($this->resource, $sql, MYSQLI_USE_RESULT);

		if (!$this->cursor)
		{
			$this->errorNum = mysqli_errno($this->resource);
			$this->errorMsg = "\033[0;31m" . mysqli_error($this->resource) . "\033[0m\nSQL=\n\033[0;36m$sql\033[0m";

			return false;
		}

		return $this->cursor;
	}

	/**
	 * This method loads the first field of the first row returned by the query.
	 *
	 * @return mixed The value returned in the query or null if the query failed.
	 */
	public function loadResult()
	{
		if (!($cur = $this->query()))
		{
			return null;
		}

		$ret = null;

		if ($row = mysqli_fetch_row($cur))
		{
			$ret = $row[0];
		}

		mysqli_free_result($cur);

		return $ret;
	}

	/**
	 * Load an array of single field results into an array
	 *
	 * @param int $numInArray Column number (0-based)
	 *
	 * @return mixed An array, or null if query failed
	 */
	public function loadResultArray($numInArray = 0)
	{
		if (!($cur = $this->query()))
		{
			return null;
		}

		$array = array();

		while ($row = mysqli_fetch_row($cur))
		{
			$array[] = $row[$numInArray];
		}

		mysqli_free_result($cur);

		return $array;
	}

	/**
	 * Fetch a result row as an associative array
	 *
	 * @param bool $free_cursor If true, frees the cursor after returning the result
	 *
	 * @return array An associative array, null if query failed or false on end of data
	 */
	public function loadAssoc($free_cursor = false)
	{
		if (!is_resource($this->cursor) && !is_object($this->cursor))
		{
			if (!($this->cursor = $this->query()))
			{
				return null;
			}
		}

		$ret = null;

		if ($array = mysqli_fetch_assoc($this->cursor))
		{
			$ret = $array;
		}
		else
		{
			$ret = false;
			$free_cursor = true;
		}

		if ($free_cursor)
		{
			mysqli_free_result($this->cursor);
		}

		return $ret;
	}

	/**
	 * Load a associactive list of database rows
	 *
	 * @param string $key The field name of a primary key
	 *
	 * @return array If key is empty as sequential list of returned records.
	 */
	public function loadAssocList($key = null)
	{
		if (!($cur = $this->query()))
		{
			return null;
		}

		$array = array();

		while ($row = mysqli_fetch_assoc($cur))
		{
			if ($key)
			{
				$array[$row[$key]] = $row;
			}
			else
			{
				$array[] = $row;
			}
		}

		mysqli_free_result($cur);

		return $array;
	}

	/**
	 * Load a list of database rows (numeric column indexing)
	 * If <var>key</var> is not empty then the returned array is indexed by the value
	 * the database key.  Returns <var>null</var> if the query fails.
	 *
	 * @param string $key The field name of a primary key
	 *
	 * @return array
	 */
	public function loadRowList($key = null)
	{
		if (!($cur = $this->query()))
		{
			return null;
		}

		$array = array();

		while ($row = mysqli_fetch_row($cur))
		{
			if ($key !== null)
			{
				$array[$row[$key]] = $row;
			}
			else
			{
				$array[] = $row;
			}
		}

		mysqli_free_result($cur);

		return $array;
	}

	/**
	 * Get the version of the database connector
	 *
	 * @return string The database server's version number
	 */
	public function getVersion()
	{
		return mysqli_get_server_info($this->resource);
	}

	/**
	 * Returns the last INSERT auto_increase column's value
	 *
	 * @return int
	 */
	public function insertid()
	{
		return mysqli_insert_id($this->resource);
	}

	/**
	 * Get the error number
	 *
	 * @return int The error number for the most recent query
	 */
	public final function getErrorNum()
	{
		return $this->errorNum;
	}

	/**
	 * Get the error message
	 *
	 * @param bool $escaped Escape the message?
	 *
	 * @return string The error message for the most recent query
	 */
	public final function getErrorMsg($escaped = false)
	{
		if ($escaped)
		{
			return addslashes($this->errorMsg);
		}
		else
		{
			return $this->errorMsg;
		}
	}

	/**
	 * Quote an identifier name (field, table, etc)
	 *
	 * @param string $s The name
	 *
	 * @return string The quoted name
	 */
	public final function nameQuote($s)
	{
		// Only quote if the name is not using dot-notation
		if (strpos($s, '.') === false)
		{
			$q = $this->nameQuote;
			if (strlen($q) == 1)
			{
				return $q . $s . $q;
			}
			else
			{
				return $q{0} . $s . $q{1};
			}
		}
		else
		{
			return $s;
		}
	}

	/**
	 * Get the database table prefix
	 *
	 * @return string The database prefix
	 */
	public final function getPrefix()
	{
		return $this->table_prefix;
	}

	/**
	 * Sets the SQL query string for later execution.
	 * This function replaces a string identifier <var>$prefix</var> with the
	 * string held is the <var>table_prefix</var> class variable.
	 *
	 * @param string $sql    The SQL query
	 * @param int    $offset The offset to start selection
	 * @param int    $limit  The number of results to return
	 * @param string $prefix The common table prefix
	 */
	public function setQuery($sql, $offset = 0, $limit = 0, $prefix = '#__')
	{
		$this->sql = $this->replacePrefix($sql, $prefix);
		$this->limit = (int)$limit;
		$this->offset = (int)$offset;
		$this->cursor = null;
	}

	/**
	 * This function replaces a string identifier <var>$prefix</var> with the
	 * string held is the <var>table_prefix</var> class variable.
	 *
	 * @access public
	 *
	 * @param string $sql    The SQL query
	 * @param string $prefix The common table prefix
	 *
	 * @return string
	 */
	public final function replacePrefix($sql, $prefix = '#__')
	{
		$sql = trim($sql);

		$n = strlen($sql);

		$startPos = 0;
		$literal = '';
		while ($startPos < $n)
		{
			$ip = strpos($sql, $prefix, $startPos);
			if ($ip === false)
			{
				break;
			}

			$j = strpos($sql, "'", $startPos);
			$k = strpos($sql, '"', $startPos);
			if (($k !== false) && (($k < $j) || ($j === false)))
			{
				$quoteChar = '"';
				$j = $k;
			}
			else
			{
				$quoteChar = "'";
			}

			if ($j === false)
			{
				$j = $n;
			}

			$literal .= str_replace($prefix, $this->table_prefix, substr($sql, $startPos, $j - $startPos));
			$startPos = $j;

			$j = $startPos + 1;

			if ($j >= $n)
			{
				break;
			}

			// quote comes first, find end of quote
			while (true)
			{
				$k = strpos($sql, $quoteChar, $j);
				$escaped = false;
				if ($k === false)
				{
					break;
				}
				$l = $k - 1;
				while ($l >= 0 && $sql{$l} == '\\')
				{
					$l--;
					$escaped = !$escaped;
				}
				if ($escaped)
				{
					$j = $k + 1;
					continue;
				}
				break;
			}
			if ($k === false)
			{
				// error in the query - no end quote; ignore it
				break;
			}
			$literal .= substr($sql, $startPos, $k - $startPos + 1);
			$startPos = $k + 1;
		}
		if ($startPos < $n)
		{
			$literal .= substr($sql, $startPos, $n - $startPos);
		}

		return $literal;
	}

	/**
	 * Get the active query
	 *
	 * @return string The current value of the internal SQL vairable
	 */
	public function getQuery()
	{
		return $this->sql;
	}

	/**
	 * Get a quoted database escaped string
	 *
	 * @param string  $text    A string
	 * @param boolean $escaped Default true to escape string, false to leave the string unchanged
	 *
	 * @return    string
	 */
	public final function Quote($text, $escaped = true)
	{
		return '\'' . ($escaped ? $this->getEscaped($text) : $text) . '\'';
	}

	/**
	 * Gets the CREATE TABLE command for a given table/view
	 *
	 * @param string $table_abstract The abstracted name of the entity
	 * @param string $table_name     The name of the table
	 * @param string $type           The type of the entity to scan. If it's found to differ, the correct type is returned.
	 *
	 * @return string The CREATE command, w/out newlines
	 */
	protected function get_create($table_abstract, $table_name, &$type)
	{
		$sql = "SHOW CREATE TABLE `$table_abstract`";
		$this->setQuery($sql);
		$temp = $this->loadRowList();
		$table_sql = $temp[0][1];
		unset($temp);

		// Smart table type detection
		if (in_array($type, array('table', 'merge', 'view')))
		{
			// Check for CREATE VIEW
			$pattern = '/^CREATE(.*) VIEW (.*)/i';
			$result = preg_match($pattern, $table_sql);
			if ($result === 1)
			{
				// This is a view.
				$type = 'view';
			}
			else
			{
				// This is a table.
				$type = 'table';
			}

			// Is it a VIEW but we don't have SHOW VIEW privileges?
			if (empty($table_sql))
			{
				$type = 'view';
			}
		}

		$table_sql = str_replace($table_name, $table_abstract, $table_sql);

		// Replace newlines with spaces
		$table_sql = str_replace("\n", " ", $table_sql) . ";\n";
		$table_sql = str_replace("\r", " ", $table_sql);
		$table_sql = str_replace("\t", " ", $table_sql);

		// Post-process CREATE VIEW
		if ($type == 'view')
		{
			$pos_view = strpos($table_sql, ' VIEW ');

			if ($pos_view > 7)
			{
				// Only post process if there are view properties between the CREATE and VIEW keywords
				$propstring = substr($table_sql, 7, $pos_view - 7); // Properties string
				// Fetch the ALGORITHM={UNDEFINED | MERGE | TEMPTABLE} keyword
				$algostring = '';
				$algo_start = strpos($propstring, 'ALGORITHM=');
				if ($algo_start !== false)
				{
					$algo_end = strpos($propstring, ' ', $algo_start);
					$algostring = substr($propstring, $algo_start, $algo_end - $algo_start + 1);
				}
				// Create our modified create statement
				$table_sql = 'CREATE OR REPLACE ' . $algostring . substr($table_sql, $pos_view);
			}
		}

		return $table_sql;
	}

	/**
	 * Returns the abstracted name of a database object
	 *
	 * @param string $tableName
	 *
	 * @return string
	 */
	public function getAbstract($tableName)
	{
		$prefix = $this->getPrefix();

		// Don't return abstract names for non-CMS tables
		if (is_null($prefix))
		{
			return $tableName;
		}

		switch ($prefix)
		{
			case '':
				// This is more of a hack; it assumes all tables are CMS tables if the prefix is empty.
				return '#__' . $tableName;
				break;

			default:
				// Normal behaviour for 99% of sites
				$tableAbstract = $tableName;
				if (!empty($prefix))
				{
					if (substr($tableName, 0, strlen($prefix)) == $prefix)
					{
						$tableAbstract = '#__' . substr($tableName, strlen($prefix));
					}
					else
					{
						$tableAbstract = $tableName;
					}
				}

				return $tableAbstract;
				break;
		}
	}

	/**
	 * Inserts a row into a table based on an object's properties.
	 *
	 * @param   string $table   The name of the database table to insert into.
	 * @param   object &$object A reference to an object whose public properties match the table fields.
	 * @param   string $key     The name of the primary key. If provided the object property is updated.
	 *
	 * @return  boolean    True on success.
	 */
	public function insertObject($table, &$object, $key = null)
	{
		$fields = array();
		$values = array();

		// Iterate over the object variables to build the query fields and values.
		foreach (get_object_vars($object) as $k => $v)
		{
			// Only process non-null scalars.
			if (is_array($v) or is_object($v) or $v === null)
			{
				continue;
			}

			// Ignore any internal fields.
			if ($k[0] == '_')
			{
				continue;
			}

			// Prepare and sanitize the fields and values for the database query.
			$fields[] = $this->nameQuote($k);
			$values[] = $this->quote($v);
		}

		// Create the base insert statement.
		$query = 'INSERT INTO ' . $this->nameQuote($table)
			. '(' . implode(', ', $fields) . ') VALUES (' .
			implode(',', $values) . ')';

		// Set the query and execute the insert.
		$this->setQuery($query);

		if (!$this->query())
		{
			return false;
		}

		// Update the primary key if it exists.
		$id = $this->insertid();
		if ($key && $id && is_string($key))
		{
			$object->$key = $id;
		}

		return true;
	}

	/**
	 * Truncate a table
	 *
	 * @param string $table
	 */
	public function truncate($table)
	{
		$query = 'TRUNCATE TABLE ' . $this->nameQuote($table);
		$this->setQuery($query);
		$this->query();
	}
}
