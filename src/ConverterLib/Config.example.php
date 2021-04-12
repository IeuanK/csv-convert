<?php

	namespace ConverterLib;

	class Config
	{
		public const EigenRekeningen = [
		];

		public const Types = [
			'knab' => \ConverterLib\Format\Knab::class,
			'rabo' => \ConverterLib\Format\Rabo::class,
		];

		public const SecureKey = 'sdgffddfgdfg,fdgdff';
	}