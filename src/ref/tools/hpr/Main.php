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
use pocketmine\snooze\SleeperNotifier;
use pocketmine\utils\Internet;
use ref\tools\hpr\thread\DirectoryWatchThread;
use ref\tools\hpr\utils\FileUpdate;

use function igbinary_unserialize;

const LOCALHOST = "127.0.0.1";

final class Main extends PluginBase{

    private DirectoryWatchThread $thread;

    protected function onEnable() : void{
        $server = $this->getServer();
        $notifier = new SleeperNotifier();
        $this->thread = new DirectoryWatchThread($server->getPluginPath(), $notifier);
        $this->thread->start(PTHREADS_INHERIT_NONE);
        $server->getTickSleeper()->addNotifier($notifier, function() use ($server) : void{
            $updatedFiles = igbinary_unserialize($this->thread->getSerializedFiles());

            $this->thread->stop();
            unset($this->thread);

            $logger = $this->getLogger();
            $logger->info("Plugin file changes was detected...");


            /**
             * @var string $pathname updated file path
             * @var string $update file update type
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

            // Just try reconnect players to server
            $ip = Internet::getIP();
            $port = $server->getPort();
            foreach($server->getOnlinePlayers() as $player){
                $player->transfer(
                    address: $player->getNetworkSession()->getIp() === LOCALHOST ? LOCALHOST : $ip,
                    port: $port
                );
            }
        });
    }

    protected function onDisable() : void{
        if(isset($this->thread)){
            $this->thread->stop();
        }
    }
}