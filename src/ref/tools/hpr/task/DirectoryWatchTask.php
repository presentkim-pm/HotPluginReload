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

namespace ref\tools\hpr\task;

use Closure;
use http\Exception\InvalidArgumentException;
use ref\tools\hpr\utils\FileHash;
use ref\tools\hpr\utils\FileUpdate;
use pocketmine\scheduler\AsyncTask;
use pocketmine\utils\Utils;
use Webmozart\PathUtil\Path;

use function count;
use function file_exists;
use function is_dir;
use function usleep;

final class DirectoryWatchTask extends AsyncTask{
    private const DEFAULT_INTERVAL = 500000; // 0.5s

    /** @var string Watching directory path */
    private string $path;

    /**
     * @var Closure $onUpdated
     * @phpstan-var (Closure(array<string, ModifyType> $updatedFiles) : void) $onUpdated
     */
    private Closure $onUpdated;

    /** @var int Directory scan interval [unit: micro seconds] */
    private int $interval;

    public function __construct(string $path, Closure $onUpdated, int $interval = self::DEFAULT_INTERVAL){
        $this->path = Path::canonicalize($path);
        if(!file_exists($this->path) || !is_dir($this->path)){
            throw new InvalidArgumentException("$path is not a valid path");
        }

        $this->onUpdated = $onUpdated;
        Utils::validateCallableSignature(static function(array $updatedFiles) : void{ }, $this->onUpdated);

        $this->interval = $interval;
        if($this->interval < 1){
            throw new InvalidArgumentException("Interval must be greater than 0, given $interval");
        }
    }

    /** Start watching of the directory */
    public function onRun() : void{
        $originHashes = FileHash::dir($this->path);

        /** @var array<string, FileUpdate> $updatedFiles */
        $updatedFiles = [];
        while(true){
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
                $this->setResult($updatedFiles);
                break;
            }
            if($this->isTerminated() || $this->hasCancelledRun()){
                break;
            }
            usleep($this->interval);
        }
    }

    public function onCompletion() : void{
        if($this->hasResult()){
            ($this->onUpdated)($this->getResult());
        }
    }
}