<?php
require_once "vendor/autoload.php";

use PHPUnit\Framework\TestCase;
use Taro\User;

class UserTest extends TestCase
{
    public function testReturnsFullName()
    {
        # テスト対象を読み込む
        // require('User.php');

        $user = new User;

        $user->first_name = "Teresa";
        $user->surname = "Green";

        $this->assertEquals('Teresa Green', $user->getFullName());
    }
    public function testFullNameIsEmptyByDefault()
    {
        $user = new User;
        $this->assertEquals('', $user->getFullName());
    }

    /**
    * @test
    */
    public function user_has_first_name()
    {
        $user = new User;
        $user->first_name = "Teresa";
        $this->assertEquals('Teresa', $user->first_name);
    }
}
