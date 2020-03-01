<?php

namespace nick;

use pocketmine\Player;
use pocketmine\Server;
use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\command\Command;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\command\CommandSender;
use pocketmine\utils\Config;

class Main extends PluginBase implements Listener {
	
	private $nickcfg;
	
	public function onEnable(){
		$this->nickcfg = new Config($this->getDataFolder() . "nicks.yml", Config::YAML);
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
	}
	
	public function onQuit(PlayerQuitEvent $ev){
		$player = $ev->getPlayer();
		
		if(!$this->nickcfg->exists($player->getName())){
			return true;
		}
		
		if($this->nickcfg->exists($player->getName())){
			$this->nickcfg->remove($player->getName());
			$this->nickcfg->save();
			return true;
		}
	}
	
	public function onCommand(CommandSender $sender, Command $cmd, string $label, array $args): bool {
		
		switch($cmd->getName()){
			case "nick":
			if(!($sender->hasPermission("danuroyt.nick"))){
				$sender->sendMessage("§cYou don't have permission");
				return true;
			}
			if(!($sender instanceof Player)){
				$sender->sendMessage("§cThis command can be run only in game");
				return true;
			}
			$form = $this->getServer()->getPluginManager()->getPlugin("FormAPI")->createSimpleForm(function (Player $player, $data = null){
				$result = $data;
				if($result === null){
					return true;
				}
				switch($result){
					case 0;
					$this->changenick($player);
					break;
					case 1;
					if(!$this->nickcfg->exists($player->getName())){
						$player->sendMessage("§cSorry! Your nickname is already reset");
						return true;
					}
					if($this->nickcfg->exists($player->getName())){
						$player->setNameTag($this->nickcfg->getNested($player->getName() . ".normal name"));
						$player->setDisplayName($this->nickcfg->getNested($player->getName() . ".normal name"));
						$this->nickcfg->remove($player->getName());
						$this->nickcfg->save();
						$player->sendMessage("§6Your nickname has been reset");
						return true;
					}
					break;
				}
			});
			$form->setTitle("/Nick");
			if($this->nickcfg->exists($sender->getName())){
			$form->setContent(".§eYour nickname is " . $this->nickcfg->getNested($sender->getName() . ".custom name"));
			}
			if(!$this->nickcfg->exists($sender->getName())){
			$form->setContent("§7Click on change nickname to change your nickname or reset nickname to reset it. ");
			}
			$form->addButton("§aChange Nickname");
			$form->addButton("§cReset Nickname");
			$form->sendToPlayer($sender);
			break;
		}
		
		return true;
	}
	
	public function changenick($player){
		$form = $this->getServer()->getPluginManager()->getPlugin("FormAPI")->createCustomForm(function (Player $player, $data = null){
				$result = $data;
				if($result === null){
					return true;
				}
				if($result != null){
					$this->nickcfg->setNested($player->getName() . ".custom name", $data[0]);
					$this->nickcfg->setNested($player->getName() . ".normal name", $player->getName());
					$this->nickcfg->save();
					$player->setDisplayName($this->nickcfg->getNested($player->getName() . ".custom name"));
					$player->setNameTag($this->nickcfg->getNested($player->getName() . ".custom name"));
					$player->sendMessage("§6Your name is now §a" . $this->nickcfg->getNested($player->getName() . ".custom name"). "§e!");
					return true;
				}
		});
		$form->setTitle("/nick");
		$form->addInput("§7Please Note: Any inappropriate names you will be banned for!\n§7You can use colors by using § \n\n§6> §7Nickname");
		$form->sendToPlayer($player);
		return $form;
	}
	
	
}
