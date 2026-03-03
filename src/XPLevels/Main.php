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

        // Block namespaced command usage
        if (str_contains($label, ":")) {
            $sender->sendMessage("§cUse /xp only.");
            return true;
        }

        if ($command->getName() !== "xp") {
            return false;
        }

        $msg = $this->getConfig()->get("messages");

        if (!$sender->hasPermission("xplevels.use")) {
            $sender->sendMessage($msg["no-permission"]);
            return true;
        }

        if (!isset($args[0]) || !isset($args[1])) {
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

        switch ($sub) {

            case "see":
                $sender->sendMessage(
                    str_replace(
                        ["{player}", "{amount}"],
                        [$target->getName(), (string)$xpManager->getXp()],
                        $msg["see-message"]
                    )
                );
                return true;

            case "add":
            case "remove":
            case "set":

                if (!isset($args[2]) || !is_numeric($args[2]) || (int)$args[2] < 0) {
                    $sender->sendMessage($msg["invalid-amount"]);
                    return true;
                }

                $amount = (int)$args[2];

                switch ($sub) {

                    case "add":
                        $xpManager->addXp($amount);
                        $target->sendMessage(str_replace("{amount}", (string)$amount, $msg["received-add"]));
                        $sender->sendMessage(str_replace(
                            ["{amount}", "{player}"],
                            [(string)$amount, $target->getName()],
                            $msg["sender-add"]
                        ));
                        break;

                    case "remove":
                        $newXp = max(0, $xpManager->getXp() - $amount);
                        $xpManager->setXp($newXp);
                        $target->sendMessage(str_replace("{amount}", (string)$amount, $msg["received-remove"]));
                        $sender->sendMessage(str_replace(
                            ["{amount}", "{player}"],
                            [(string)$amount, $target->getName()],
                            $msg["sender-remove"]
                        ));
                        break;

                    case "set":
                        $xpManager->setXp($amount);
                        $target->sendMessage(str_replace("{amount}", (string)$amount, $msg["received-set"]));
                        $sender->sendMessage(str_replace(
                            ["{amount}", "{player}"],
                            [(string)$amount, $target->getName()],
                            $msg["sender-set"]
                        ));
                        break;
                }

                return true;
        }

        $sender->sendMessage($msg["usage"]);
        return true;
    }
}
