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

	public function home(Request $request){
		$tasks = Task::all();
		return response()->json($tasks);
	}

	public function recievedata(Request $request){
		print_r($request->all());
		return response() ->json([$request->all()]);
	}

	public function csv(Request $request){
		dd("nenga muti beb");
	}

	public function dbnames(Request $request){
		//$columns = Schema::Connection('todo')->getColumnListing('tasks'); // 'business' is your database connection
		/*$list_of_names_in_db = [];

		$crud_names = [];

		$tables = ['debtors','case_files','branches','employers','guarantors','phones','emails','next_of_kins']; //,

		$names = ['loan_amount','contract_no','loan_taken_date','loan_due_date',
							 'account_no','branch_title','full_name',
							 'employee_email','employee_name','nok_name','nok_phone',
							 'nok_address','nok_email','nok_contacts','gua_name','gua_email','gua_address'
						 ];

		foreach($tables as $table) {
						 array_push($crud_names, Schema::getColumnListing($table));

			}

		$crud_names = Arr::collapse($crud_names);

		foreach($crud_names as $name) {
		    if(in_array($name,$names)) {
		        array_push($list_of_names_in_db, $name);
		    }
		} */

		//This is just but a list of columns that are to be matched
		$list_of_names_in_db = ['Full Name','Loan Amount','Contract No','Loan Taken Date','Loan Due Date',
							 'Account No','Branch Title',
							 'Employee Email','Employee Name','NOK Name','NOK phone',
							 'NOK Address','NOK Email','NOK Contacts','GUA Name','GUA Email','GUA Address'
						 ];

		return response()->json($list_of_names_in_db);
	}

	public function update(Request $request, $id){
		$task = Task::find($id);
		$task->update($request->all());

		return response()->json("updated");
	}

	public function delete($id){
		$task = Task::find($id);

		broadcast(new TaskRemoved($task));

		Task::destroy($id);


		return response()->json("deleted");
	}
}
