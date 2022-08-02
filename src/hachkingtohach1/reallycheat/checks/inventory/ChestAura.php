<?php
/**
 *  Copyright (c) 2022 hachkingtohach1
 *
 *  Permission is hereby granted, free of charge, to any person obtaining a copy
 *  of this software and associated documentation files (the "Software"), to deal
 *  in the Software without restriction, including without limitation the rights
 *  to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 *  copies of the Software, and to permit persons to whom the Software is
 *  furnished to do so, subject to the following conditions:
 *
 *  The above copyright notice and this permission notice shall be included in all
 *  copies or substantial portions of the Software.
 *
 *  THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 *  IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 *  FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 *  AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 *  LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 *  OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 *  SOFTWARE.
 */
namespace hachkingtohach1\reallycheat\checks\inventory;

use hachkingtohach1\reallycheat\checks\Check;
use hachkingtohach1\reallycheat\player\RCPlayerAPI;
use pocketmine\event\Event;
use pocketmine\event\inventory\InventoryOpenEvent;
use pocketmine\event\inventory\InventoryCloseEvent;
use pocketmine\event\inventory\InventoryTransactionEvent;
use pocketmine\inventory\PlayerInventory;
use pocketmine\inventory\PlayerCraftingInventory;

class ChestAura extends Check{

    public function getName() :string{
        return "ChestAura";
    }
    
    public function getSubType() :string{
        return "A";
    }
    
    public function enable() :bool{
        return true;
    }

    public function ban() :bool{
        return false;
    }

    public function transfer() :bool{
        return true;
    }

    public function flag() :bool{
        return false;
    }

    public function captcha() :bool{
        return false;
    }

    public function maxViolations() :int{
        return 1;
    }

    public function checkEvent(Event $event, RCPlayerAPI $playerAPI) :void{
        $countTransaction = $playerAPI->getExternalData("countTransaction");
        $timeOpenChest = $playerAPI->getExternalData("timeOpenChest");
        if($event instanceof InventoryOpenEvent){
            if($timeOpenChest === null && !($event->getInventory() instanceof PlayerCraftingInventory)){
                $playerAPI->setExternalData("timeOpenChest", microtime(true));
            }
        }
        if($event instanceof InventoryCloseEvent){
            if($timeOpenChest !== null && $countTransaction !== null){
                $timeDiff = microtime(true) - $timeOpenChest;
                if($timeDiff < $countTransaction / 3){
                    $this->failed($playerAPI);
                }
                $playerAPI->unsetExternalData("timeOpenChest");
                $playerAPI->unsetExternalData("countTransaction");
            }
        }
        if($event instanceof InventoryTransactionEvent){
            $transaction = $event->getTransaction();
            foreach($transaction->getInventories() as $inventory){
                if($inventory instanceof PlayerInventory){
                    if($countTransaction !== null && $timeOpenChest !== null){
                        $playerAPI->setExternalData("countTransaction", $countTransaction + 1);
                    }else{
                        $playerAPI->setExternalData("countTransaction", 0);
                    }
                }
            }
        }
    }

}