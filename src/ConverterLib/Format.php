<?php

	namespace ConverterLib;

	class Format
	{
		const RowClass = FormatRow::class;

		const FormatName = '';

		public $rows = [];
		public $filename = "";

		const Delimeter = ',';

		public $rawData = [];

		static function fromCSV($csvfile)
		{
			$row = 1;
			$headers = [];
			$fileData = [];
			if (($handle = fopen($csvfile, "r")) !== FALSE) {
				while (($data = fgetcsv($handle, 1000, static::Delimeter)) !== FALSE) {
					if(!static::checkRow($data)) {
						continue;
					}
					if ($row === 1) {
						foreach ($data as $header) {
							if (!empty($header)) {
								$headers[] = preg_replace("/[^0-9a-zA-Z]+/", "", strtolower($header));
							}
						}
					} else {
						$r = [];
						$num = count($data);
						for ($i = 0; $i < $num; $i++) {
							$header = $headers[$i] ?? '';
							if (!empty($header)) {
								$r[$header] = $data[$i];
							}
						}
						if (!empty($r)) {
							$fileData[] = $r;
						}
					}
					$row++;
				}
				fclose($handle);
			}
			return static::fromArray($fileData);
		}

		public function toCsv($directory)
		{
			op("Converting to CSV");
			$first = true;
			$data = [];
			foreach ($this->rows as $row) {
				$array = $row->toCsv();
				if ($first) {
					$data[] = array_keys($array);
					$first = false;
				}
				$data[] = array_values($array);
			}
			op('Saving to ' . $directory . '/' . $this::FormatName . '_' . $this->filename);
			$out = fopen($directory . '/' . $this::FormatName . '_' . $this->filename, 'w');
			foreach ($data as $d) {
				fputcsv($out, $d);
			}
			fclose($out);
			return $out;
		}

		/**
		 * @param $class
		 * @return Format
		 */
		public function toFormat($class)
		{
			$obj = new $class;
			$obj->rawData = $this->rawData;
			foreach ($this->rows as $row) {
				if ($newRow = $row->toFormat($obj::RowClass)) {
					$obj->rows[] = $newRow;
				}
			}
			$obj->filename = "converted_" . $this->filename;
			return $obj;
		}

		static function fromArray($arr)
		{
			$inst = new static();
			$inst->rawData = $arr;
			$num = 0;
			foreach ($arr as $row) {
				$rowClass = static::RowClass;
				if ($rObj = $rowClass::fromArray($row)) {
					$inst->rows[] = $rObj;
				} else {
					op("Row {$num} not imported");
				}
				$num++;
			}
			return $inst;
		}

		static function checkRow($row) {
			if(empty($row)) {
				return false;
			}
			return true;
		}
	}