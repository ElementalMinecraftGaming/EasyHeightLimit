<?php

namespace ElementalMinecraftGaming\EasyHeightLimit;

use pocketmine\event\block\{BlockPlaceEvent};
use pocketmine\CommandReader;
use pocketmine\CommandExecuter;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\utils\Config;
use pocketmine\event\Listener;
use pocketmine\command\utils\InvalidCommandSyntaxException;
use pocketmine\event\TranslationContainer;
use pocketmine\utils\TextFormat;
use pocketmine\level\{Level,Position,ChunkManager};
use pocketmine\Server;
use pocketmine\permission\PermissibleBase;

class Main extends PluginBase implements Listener {
    
    private $config;
    
    public function onEnable(){
        @mkdir($this->getDataFolder());
        $this->config = new Config($this->getDataFolder() . "config.yml", Config::YAML, array());
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
    }
    
    public function onBlockPlace(BlockPlaceEvent $event) {
        $user = $event->getPlayer();
        $lvl = $user->getLevel()->getFolderName();
        $block = $event->getBlock();
        
        $data = $this->config->get($lvl);
        $y = $block->y;
        if (isset($data)) {
            if (is_array($data)) {
                $limit = $data[0];
                if ($y > $limit) {
                    if ($user->hasPermission("hlimit.bypass")) {
                        return true;
                    } else {
                        $user->sendMessage(TextFormat::RED . "Limit reached");
                        $event->setCancelled();
                    }
                } 
            }
        }
        return false;
    }

    public function onCommand(CommandSender $sender, Command $command, string $label, array $args): bool {
        if (strtolower($command->getName()) == "heightlimit") {
            if ($sender->hasPermission("edit.hlimit")) {
                if ($sender instanceof Player) {
                    if (isset($args[0])) {
                        if (isset($args[1])) {
                        $sender->sendMessage(TextFormat::GREEN . "Setting limit");
                        $worldd = $args[1];
                        $y = $args[0];
                        
                        $this->config->set($worldd, [$y]);
                        $this->config->save();
                        $sender->sendMessage(TextFormat::GREEN . "Set!");
                        return true;
                        } else {
                        $sender->sendMessage(TextFormat::RED . "Missing world!");
                    }
                    } else {
                        $sender->sendMessage(TextFormat::RED . "Missing height!");
                    }
                } else {
                    $sender->sendMessage(TextFormat::RED . "Must run in game!");
                }
            } else {
                $sender->sendMessage(TextFormat::RED . "No perms!");
                return false;
            }
        }

		if (strtolower($command->getName()) == "heightlimithelp") {
            if ($sender->hasPermission("hlimit.help")) {
                if ($sender instanceof Player) {
                        $sender->sendMessage(TextFormat::GREEN . "§l§c[§aEasy§2Height§6Limit§c]\n\n§r§e/lhl {world} {y} - Set height limit\n/edhl {world} - Delete height limit from world\n/limithh - Help!");
                        return true;
                } else {
                    $sender->sendMessage(TextFormat::RED . "Must run in game!");
                }
            } else {
                $sender->sendMessage(TextFormat::RED . "No perms!");
                return false;
            }
        } 

        if (strtolower($command->getName()) == "disableheightlimit") {
            if ($sender->hasPermission("edit.dhlimit")) {
                if ($sender instanceof Player) {
                    if (isset($args[0])) {
                        $world = $args[0];
                        
                        unset($this->config->$world);
                        $this->config->save(true);
                        $sender->sendMessage(TextFormat::GREEN . "Deleted $world height limit! ");
                        return true;
                        } else {
                    $sender->sendMessage(TextFormat::RED . "World name required!");
                }
                } else {
                    $sender->sendMessage(TextFormat::RED . "Must be in game!");
                }
            } else {
                $sender->sendMessage(TextFormat::RED . "No perms!");
                return false;
            }
        }
        return false;
    }

}
