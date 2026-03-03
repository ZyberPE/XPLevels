<?php

declare(strict_types=1);

namespace XPLevels;

use pocketmine\plugin\PluginBase;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;

class Main extends PluginBase {

    public function onEnable(): void {
        $this->saveDefaultConfig();

        // Remove vanilla /xp command
        $cmdMap = $this->getServer()->getCommandMap();
        $vanilla = $cmdMap->getCommand("xp");
        if ($vanilla !== null) {
            $cmdMap->unregister($vanilla);
        }

        // Register our /xp command manually
        $cmdMap->register("xplevels", new class($this) extends Command {

            private Main $plugin;

            public function __construct(Main $plugin){
                parent::__construct("xp", "Manage player XP levels", "/xp <add|remove|set|see> <player> [amount]");
                $this->setPermission("xplevels.use");
                $this->plugin = $plugin;
            }

            public function execute(CommandSender $sender, string $label, array $args): bool {

                $msg = $this->plugin->getConfig()->get("messages");

                if(!$this->testPermission($sender)){
                    $sender->sendMessage($msg["no-permission"]);
                    return true;
                }

                if(count($args) < 2){
                    $sender->sendMessage($msg["usage"]);
                    return true;
                }

                $sub = strtolower($args[0]);
                $target = $this->plugin->getServer()->getPlayerExact($args[1]);

                if(!$target instanceof Player){
                    $sender->sendMessage($msg["player-not-found"]);
                    return true;
                }

                $xp = $target->getXpManager();

                if($sub === "see"){
                    $sender->sendMessage(str_replace(
                        ["{player}", "{amount}"],
                        [$target->getName(), (string)$xp->getXpLevel()],
                        $msg["sender-see"]
                    ));
                    return true;
                }

                if(count($args) < 3 || !is_numeric($args[2]) || (int)$args[2] < 0){
                    $sender->sendMessage($msg["invalid-amount"]);
                    return true;
                }

                $amount = (int)$args[2];

                switch($sub){

                    case "add":
                        $xp->addXpLevels($amount);
                        break;

                    case "remove":
                        $new = max(0, $xp->getXpLevel() - $amount);
                        $xp->setXpLevel($new);
                        break;

                    case "set":
                        $xp->setXpLevel($amount);
                        break;

                    default:
                        $sender->sendMessage($msg["usage"]);
                        return true;
                }

                $sender->sendMessage("§aXP command executed successfully.");
                return true;
            }
        });
    }
}
