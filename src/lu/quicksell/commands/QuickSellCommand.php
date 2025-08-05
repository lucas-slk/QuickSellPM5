<?php
namespace lu\quicksell\commands;

use cooldogedev\BedrockEconomy\api\BedrockEconomyAPI;
use lu\quicksell\Main;
use onebone\economyapi\EconomyAPI;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\item\StringToItemParser;
use pocketmine\item\VanillaItems;
use pocketmine\permission\DefaultPermissions;
use pocketmine\player\Player;

class QuickSellCommand extends Command {

    public function __construct() {
        parent::__construct("quicksell", "Vendre rapidement son inventaire.", "/quicksell");
        $this->setPermission(DefaultPermissions::ROOT_USER);
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): void {
        if (!$sender instanceof Player) {
            $sender->sendMessage("§cCette commande est réservée aux joueurs.");
            return;
        }

        $pricesConfig = Main::getInstance()->getSellPrices();
        $economy = Main::getInstance()->getEconomyConfig();

        $inventory = $sender->getInventory();
        $totalPrice = 0;
        $itemsToRemove = [];

        $parsedItems = [];
        foreach ($pricesConfig as $itemString => $price) {
            $parsedItem = StringToItemParser::getInstance()->parse($itemString);
            if ($parsedItem !== null) {
                $parsedItems[$itemString] = $parsedItem;
            }
        }

        foreach ($inventory->getContents() as $slot => $item) {
            foreach ($parsedItems as $itemString => $parsedItem) {
                if ($item->equals($parsedItem, true, false)) {
                    $price = $pricesConfig[$itemString];
                    $amount = $item->getCount();
                    $totalPrice += $price * $amount;
                    $itemsToRemove[] = $slot;
                    break;
                }
            }
        }

        if ($totalPrice <= 0) {
            $sender->sendMessage("§cTu n'as aucun item vendable dans ton inventaire.");
            return;
        }

        foreach ($itemsToRemove as $slot) {
            $inventory->setItem($slot, VanillaItems::AIR());
        }

        // TODO: Ajouter argent au joueur via économie (EconomyAPI, BedrockEconomy...)
        if($economy["economyapi"] === true){
            EconomyAPI::getInstance()->addMoney($sender, $totalPrice);
        }
        elseif($economy["bedrockeconomy"] === true){
            BedrockEconomyAPI::getInstance()->addToPlayerBalance($sender->getName(), $totalPrice);
        }

        $sender->sendMessage("§aTu as vendu tes items pour §e{$totalPrice}$");
    }

    private function getPlugin(): Main {
        return Main::getInstance();
    }
}
