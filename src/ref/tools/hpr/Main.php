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

namespace ref\tools\hpr;

use pocketmine\plugin\PluginBase;
use ref\tools\hpr\task\DirectoryWatchTask;
use ref\tools\hpr\utils\FileUpdate;

final class Main extends PluginBase{
    private ?DirectoryWatchTask $watcher = null;

    protected function onEnable() : void{
        $server = $this->getServer();
        $this->watcher = new DirectoryWatchTask($server->getPluginPath(), function(array $updatedFiles) use ($server) : void{
            $logger = $this->getLogger();
            $logger->info("Plugin file changes was detected...");
            /**
             * @var string     $pathname updated file path
             * @var FileUpdate $update file update type
             */
            foreach($updatedFiles as $pathname => $update){
                switch($update){
                    case FileUpdate::CREATED():
                        $logger->debug("+ created: $pathname");
                        break;
                    case FileUpdate::MODIFIED():
                        $logger->debug("= modified: $pathname");
                        break;
                    case FileUpdate::DELETED():
                        $logger->debug("- deleted: $pathname");
                        break;
                }
            }
            $server->shutdown();
        });
        $server->getAsyncPool()->submitTask($this->watcher);
    }

    protected function onDisable() : void{
        $this->watcher?->cancelRun();
    }
}