<?php

	namespace ConverterLib;

	class FormatRow
	{
		/**
		 * @var String $self_name Naam eigen rekening
		 */
		public $self_name;
		/**
		 * @var String $self_iban IBAN eigen rekening
		 */
		public $self_iban;
		/**
		 * @var String $self_bic BIC eigen rekening
		 */
		public $self_bic;
		/**
		 * @var String $self_name Naam tegenrekening
		 */
		public $opp_name;
		/**
		 * @var String $opp_iban IBAN tegenrekening
		 */
		public $opp_iban;
		/**
		 * @var String $opp_bic BIC tegenrekening
		 */
		public $opp_bic;
		/**
		 * @var String $sepa_auth SEPA-authenticatie, machtigingskenmerk, machtigingsnummer
		 */
		public $sepa_auth;
		/**
		 * @var String $sepa_id SEPA-incassant ID
		 */
		public $sepa_id;
		/**
		 * @var String $date_transaction Transactiedatum: 2020-02-03
		 */
		public $date_transaction;
		/**
		 * @var String $date_value Rentedatum, valutadatum: 2020-02-03
		 */
		public $date_value;
		/**
		 * @var String $date_booking Boekdatum: 2020-02-03
		 */
		public $date_booking;
		/**
		 * @var String $currency_code ISO valutacode: EUR, USD
		 */
		public $currency_code;
		/**
		 * @var integer $amount Transactiebedrag: -11,25
		 */
		public $amount;
		/**
		 * @var integer $amount_abs Absolute transactiebedrag: 11,25
		 */
		public $amount_abs;
		/**
		 * @var String $posneg Credit/debet: +/-
		 */
		public $posneg;
		/**
		 * @var String $description Beschrijving/omschrijving
		 */
		public $description;
		/**
		 * @var String $transaction_type Betaalwijze: Betaalautomaat, iDEAL maar ook codes als 'ma', 'bg'
		 */
		public $transaction_type;
		/**
		 * @var String $transaction_id Volgnr in sheet
		 */
		public $transaction_id;
		/**
		 * @var String $transaction_reference Referentie/betalingskenmerk
		 */
		public $transaction_reference;

		const Fillable = [
			'self_name',
			'self_iban',
			'self_bic',
			'opp_name',
			'opp_iban',
			'opp_bic',
			'sepa_auth',
			'sepa_id',
			'date_transaction',
			'date_value',
			'date_booking',
			'currency_code',
			'amount',
			'amount_abs',
			'posneg',
			'description',
			'transaction_type',
			'transaction_id',
			'transaction_reference',
		];

		/**
		 * @var array $rawData Originele CSV-data als array
		 */
		public $rawData = [];

		/**
		 * Mapping van velden
		 */
		const Mapping = [];

		/**
		 * Omgekeerde mapping
		 */
		const Reverse = [];

		/**
		 * Velden om te negeren
		 */
		const Ignore = [];

		/**
		 * Instance ophalen vanaf array
		 * @param string[] $arr Array met data
		 * @return static|false
		 */
		public static function fromArray($arr)
		{
			//d($arr);
			$obj = new static();
			$obj->rawData = $arr;
			$fail = false;
			foreach ($arr as $k => $v) {
				if (!$obj->loadColumn($k, $v) && !in_array($k, $obj::Ignore)) {
					op("Key {$k} not mapped");
					$fail = true;
				}
			}
			if (!$obj->postProcess()) {
				$fail = true;
			}
			if (!$fail) {
				return $obj;
			}
			return false;
		}

		public function loadColumn($k, $v)
		{
			$fKey = strtolower(str_replace("/[^a-zA-Z0-9]+/", "", $k));
			$m = 'loadColumn' . ucfirst($fKey);
			if (!method_exists($this, $m)) {
				if (!empty(static::Mapping[$k])) {
					$this->{static::Mapping[$k]} = $v;
					return true;
				}
				if (property_exists($this, $k)) {
					$this->$k = $v;
					return true;
				}
			} else {
				return $this->$m($v);
			}
			return false;
		}

		public function toFormat($class)
		{
			$obj = new $class;
			foreach (static::Fillable as $key) {
				if (isset($this->$key)) {
					$obj->$key = $this->$key;
				}
			}
			return $obj;
		}

		/**
		 * Post-processing toepassen
		 * bijvoorbeeld credit/debet en bedrag met elkaar controleren
		 */
		public function postProcess()
		{
			return true;
		}

		/**
		 * Array voor CSV-rij ophalen
		 * @return array
		 */
		public function toCsv()
		{
			$array = [];
			foreach($this::Reverse as $k => $header) {
				$m = 'getColumn' . ucfirst($k);
				if (!method_exists($this, $m)) {
					$array[$header] = $this->$k;
				} else {
					$array[$header] = $this->$m();
				}
			}
			return $array;
		}

		public static function FormatIBAN($v) {
			$v = preg_replace("/[^a-zA-Z0-9]+/", "", $v);
			$v = strtoupper($v);
			return $v;
		}
	}
