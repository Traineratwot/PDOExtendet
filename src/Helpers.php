<?php

	namespace Traineratwot\PDOExtended;

	use PDO;

	class Helpers
	{

		/**
		 * @param string            $sql
		 * @param array             $values
		 * @param Callable|PDO|null $escape
		 * @return string
		 */
		public static function prepare(string $sql, array $values, $escape = NULL)
		: string
		{
			$sql      = trim(trim($sql), ';');
			$words    = preg_split(<<<REGEXP
@[\s(),."`']+@
REGEXP
				, $sql);
			$i        = -1;
			$nameTags = [];
			$question = [];
			foreach ($words as $key => $word) {
				if ($word === '?') {
					$i++;
					$question[$key] = $i;
					continue;
				}
				if (strpos($word, ':') === 0) {
					$nameTags[$key] = $word;
				}
			}
			foreach ($nameTags as $word) {
				if (array_key_exists($word, $values)) {
					$value = $values[$word];
				} elseif (array_key_exists(substr($word, 1), $values)) {
					$value = $values[substr($word, 1)];
				} else {
					continue;
				}
				$value = self::getValue($escape, $value);
				$sql = str_replace($word, $value, $sql);
			}
			foreach ($question as $i) {
				if (array_key_exists($i, $values)) {
					$value = $values[$i];
				} else {
					continue;
				}
				$value = self::getValue($escape, $value);
				$sql = preg_replace('@([^?](\?)[^?])|([^?](\?)$)@', ' ' . $value . ' ', $sql, 1);

			}
			$sql = trim($sql);
			$sql .= ';';
			return preg_replace('/;+\s*$/', ';', $sql);
		}

		/**
		 * @param $escape
		 * @param $value
		 * @return false|string
		 */
		public static function getValue($escape, $value)
		{
			if (is_null($escape)) {
				$value = "'" . escapeshellcmd($value) . "'";
			} elseif ($escape instanceof PDO) {
				$value = $escape->quote($value);
			} elseif (is_callable($escape)) {
				$value = $escape($value);
			} else {
				$value = "'" . escapeshellcmd($value) . "'";
			}
			return $value;
		}
	}