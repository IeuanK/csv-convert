<?php
	session_start();
	define('BASE_DIR', __DIR__);
	define('DATA_DIR', realpath('./data/'));
	require_once("vendor/autoload.php");
	Kint\Renderer\RichRenderer::$folder = false;
	function op($op)
	{
		if (is_string($op)) {
			print($op . PHP_EOL);
		} else {
			var_dump($op . PHP_EOL);
		}
		ob_flush();
	}

	spl_autoload_register(function ($class) {
		$class = str_replace('\\', DIRECTORY_SEPARATOR, $class);
		$file = BASE_DIR . '/src/' . $class . '.php';
		if (file_exists($file)) {
			require_once($file);
		} else {
			throw new RuntimeException('Class not found');
		}
	});

	define('FIREFLY__IMPORT__CONVERT', \ConverterLib\Config::SecureKey);

	$loader = new \Twig\Loader\FilesystemLoader(BASE_DIR . '/templates');
	$twig = new \Twig\Environment($loader, [
		//'cache' => BASE_DIR.'/cache',
		'cache' => false,
		'debug' => true,
	]);

	if (isset($_REQUEST['convert'])) {
		require_once("convert.php");
	} else {
		print $twig->render('base.twig');
	}