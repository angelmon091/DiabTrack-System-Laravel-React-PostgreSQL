<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Nombre de la aplicación
    |--------------------------------------------------------------------------
    |
    | Este valor es el nombre de tu aplicación, que se utilizará cuando el
    | framework necesite colocar el nombre de la aplicación en una notificación o
    | otros elementos de la interfaz de usuario donde se deba mostrar el nombre de la aplicación.
    |
    */

    'name' => env('APP_NAME', 'Laravel'),

    /*
    |--------------------------------------------------------------------------
    | Entorno de la aplicación
    |--------------------------------------------------------------------------
    |
    | Este valor determina el "entorno" en el que se está ejecutando actualmente tu aplicación.
    | Esto puede determinar cómo prefieres configurar varios servicios que utiliza la aplicación.
    | Establécelo en tu archivo ".env".
    |
    */

    'env' => env('APP_ENV', 'production'),

    /*
    |--------------------------------------------------------------------------
    | Modo de depuración de la aplicación
    |--------------------------------------------------------------------------
    |
    | Cuando tu aplicación está en modo de depuración, se mostrarán mensajes de error detallados con
    | rastros de pila en cada error que ocurra dentro de tu
    | aplicación. Si está deshabilitado, se mostrará una página de error genérica simple.
    |
    */

    'debug' => (bool) env('APP_DEBUG', false),

    /*
    |--------------------------------------------------------------------------
    | URL de la aplicación
    |--------------------------------------------------------------------------
    |
    | Esta URL se utiliza por la consola para generar correctamente las URL cuando se utiliza
    | la herramienta de línea de comandos Artisan. Debes establecer esto en la raíz de
    | la aplicación para que esté disponible dentro de los comandos Artisan.
    |
    */

    'url' => env('APP_URL', 'http://localhost'),

    /*
    |--------------------------------------------------------------------------
    | Zona horaria de la aplicación
    |--------------------------------------------------------------------------
    |
    | Aquí puedes especificar la zona horaria predeterminada para tu aplicación, que
    | será utilizada por las funciones de fecha y hora de PHP. La zona horaria
    | está establecida en "UTC" por defecto, ya que es adecuada para la mayoría de los casos de uso.
    |
    */

    'timezone' => env('APP_TIMEZONE', 'UTC'),

    /*
    |--------------------------------------------------------------------------
    | Configuración de idioma de la aplicación
    |--------------------------------------------------------------------------
    |
    | El idioma de la aplicación determina el idioma predeterminado que se utilizará
    | por los métodos de traducción / localización de Laravel. Esta opción puede ser
    | establecida en cualquier idioma para el que planees tener cadenas de traducción.
    |
    */

    'locale' => env('APP_LOCALE', 'en'),

    'fallback_locale' => env('APP_FALLBACK_LOCALE', 'en'),

    'faker_locale' => env('APP_FAKER_LOCALE', 'en_US'),

    /*
    |--------------------------------------------------------------------------
    | Clave de cifrado
    |--------------------------------------------------------------------------
    |
    | Esta clave es utilizada por los servicios de cifrado de Laravel y debe ser establecida
    | a una cadena aleatoria de 32 caracteres para asegurar que todos los valores cifrados
    | sean seguros. Debes hacer esto antes de desplegar la aplicación.
    |
    */

    'cipher' => 'AES-256-CBC',

    'key' => env('APP_KEY'),

    'previous_keys' => [
        ...array_filter(
            explode(',', (string) env('APP_PREVIOUS_KEYS', ''))
        ),
    ],

    /*
    |--------------------------------------------------------------------------
    | Driver del modo de mantenimiento
    |--------------------------------------------------------------------------
    |
    | Estas opciones de configuración determinan el driver utilizado para determinar y
    | gestionar el estado del "modo de mantenimiento" de Laravel. El driver "cache" permitirá
    | controlar el modo de mantenimiento a través de múltiples máquinas.
    |
    | Supported drivers: "file", "cache"
    |
    */

    'maintenance' => [
        'driver' => env('APP_MAINTENANCE_DRIVER', 'file'),
        'store' => env('APP_MAINTENANCE_STORE', 'database'),
    ],

];
