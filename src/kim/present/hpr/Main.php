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

namespace kim\present\hpr;

use kim\present\hpr\thread\DirectoryWatchThread;
use kim\present\hpr\utils\FileUpdate;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Internet;

use function igbinary_unserialize;

const LOCALHOST = "127.0.0.1";

final class Main extends PluginBase{

	private DirectoryWatchThread $thread;

	private int $notifierId;

	protected function onEnable() : void{
		$server = $this->getServer();

		$sleeperHandlerEntry = $server->getTickSleeper()->addNotifier(function() use ($server) : void{
			$updatedFiles = igbinary_unserialize($this->thread->getSerializedFiles());

			$this->thread->shutdown();
			unset($this->thread);

			$logger = $this->getLogger();
			$logger->notice("Plugin file changes have been detected...");

			/**
			 * @var string     $pathname updated file path
			 * @var FileUpdate $update   file update type
			 */
			foreach($updatedFiles as $pathname => $update){
				switch($update){
					case FileUpdate::CREATED:
						$logger->debug("+ created: $pathname");
						break;
					case FileUpdate::MODIFIED:
						$logger->debug("= modified: $pathname");
						break;
					case FileUpdate::DELETED:
						$logger->debug("- deleted: $pathname");
						break;
				}
			}
			$server->shutdown();

			//reconnect players to server
			$ip = Internet::getIP();
			$port = $server->getPort();
			foreach($server->getOnlinePlayers() as $player){
				$player->transfer(
					address: $player->getNetworkSession()->getIp() === LOCALHOST ? LOCALHOST : $ip,
					port: $port
				);
			}
		});

		$this->notifierId = $sleeperHandlerEntry->getNotifierId();
		$this->thread = new DirectoryWatchThread($server->getPluginPath(), $sleeperHandlerEntry);
		$this->thread->start();
	}

	protected function onDisable() : void{
		if(isset($this->thread)){
			$this->thread->shutdown();
		}
		if($this->notifierId !== -1){
			$this->getServer()->getTickSleeper()->removeNotifier($this->notifierId);
		}
	}
}
