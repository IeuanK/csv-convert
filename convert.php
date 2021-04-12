<?php
	if (!defined('FIREFLY__IMPORT__CONVERT')) {
		die('No access (1)');
	}
	if(FIREFLY__IMPORT__CONVERT !== \ConverterLib\Config::SecureKey) {
		die('No access (2)');
	}

	/**
	 * @var \Twig\Environment $twig
	 */
	$types = \ConverterLib\Config::Types;


	if (isset($_POST['convert'])) {
		if (!isset($types[$_POST['from_type']])) {
			die('Wrong from type');
		}
		$from_type = $types[$_POST['from_type']];
		if (!isset($types[$_POST['to_type']])) {
			die('Wrong to type');
		}
		$to_type = $types[$_POST['to_type']];
		convertFiles($_FILES['files'], $from_type, $to_type);
	} else {
		print $twig->render('files.twig', [
			'types' => $types
		]);
	}


	function convertFiles($tFileArr, $from, $to)
	{
		global $twig;
		ob_start();
		print $twig->render('converting.twig');
		print('<pre>');
		ob_flush();
		$numFiles = count($tFileArr['error']);
		$files = [];
		for ($i = 0; $i < $numFiles; $i++) {
			$f = [
				'name' => $tFileArr['name'][$i],
				'type' => $tFileArr['type'][$i],
				'tmp_name' => $tFileArr['tmp_name'][$i],
				'error' => $tFileArr['error'][$i],
				'size' => $tFileArr['size'][$i],
			];
			if ($f['error'] != "0") {
				d("Error", $f);
				continue;
			}
			$files[] = $f;
		}
		foreach ($files as $file) {
			$class = $from;
			/**
			 * @var ConverterLib\Format $sheet
			 */
			$sheet = $class::fromCSV($file['tmp_name']);
			$sheet->filename = $file['name'];
			op("Loaded {$from} instance");

			$targetClass = $to;
			$target = $sheet->toFormat($targetClass);
			op("Converted to {$to} instance");

			$target->toCsv(realpath(DATA_DIR.'/converted'));

			op('Output done');
		}
//		foreach ($_SESSION['importer']['files'] as $hash => $file) {
//			if (!file_exists($file['path'])) {
//				unset($_SESSION['importer']['files'][$hash]);
//			}
//			//$contents = file_get_contents($file['path']);
//			$class = $types[$file['type']];
//			/**
//			 * @var ConverterLib\Format $sheet
//			 */
//			$sheet = $class::fromCSV($file['path']);
//			$sheet->filename = basename($file['path']);
//			op("Loaded {$file['type']} instance");
//
//			$targetClass = $types[$_SESSION['importer']['target_type']];
//			$target = $sheet->toFormat($targetClass);
//			op("Converted to {$_SESSION['importer']['target_type']} instance");
//
//			d($sheet, $target);
//
//			op('Output done');
//		}
		print('</pre>');
		ob_flush();
	}