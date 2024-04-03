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
 * @author       ref-team
 * @link         https://github.com/refteams
 *
 *  &   ／l、
 *    （ﾟ､ ｡ ７
 *   　\、ﾞ ~ヽ   *
 *   　じしf_, )ノ
 *
 * @noinspection PhpUnused
 */

declare(strict_types=1);

namespace kim\present\hpr\utils;

use Symfony\Component\Filesystem\Path;

use function file_exists;
use function hash_file;
use function in_array;
use function is_dir;
use function is_file;
use function scandir;
use function str_ends_with;
use function str_starts_with;

final class FileHash{

    private function __construct(){
        //NOOP
    }

    public static function file(string $filePath) : string{
        if(!file_exists($filePath) || !is_file($filePath)){
            return "";
        }
        try{
            return hash_file("sha256", $filePath);
        }catch(\Throwable){
            return "";
        }
    }

    public static function dir(string $dir, array $result = []) : array{
        if(!file_exists($dir) || !is_dir($dir)){
            return [];
        }

        foreach(scandir($dir) as $innerPath){
            if(
                in_array($innerPath, [".", ".."])
                || //skip dot inodes
                str_starts_with($innerPath, ".")
                || //skip hidden files
                str_ends_with($innerPath, "~")  //skip backup files
            ){
                continue;
            }

            $fullPath = Path::join($dir, $innerPath);
            if(is_file($fullPath)){
                $result[$fullPath] = self::file($fullPath);
            }else{
                $result = self::dir($fullPath, $result);
            }
        }
        return $result;
    }
}
