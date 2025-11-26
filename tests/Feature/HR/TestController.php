<?php

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class TestController extends Controller
{
    use AuthorizesRequests;

    public function test()
    {
        $this->authorize('test', new \stdClass);

        return 'ok';
    }
}
