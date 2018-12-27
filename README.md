[![Build Status](https://travis-ci.org/samkitano/kompressor.svg?branch=master)](https://travis-ci.org/samkitano/Kompressor)
# An archive utility for Laravel 5.7

Kompressor is a simplified wrapper for PHP archive libraries, such as Zip,
to easily manage archiving in Laravel.

## REQUIREMENTS
- PHP >= 7.1.3
- Laravel 5.7.*
- PHP [Zip extension](https://secure.php.net/manual/en/book.zip.php)

## INSTALLATION
```bash
composer require samkitano/kompressor
```

## CONFIGURATION
Publish configuration (optional):

```bash
php artisan vendor:publish --tag=kompressor
```

The configuration file contains detailed descriptions of all and each entry.

## AVAILABLE ARCHIVING TOOLS
At this time, only Zip is available. Feel free to implement your own archive tool.

## USAGE
### Compress file(s)

```php
Kompressor::compress(
    $files,
    $destination = null,
    $name = null,
    $delete = false,
    $pattern = null
);
```

***Arguments***:

- **$files** [array|string] The file(s) or directory to archive
   - **required**
   - Must contain fully qualified path(s).
   - Can be a directory, a single file, or an array of files.
   - Multiple files must be on the same directory.
   - Multiple source directories are not allowed.
   - Mixed sources (directories and files) are not allowed.

- **$destination** [string] The destination path
   - **optional**
   - Overrides default configuration 'default_archives_directory'
   - If omitted, archive will be created on the default configuration.
   - If omitted and default configuration is not set, archive will be created on source directory.
   - Must be a fully qualified path.
   - Must exist.

- **$name** [string] The name for the archive file
   - **optional**
   - Default is source file(s) directory name.
   - No need to include extension.

- **$delete** [boolean] Mark the source file(s) for deletion after archiving
   - **optional**
   - Default value is false.
   
- **$pattern** [string] The *glob* search pattern for archiving directories
   - **optional**
   - Overrides default configuration 'glob_dir_pattern'
   - Default is '*'

### Extract files

```php
Kompressor::extract(
    $source,
    $destination = null,
    $delete = false,
    $files = []
);
```
***Arguments***:

- **$source** [string] The full path to the archive
   - **required**
   - Must be a fully qualified path.
   - Archive must exist.

- **$destination** [string] The destination path
   - **optional**
   - If omitted, files will be extracted on the same archive directory.
   - Must be a fully qualified path.
   - Must exist.

- **$delete** [boolean] Mark the archive file for deletion after extraction
   - **optional**
   - Default value is false.

- **$files** [array] The specific file(s) to extract
   - **optional**
   - If omitted, all files will be extracted.
   - Must be an array of file names.
   - Must exist on archive.

## Read an archive
```php
Kompressor::read(
    $source
);
```
***Arguments***:

- **$source** [string] The full path to the archive
   - **required**
   - Must be a fully qualified path.
   - Archive must exist.

Returns an array with the file names contained in the archive.

## Add files into an archive

```php
Kompressor::add(
    $source,
    $files,
    $delete = false
);
```

***Arguments***

- **$source** [string] The full path to the archive
   - **required**
   - Must be a fully qualified path.
   - Archive must exist.

- **$files** [array|string] The file(s) or directory to add to the archive
   - **required**
   - Must be a single file, or an array of files.
   - Multiple files must be on the same directory.
   - Multiple source directories are not allowed.
   - Mixed sources (directories and files) are not allowed.

- **$delete** [boolean] Mark the source file(s) for deletion after adding to archive
   - **optional**
   - Default value is false.

### Remove files from archive

```php
Kompressor::remove(
    $source,
    $files
);
```

***Arguments***

- **$source** [string] The full path to the archive
   - **required**
   - Must be a fully qualified path.
   - Archive must exist.

- **$files** [array|string] The file(s) to remove from the archive
   - **required**
   - Must be either a single file, or an array of files.
   - Multiple files must be on the same directory.
   - Multiple source directories are not allowed.
   - Mixed sources (directories and files) are not allowed.

## ADDING SUPPORT FOR OTHER LIBRARIES
1 - Create a class within ```libraries/```

- Class and file names **must** match the library name.
- Class **must** extend the *BaseCompression* class.
- Class **must** implement the *CompressionContract* interface.

For example, should you wish to implement the *gzip* library, class name should be **Gzip**, and file name should be **Gzip.php**

```php
<?php

namespace sammy\Kompressor\Libraries;

class Gzip extends BaseCompression implements CompressionContract
{
    //
}
```

2 - Implement contract methods, and do your thing.

## LICENSE
This package is open-source software, licensed under the [MIT license](https://opensource.org/licenses/MIT)
