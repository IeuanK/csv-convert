<?php

	namespace ConverterLib\FormatRow;

	use ConverterLib\Config;
	use ConverterLib\FormatRow;
	use Exception;

	class KnabRow extends FormatRow
	{
		const Mapping = [
			'rekeningnummer' => 'self_iban',
			'transactiedatum' => 'date_transaction',
			'valutacode' => 'currency_code',
			'creditdebet' => 'posneg',
			'bedrag' => 'amount',
			'tegenrekeningnummer' => 'opp_iban',
			'tegenrekeninghouder' => 'opp_name',
			'valutadatum' => 'date_value',
			'omschrijving' => 'description',
			'typebetaling' => 'transaction_type',
			'machtigingsnummer' => 'sepa_auth',
			'incassantid' => 'sepa_id',
			'referentie' => 'transaction_reference',
			'boekdatum' => 'date_booking',
		];

		const Reverse = [
			'self_iban' => 'Rekeningnummer',
			'date_transaction' => 'Transactiedatum',
			'currency_code' => 'Valutacode',
			'posneg' => 'CreditDebet',
			'amount' => 'Bedrag',
			'opp_iban' => 'Tegenrekeningnummer',
			'opp_name' => 'Tegenrekeninghouder',
			'date_value' => 'Valutadatum',
			'description' => 'Omschrijving',
			'transaction_type' => 'Type betaling',
			'sepa_auth' => 'Machtigingsnummer',
			'sepa_id' => 'Incassant ID',
			'transaction_reference' => 'Referentie',
			'date_booking' => 'Boekdatum',
		];

		const Ignore = [
			'adres',
			'betaalwijze'
		];

		public function loadColumnRekeningnummer($v)
		{
			$this->self_iban = static::FormatIBAN($v);
			return true;
		}

		public function loadColumnTegenrekeningnummer($v)
		{
			$this->opp_iban = static::FormatIBAN($v);
			return true;
		}

		public function loadColumnCreditdebet($v)
		{
			if ($v === "D") {
				$this->posneg = '-';
				return true;
			}
			if ($v === "C") {
				$this->posneg = '+';
				return true;
			}
			return false;
		}

		public function loadColumnBedrag($v)
		{
			try {
				$v = str_replace('.', '', $v);
				$v = str_replace(',', '.', $v);
				$this->amount_abs = abs($v);
				$this->amount = $this->posneg . $this->amount_abs;
				return true;
			} catch (Exception $e) {
				return false;
			}
		}

		public function getColumnPosneg()
		{
			if ($this->posneg == '-') {
				return 'D';
			}
			if ($this->posneg == '+') {
				return 'C';
			}
			return '';
		}

		public function getColumnAmount()
		{
			$v = $this->amount_abs;
			$v = str_replace('.', ',', $v);
			return $v;
		}

		public function postProcess()
		{
			if($this->amount_abs == 0.0) {
				return false;
			}

			// Tegenrekening is eigen rekening
			if (in_array($this->opp_iban, Config::EigenRekeningen)) {
				// Beide rekeningen zijn eigen rekening
				if (in_array($this->self_iban, Config::EigenRekeningen)) {
					// Rekening is KNAB
					if (preg_match('/^[A-Z]{2}[0-9]{2}KNAB/', $this->self_iban)) {
						// Bedrag is negatief, negeren want die pakken we van tegenrekening
						if ($this->posneg == '-') {
							// Negatieve overschrijving van en naar eigen rekening
							op("Negatieve overschrijving van en naar eigen rekening");
							return false;
						}
					}
					// Naar spaarrekening negeren we ook
					if (preg_match("/^[0-9]+$/", $this->opp_iban)) {
						// Naar spaarrekening
						op("Naar spaarrekening");
						return false;
					}
				}
			}

			return true;
		}
	}