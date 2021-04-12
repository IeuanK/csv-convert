<?php

	namespace ConverterLib\FormatRow;

	use ConverterLib\Config;
	use ConverterLib\FormatRow;
	use Exception;

	class RaboRow extends FormatRow
	{

		const Mapping = [
			'iban/bban'              => 'self_iban',
			'munt'                   => 'currency_code',
			'bic'                    => 'self_bic',
			'volgnr'                 => 'transaction_id',
			'datum'                  => 'date_transaction',
			'rentedatum'             => 'date_value',
			'bedrag'                 => 'amount',
			'tegenrekeningiban/bban' => 'opp_iban',
			'naamtegenpartij'        => 'opp_name',
			'bictegenpartij'         => 'opp_bic',
			'code'                   => 'transaction_type',
			'transactiereferentie'   => 'transaction_reference',
			'machtigingskenmerk'     => 'sepa_auth',
			'incassantid'            => 'sepa_id',
			'omschrijving1'          => 'description',
		];

		const Reverse = [
			'self_iban'             => "IBAN/BBAN",
			'currency_code'         => "Munt",
			'self_bic'              => "BIC",
			'transaction_id'        => "Volgnr",
			'date_transaction'      => "Datum",
			'date_value'            => "Rentedatum",
			'amount'                => "Bedrag",
			'opp_iban'              => "Tegenrekening IBAN/BBAN",
			'opp_name'              => "Naam tegenpartij",
			'opp_bic'               => "BIC tegenpartij",
			'transaction_type'      => "Code",
			'transaction_reference' => "Transactiereferentie",
			'sepa_auth'             => "Machtigingskenmerk",
			'sepa_id'               => "Incassant ID",
			'description'           => "Omschrijving-1",
		];

		const Ignore = [
			'saldonatrn',
			'batchid',
			'naamuiteindelijkepartij',
			'naaminitirendepartij',
			'betalingskenmerk',
			'redenretour',
			'oorsprbedrag',
			'oorsprmunt',
			'koers',
			'omschrijving2',
			'omschrijving3',
		];


		public function loadColumnIbanbban($v)
		{
			$this->self_iban = static::FormatIBAN($v);
			return true;
		}

		public function loadColumnTegenrekeningibanbban($v)
		{
			$this->opp_iban = static::FormatIBAN($v);
			return true;
		}

		public function loadColumnBedrag($v)
		{
			try {

				$v = str_replace('.', '', $v);
				$v = str_replace(',', '.', $v);
				$i = (int)$v;
				if (substr($v, 0, 1) == '+') {
					$this->posneg = '+';
					$this->amount_abs = abs($v);
					$this->amount = $this->posneg . $this->amount_abs;
					return true;
				}
				if (substr($v, 0, 1) == '-') {
					$this->posneg = '-';
					$this->amount_abs = abs($v);
					$this->amount = $this->posneg . $this->amount_abs;
					return true;
				}
			} catch (Exception $e) {
				return false;
			}
			return false;
		}

		public function loadColumnTransactiereferentie($v)
		{
			$str = [];
			if (isset($this->rawData['betalingskenmerk'])) {
				$str[] = $this->rawData['betalingskenmerk'];
			}
			if (isset($this->rawData['transactiereferentie'])) {
				$str[] = $this->rawData['transactiereferentie'];
			}
			return implode("-", $str);
		}

		public function getColumnAmount()
		{
			$v = $this->amount_abs;
			return $this->posneg . number_format($v, 2, ',', '');
		}

		public function postProcess()
		{
			if ($this->amount_abs == 0.0) {
				return false;
			}

			// Tegenrekening is eigen rekening
			if (in_array($this->opp_iban, Config::EigenRekeningen)) {
				// Beide rekeningen zijn eigen rekening
				if (in_array($this->self_iban, Config::EigenRekeningen)) {
					// Rekening is RABO
					if (preg_match("/^[A-Z]{2}[0-9]{2}RABO/", $this->self_iban)) {
						// Bedrag is negatief, negeren want die pakken we van tegenrekening
						if ($this->posneg == '-') {
							// Negatieve overschrijving van en naar eigen rekening
							op("Negatieve overschrijving van en naar eigen rekening");
							return false;
						}
					}
					// Naar spaarrekening negeren we ook
					if (preg_match('/^[0-9]+$/', $this->opp_iban)) {
						// Naar spaarrekening
						op("Naar spaarrekening");
						return false;
					}
				}
			}

			return true;
		}
	}