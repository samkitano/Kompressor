<?php

/*
|--------------------------------------------------------------------------
| Kompressor - A Compression Tool utility for Laravel 5.6 +
|--------------------------------------------------------------------------
|
| Here you can change this utility default settings.
|
*/

return [
    /*
    |--------------------------------------------------------------------------
    | Compression library to utilize
    |--------------------------------------------------------------------------
    |
    | Pick your favourite compression library.
    | Default: 'zip'
    | Available:
    |   - zip
    |
    | Feel free to implement your own library (bzip, phar, gzip, etc).
    | See Documentation.
    |
    */

    'library' => 'zip',

    /*
    |--------------------------------------------------------------------------
    | PHP glob() Search Pattern for directory storage
    |--------------------------------------------------------------------------
    |
    | If you consistently need a specific search pattern for archiving
    | entire directories, you can change the default pattern here.
    | Should you want to archive log files only, for instance,
    | you could set this value to '*.log'. You can always
    | override this pattern. See documentation.
    |
    */

    'glob_dir_pattern' => '*',

    /*
    |--------------------------------------------------------------------------
    | Default directory for your archived files
    |--------------------------------------------------------------------------
    |
    | By default, archived files are stored on the original source directory.
    | However, you may want to store your archived files in one specific
    | folder by default. Just set this value to that Path. This path
    | can also be overridden. See docs.
    |
    */

    'default_archives_directory' => env('ARCHIVED_FILES_DIR', null),

    /*
    |--------------------------------------------------------------------------
    | Zip library defaults
    |--------------------------------------------------------------------------
    |
    | Default Settings for the Zip library
    |
    */

    'zip' => [
        'extension' => '.zip'
    ],

];
