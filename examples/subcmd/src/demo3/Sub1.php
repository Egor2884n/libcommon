<?php
namespace demo3;
use mf\common\BaseSubCmd;
use pocketmine\plugin\PluginBase;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;


class Sub1 extends BaseSubCmd {
  public static function defaults() {
    return [ "one" => 1, "two" => 2 ];
  }  
  public function __construct(PluginBase $plugin, array $cfg = []) {
    parent::__construct($plugin,$cfg);
    $plugin->getLogger()->info("Enabling subcmd module1");
  }
  public function getMainCmd() { return "xl1"; }
  public function getName() { return "sub1"; }
  public function onCommand(CommandSender $sender, Command $command, $label, array $args) {
    $sender->sendMessage("Sub Command 1 being executed");
    return TRUE;
  }
}

