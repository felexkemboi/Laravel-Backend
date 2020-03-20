<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Schema;
use Illuminate\Http\Request;
use App\Events\TaskCreated;
use App\Events\TaskRemoved;
use App\Task;
use \Illuminate\Support\Arr;
//use App\Http\Controllers\Storage;
use Illuminate\Support\Facades\Storage;



class TaskController extends Controller
{
	//


	public function recievedata(Request $request){
		print_r($request->all());
		return response() ->json([$request->all()]);
	}


}
