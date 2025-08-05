<?php
namespace lu\quicksell;

use pocketmine\plugin\PluginBase;
use pocketmine\utils\SingletonTrait;

class Main extends PluginBase {
    use SingletonTrait;

    public function onLoad(): void {
        self::setInstance($this);
    }

    public function onEnable(): void {
        $this->saveResource("config.yml");
        $this->getLogger()->info("[QuickSell] Chargé by Root PM Shop");
        $this->getServer()->getCommandMap()->register("quicksell", new commands\QuickSellCommand());
    }

    /**
     * Récupère la liste des items vendables et leur prix
     * @return array<string, int>
     */
    public function getSellPrices(): array {
        return $this->getConfig()->get("items", []);
    }

    /**
     * Récupère la config économie
     * @return array<string, bool>
     */
    public function getEconomyConfig(): array {
        return $this->getConfig()->get("economy", []);
    }
}
