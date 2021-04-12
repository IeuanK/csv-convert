<?php

	namespace ConverterLib\Format;

	use ConverterLib\Format;
	use ConverterLib\FormatRow\KnabRow;
	use ConverterLib\FormatRow\RaboRow;

	class Rabo extends Format
	{
		const Delimeter = ',';
		const RowClass = RaboRow::class;
		const FormatName = 'rabo';
	}