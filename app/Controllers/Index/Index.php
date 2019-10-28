<?php
namespace App\Controllers\Index;
use \Core\Framework\Controller;
use \Core\Framework\View;
class Index extends Controller
{
    public function welcome() 
    {
        return 'hello SWAT!';
    }

}