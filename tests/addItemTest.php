<?php

use App\controller\addItemController;
use PHPUnit\Framework\TestCase;

class addItemTest extends TestCase
{
    private $addItem;

    protected function setUp(): void
    {
        $this->addItem = new addItemController();

    }

    public function testValidEmailInputReturnsTrue()
    {
        $this->assertEquals(1,$this->addItem->isEmail('john.doe@example.com'));
    }

    public function testInvalidEmailInputReturnsFalse()
    {
        $this->assertEquals(0, $this->addItem->isEmail('invalid email'));
    }
}