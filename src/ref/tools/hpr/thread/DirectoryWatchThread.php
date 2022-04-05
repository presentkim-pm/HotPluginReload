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

namespace ref\tools\hpr\thread;

use pocketmine\snooze\SleeperNotifier;
use pocketmine\thread\Thread;
use ref\tools\hpr\utils\FileHash;
use ref\tools\hpr\utils\FileUpdate;

use function count;
use function igbinary_serialize;

final class DirectoryWatchThread extends Thread{

    private string $serializedFiles = "";

    private bool $running = true;

    public function __construct(private string $path, private SleeperNotifier $notifier){
    }

    protected function onRun() : void{
        $originHashes = FileHash::dir($this->path);

        /** @var array<string, FileUpdate> $updatedFiles */
        $updatedFiles = [];
        while($this->running){
            $currentHashes = FileHash::dir($this->path);
            // Check if a file has been deleted or modified
            foreach($originHashes as $pathname => $hash){
                if(!isset($currentHashes[$pathname])){
                    $updatedFiles[$pathname] = FileUpdate::DELETED();
                }elseif($currentHashes[$pathname] !== $hash){
                    $updatedFiles[$pathname] = FileUpdate::MODIFIED();
                }
            }
            // Check if a new file has been created
            foreach($currentHashes as $pathname => $time){
                if(!isset($originHashes[$pathname])){
                    $updatedFiles[$pathname] = FileUpdate::CREATED();
                }
            }
            if(count($updatedFiles) > 0){
                $this->serializedFiles = igbinary_serialize($updatedFiles);
                $this->notifier->wakeupSleeper();
                break;
            }
        }
    }

    public function stop() : void{
        if($this->isJoined()){
            return;
        }
        $this->synchronized(function() : void{
            $this->running = false;
        });
        $this->quit();
    }

    public function getSerializedFiles() : string{
        return $this->serializedFiles;
    }
}