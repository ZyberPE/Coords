<?php

namespace Coords;

use pocketmine\plugin\PluginBase;
use pocketmine\player\Player;
use pocketmine\scheduler\ClosureTask;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;

class Main extends PluginBase{

    private array $enabled = [];

    public function onEnable(): void{
        $this->saveDefaultConfig();

        $interval = $this->getConfig()->get("update-interval");

        $this->getScheduler()->scheduleRepeatingTask(new ClosureTask(function() : void{
            foreach($this->enabled as $name => $bool){
                $player = $this->getServer()->getPlayerExact($name);

                if($player instanceof Player && $bool){
                    $pos = $player->getPosition();

                    $format = $this->getConfig()->get("coords-format");

                    $text = str_replace(
                        ["{x}","{y}","{z}"],
                        [round($pos->getX(),1), round($pos->getY(),1), round($pos->getZ(),1)],
                        $format
                    );

                    $player->sendActionBarMessage($text);
                }
            }
        }), $interval);
    }

    public function onCommand(CommandSender $sender, Command $command, string $label, array $args): bool{

        if(!$sender instanceof Player){
            return true;
        }

        if(!isset($args[0])){
            $sender->sendMessage($this->getConfig()->get("messages")["usage"]);
            return true;
        }

        switch(strtolower($args[0])){

            case "on":
                $this->enabled[$sender->getName()] = true;
                $sender->sendMessage($this->getConfig()->get("messages")["enabled"]);
            break;

            case "off":
                unset($this->enabled[$sender->getName()]);
                $sender->sendMessage($this->getConfig()->get("messages")["disabled"]);
            break;

            default:
                $sender->sendMessage($this->getConfig()->get("messages")["usage"]);
            break;
        }

        return true;
    }
}
