<?php

	namespace ConverterLib\Format;

	use ConverterLib\Format;
	use ConverterLib\FormatRow\KnabRow;

	class Knab extends Format
	{
		const Delimeter = ';';
		const RowClass = KnabRow::class;
		const FormatName = 'knab';

		public static function checkRow($row)
		{
			if ($row[0] == "KNAB EXPORT") {
				return false;
			}
			return parent::checkRow($row);
		}
	}