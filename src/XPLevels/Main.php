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

        if ($command->getName() !== "xp") {
            return false;
        }

        $msg = $this->getConfig()->get("messages");

        if (!$sender->hasPermission("xplevels.use")) {
            $sender->sendMessage($msg["no-permission"]);
            return true;
        }

        if (count($args) < 2) {
            $sender->sendMessage($msg["usage"]);
            return true;
        }

        $sub = strtolower($args[0]);
        $target = $this->getServer()->getPlayerExact($args[1]);

        if (!$target instanceof Player) {
            $sender->sendMessage($msg["player-not-found"]);
            return true;
        }

        $xpManager = $target->getXpManager();

        // ---------------- SEE ----------------
        if ($sub === "see") {
            $totalXp = $xpManager->getCurrentTotalXp();
            $sender->sendMessage(str_replace(
                ["{player}", "{amount}"],
                [$target->getName(), (string)$totalXp],
                $msg["sender-see"]
            ));
            return true;
        }

        // All others require amount
        if (count($args) < 3 || !is_numeric($args[2]) || (int)$args[2] < 0) {
            $sender->sendMessage($msg["invalid-amount"]);
            return true;
        }

        $amount = (int)$args[2];

        switch ($sub) {

            case "add":
                $xpManager->addXp($amount);

                $sender->sendMessage(str_replace(
                    ["{amount}", "{player}"],
                    [(string)$amount, $target->getName()],
                    $msg["sender-add"]
                ));

                $target->sendMessage(str_replace(
                    "{amount}",
                    (string)$amount,
                    $msg["target-add"]
                ));
                break;

            case "remove":
                $current = $xpManager->getCurrentTotalXp();
                $newXp = max(0, $current - $amount);
                $xpManager->setCurrentTotalXp($newXp);

                $sender->sendMessage(str_replace(
                    ["{amount}", "{player}"],
                    [(string)$amount, $target->getName()],
                    $msg["sender-remove"]
                ));

                $target->sendMessage(str_replace(
                    "{amount}",
                    (string)$amount,
                    $msg["target-remove"]
                ));
                break;

            case "set":
                $xpManager->setCurrentTotalXp($amount);

                $sender->sendMessage(str_replace(
                    ["{amount}", "{player}"],
                    [(string)$amount, $target->getName()],
                    $msg["sender-set"]
                ));

                $target->sendMessage(str_replace(
                    "{amount}",
                    (string)$amount,
                    $msg["target-set"]
                ));
                break;

            default:
                $sender->sendMessage($msg["usage"]);
                break;
        }

        return true;
    }
}
