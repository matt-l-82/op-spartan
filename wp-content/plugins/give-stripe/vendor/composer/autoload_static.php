<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit1b334fe60a38ed710b83a3bc577e34d7
{
    public static $prefixLengthsPsr4 = array (
        'G' => 
        array (
            'GiveStripe\\' => 11,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'GiveStripe\\' => 
        array (
            0 => __DIR__ . '/../..' . '/src',
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit1b334fe60a38ed710b83a3bc577e34d7::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit1b334fe60a38ed710b83a3bc577e34d7::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInit1b334fe60a38ed710b83a3bc577e34d7::$classMap;

        }, null, ClassLoader::class);
    }
}
