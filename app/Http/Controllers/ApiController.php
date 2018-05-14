<?php

namespace App\Http\Controllers;

use App\Classes\Utils;

class ApiController extends Controller
{

    public function __construct(Utils $dao)
    {
        $this->dao = $dao;
    }

    public function fetchPosts()
    {

        dd($this->dao->handle());

    }

}
