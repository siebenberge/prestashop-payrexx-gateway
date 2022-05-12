<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit2a7005aef54ada1a4b6464f282a4b9f4
{
    public static $prefixLengthsPsr4 = array (
        'P' => 
        array (
            'Payrexx\\PayrexxPaymentGateway\\' => 30,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Payrexx\\PayrexxPaymentGateway\\' => 
        array (
            0 => __DIR__ . '/../..' . '/src',
        ),
    );

    public static $prefixesPsr0 = array (
        'P' => 
        array (
            'Payrexx' => 
            array (
                0 => __DIR__ . '/..' . '/payrexx/payrexx/lib',
            ),
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit2a7005aef54ada1a4b6464f282a4b9f4::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit2a7005aef54ada1a4b6464f282a4b9f4::$prefixDirsPsr4;
            $loader->prefixesPsr0 = ComposerStaticInit2a7005aef54ada1a4b6464f282a4b9f4::$prefixesPsr0;
            $loader->classMap = ComposerStaticInit2a7005aef54ada1a4b6464f282a4b9f4::$classMap;

        }, null, ClassLoader::class);
    }
}