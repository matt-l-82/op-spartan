<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit460db8e2f0ce79cac04c5c03bda8fcc6
{
    public static $prefixLengthsPsr4 = array (
        'G' => 
        array (
            'GiveAnnualReceipts\\' => 19,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'GiveAnnualReceipts\\' => 
        array (
            0 => __DIR__ . '/../..' . '/src',
        ),
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit460db8e2f0ce79cac04c5c03bda8fcc6::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit460db8e2f0ce79cac04c5c03bda8fcc6::$prefixDirsPsr4;

        }, null, ClassLoader::class);
    }
}
