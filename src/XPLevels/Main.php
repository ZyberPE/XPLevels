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
    }

    public function onCommand(CommandSender $sender, Command $command, string $label, array $args): bool {

        // Block namespaced usage like xplevels:xp
        if (str_contains($label, ":")) {
            return false;
        }

        if ($command->getName() !== "xp") {
            return false;
        }

        $config = $this->getConfig()->get("messages");

        if (!$sender->hasPermission("xplevels.command")) {
            $sender->sendMessage($config["no-permission"]);
            return true;
        }

        if (!isset($args[0], $args[1], $args[2])) {
            $sender->sendMessage($config["usage"]);
            return true;
        }

        $sub = strtolower($args[0]);
        $target = $this->getServer()->getPlayerExact($args[1]);

        if (!$target instanceof Player) {
            $sender->sendMessage($config["player-not-found"]);
            return true;
        }

        if (!is_numeric($args[2]) || (int)$args[2] < 0) {
            $sender->sendMessage($config["invalid-amount"]);
            return true;
        }

        $amount = (int)$args[2];
        $xpManager = $target->getXpManager();

        switch ($sub) {

            case "add":
                if ($amount <= 0) {
                    $sender->sendMessage($config["invalid-amount"]);
                    return true;
                }

                $xpManager->addXp($amount);

                $target->sendMessage(
                    str_replace("{amount}", (string)$amount, $config["received-add"])
                );

                $sender->sendMessage(
                    str_replace(
                        ["{amount}", "{player}"],
                        [(string)$amount, $target->getName()],
                        $config["sender-add"]
                    )
                );
                break;

            case "remove":
                if ($amount <= 0) {
                    $sender->sendMessage($config["invalid-amount"]);
                    return true;
                }

                $newXp = max(0, $xpManager->getXp() - $amount);
                $xpManager->setXp($newXp);

                $target->sendMessage(
                    str_replace("{amount}", (string)$amount, $config["received-remove"])
                );

                $sender->sendMessage(
                    str_replace(
                        ["{amount}", "{player}"],
                        [(string)$amount, $target->getName()],
                        $config["sender-remove"]
                    )
                );
                break;

            case "set":
                $xpManager->setXp($amount);

                $target->sendMessage(
                    str_replace("{amount}", (string)$amount, $config["received-set"])
                );

                $sender->sendMessage(
                    str_replace(
                        ["{amount}", "{player}"],
                        [(string)$amount, $target->getName()],
                        $config["sender-set"]
                    )
                );
                break;

            default:
                $sender->sendMessage($config["usage"]);
                break;
        }

        return true;
    }
}
