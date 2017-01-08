<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit2efe611e2fef44c89dd0029334cd0181
{
    public static $prefixesPsr0 = array (
        'P' => 
        array (
            'Pug\\' => 
            array (
                0 => __DIR__ . '/..' . '/pug-php/pug/src',
            ),
        ),
        'J' => 
        array (
            'JsPhpize' => 
            array (
                0 => __DIR__ . '/..' . '/js-phpize/js-phpize/src',
            ),
            'Jade\\' => 
            array (
                0 => __DIR__ . '/..' . '/pug-php/pug/src',
            ),
        ),
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixesPsr0 = ComposerStaticInit2efe611e2fef44c89dd0029334cd0181::$prefixesPsr0;

        }, null, ClassLoader::class);
    }
}
