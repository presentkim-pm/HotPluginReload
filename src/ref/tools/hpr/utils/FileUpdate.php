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

use pocketmine\utils\RegistryTrait;

/**
 * @method static FileUpdate MODIFIED()
 * @method static FileUpdate DELETED()
 * @method static FileUpdate CREATED()
 */
final class FileUpdate{
    use RegistryTrait;

    protected static function setup() : void{
        self::register(new self("modified"));
        self::register(new self("deleted"));
        self::register(new self("created"));
    }

    private static function register(self $fileUpdate) : void{
        self::_registryRegister($fileUpdate->name, $fileUpdate);
    }

    private function __construct(
        private string $name
    ){
    }
}
