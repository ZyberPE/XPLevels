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

        $xp = $target->getXpManager();

        // -------- SEE --------
        if ($sub === "see") {
            $level = $xp->getXpLevel();
            $sender->sendMessage(str_replace(
                ["{player}", "{amount}"],
                [$target->getName(), (string)$level],
                $msg["sender-see"]
            ));
            return true;
        }

        // All other commands need amount
        if (count($args) < 3 || !is_numeric($args[2]) || (int)$args[2] < 0) {
            $sender->sendMessage($msg["invalid-amount"]);
            return true;
        }

        $amount = (int)$args[2];

        switch ($sub) {

            case "add":
                $xp->addXpLevels($amount);

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
                $current = $xp->getXpLevel();
                $new = max(0, $current - $amount);
                $xp->setXpLevel($new);

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
                $xp->setXpLevel($amount);

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
