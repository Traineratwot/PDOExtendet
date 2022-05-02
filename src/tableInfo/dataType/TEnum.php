<?php

	namespace Traineratwot\PDOExtended\tableInfo\dataType;

	use Traineratwot\PDOExtended\abstracts\DataType;
	use Traineratwot\PDOExtended\exceptions\DataTypeException;

	class TEnum extends DataType
	{
		public string $phpName = 'string';
		public array  $values  = [];

		public function validate()
		: void
		{
			if (!in_array($this->value, $this->values, TRUE)) {
				throw new DataTypeException("invalid value ");
			}
		}

		/**
		 * @inheritDoc
		 */
		public function convert($value)
		{
			return is_null($value) ? NULL : $value;
		}
	}