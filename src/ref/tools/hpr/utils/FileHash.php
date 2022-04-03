<?php

/**            __   _____
 *  _ __ ___ / _| |_   _|__  __ _ _ __ ___
 * | '__/ _ \ |_    | |/ _ \/ _` | '_ ` _ \
 * | | |  __/  _|   | |  __/ (_| | | | | | |
 * |_|  \___|_|     |_|\___|\__,_|_| |_| |_|
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author  ref-team
 * @link    https://github.com/refteams
 *
 *  &   ／l、
 *    （ﾟ､ ｡ ７
 *   　\、ﾞ ~ヽ   *
 *   　じしf_, )ノ
 *
 * @noinspection PhpUnused
 */

declare(strict_types=1);

namespace ref\tools\hpr\utils;

use Webmozart\PathUtil\Path;

use function file_exists;
use function hash_file;
use function is_dir;
use function is_file;
use function scandir;
use function str_contains;

final class FileHash{
    private function __construct(){
        //NOOP
    }

    public static function file(string $filePath) : string{
        return is_file($filePath) ? hash_file("sha256", $filePath) : "";
    }

    public static function dir(string $dir, array $result = []) : array{
        if(!file_exists($dir) || !is_dir($dir)){
            return [];
        }

        foreach(array_diff(scandir($dir), [".", ".."]) as $innerPath){
            $fullPath = Path::join($dir, $innerPath);
            if(is_file($fullPath)){
                $result[$fullPath] = self::file($fullPath);
            }elseif(is_dir($fullPath)){
                if(str_contains($fullPath, "/.")){ //Skip directory names starting with '.'
                    continue;
                }

                $result = self::dir($fullPath, $result);
            }
        }
        return $result;
    }
}
