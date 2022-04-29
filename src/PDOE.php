<?php

	namespace Traineratwot\PDOExtended;

	use PDO;
	use PDOException;
	use Traineratwot\PDOExtended\exception\DsnException;
	use Traineratwot\PDOExtended\interfaces\DsnInterface;
	use Traineratwot\PDOExtended\statement\PDOEPoolStatement;
	use Traineratwot\PDOExtended\statement\PDOEStatement;


	class PDOE extends PDO
	{
		/**
		 * PostgreSQL
		 * <img src="https://wiki.postgresql.org/images/3/30/PostgreSQL_logo.3colors.120x120.png" width="50" height="50" />
		 */
		public const DRIVER_PostgreSQL = 'pgsql';
		/**
		 * SQLite
		 * <img src="https://cdn.icon-icons.com/icons2/2699/PNG/512/sqlite_logo_icon_169724.png" width="50" height="50" />
		 */
		public const DRIVER_SQLite = 'sqlite';
		/**
		 * PostgreSQL
		 * <img src="https://img-blog.csdnimg.cn/20200828185219514.jpg?x-oss-process=image/resize,m_fixed,h_64,w_64" width="50" height="50" />
		 */
		public const DRIVER_MySQL    = 'mysql';
		public const CHARSET_utf8    = 'utf8';
		public const CHARSET_utf8mb4 = 'utf8mb4';

		/**
		 * @var array|false
		 */
		private $query_count = 0;

		/**
		 * @var dsn
		 */
		public $dsn;

		/**
		 * @inheritDoc
		 * @param Dsn   $dsn
		 * @param array $driverOptions
		 * @throws DsnException
		 */
		public function __construct(DsnInterface $dsn, $driverOptions = [])
		{
			$this->dsn = $dsn;
			try {
				parent::__construct($dsn->get(), $dsn->getUsername(), $dsn->getPassword(), $driverOptions);
			} catch (PDOException $e) {
				throw new DsnException($dsn->get(), $e->getCode(), $e);
			}
			$this->setAttribute(PDO::ATTR_STATEMENT_CLASS, [PDOEStatement::class, [$this]]);
		}

		/**
		 * @return array|false|int
		 */
		public function queryCount()
		{
			return $this->query_count;
		}

		/**
		 * @link http://php.net/manual/en/pdo.exec.php
		 * @inheritDoc
		 */
		public function exec($statement)
		{
			$this->queryCountIncrement();
			return parent::exec($statement); // TODO: Change the autogenerated stub
		}

		/**
		 * @return void
		 */
		public function queryCountIncrement()
		{
			$this->query_count++;
		}

		/**
		 * Проверяет существование таблицы в базе. возврящет ее правильное название с учетом регистра | FALSE
		 * @param string $table
		 * @return FALSE|string
		 * @throws DsnException
		 */
		public function tableExists($table)
		{
			$list = $this->getAllTables();
			$find = FALSE;
			foreach ($list as $t) {
				if (mb_strtolower($t) === mb_strtolower($table)) {
					$find = TRUE;
					break;
				}
			}
			return $find ? $t : FALSE;
		}

		/**
		 * @return array
		 * @throws DsnException
		 */
		public function getAllTables()
		{
			if ($this->dsn->getDriver() === self::DRIVER_SQLite) {
				return $this->query("SELECT name FROM sqlite_master WHERE type='table'")->fetchAll(PDO::FETCH_COLUMN);
			}
			if ($this->dsn->getDriver() === self::DRIVER_PostgreSQL) {
				return $this->query("SELECT table_name FROM information_schema.tables")->fetchAll(PDO::FETCH_COLUMN);
			}
			return $this->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
		}

		/**
		 * @inheritDoc
		 */
		public function query($statement, $mode = self::ATTR_DEFAULT_FETCH_MODE, $arg3 = NULL, array $ctorargs = [])
		{
			$arg = func_get_args();
			$arg = array_slice($arg, 1);
			$this->queryCountIncrement();
			return parent::query($statement, ...$arg); // TODO: Change the autogenerated stub
		}

		/**
		 * @param string $statement SQL request
		 * @param array  $driver_options
		 * @return bool|PDOEPoolStatement
		 */
		public function poolPrepare($statement, array $driver_options = [])
		{

			$this->setAttribute(PDO::ATTR_STATEMENT_CLASS, [PDOEPoolStatement::class, [$this]]);
			return parent::prepare($statement, $driver_options);
		}

		/**
		 * @inheritDoc
		 */
		public function prepare($statement, $driver_options = NULL)
		{
			$arg = func_get_args();
			$arg = array_slice($arg, 1);
			$this->setAttribute(PDO::ATTR_STATEMENT_CLASS, [PDOEStatement::class, [$this]]);
			return parent::prepare($statement, ...$arg);
		}

		/**
		 * @param $name
		 * @return array|false|int|null
		 */
		public function __get($name)
		{
			switch ($name) {
				case 'query_count':
				case 'queryCount':
					return $this->queryCount();
			}
			return NULL;
		}

		/**
		 * @param $name
		 * @param $value
		 * @return false
		 */
		public function __set($name, $value)
		{
			return FALSE;
		}

		/**
		 * @param $name
		 * @return bool
		 */
		public function __isset($name)
		{
			return $name === 'query_count';
		}

		/**
		 * @return array|false
		 */
		public function getQueryCount()
		{
			return $this->query_count;
		}

	}

