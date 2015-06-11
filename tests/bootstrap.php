<?php

// We start a session here, to be sure that no output has been sent yet.
// We need this for tests that use PHP sessions.
session_start();

// Autoloader for source files
spl_autoload_register(function($class)
{
	$path = '../src/';
	$prefix = "StreamOne\\API\\v3\\";
	$file = $path . substr($class, strlen($prefix)) . '.php';
	if ((substr($class, 0, strlen($prefix)) === $prefix) && file_exists($file))
	{
		require_once($file);
	}
});

// Autoloader for PHPUnit support files
spl_autoload_register(function($class)
{
	$path = 'lib/';
	$prefix = "PHPUnit_";
	$file = $path . $class . '.php';
	if ((substr($class, 0, strlen($prefix)) === $prefix) && file_exists($file))
	{
		require_once($file);
	}
});
