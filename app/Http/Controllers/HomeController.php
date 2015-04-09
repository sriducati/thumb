<?php 

namespace App\Http\Controllers;


class HomeController extends Controller {

	/*
	|--------------------------------------------------------------------------
	| Home Controller
	|--------------------------------------------------------------------------
	|
	| This controller renders your application's "dashboard" for users that
	| are authenticated. Of course, you are free to change or remove the
	| controller as you wish. It is just here to get your app started!
	|
	*/

	/**
	 * Create a new controller instance.
	 *
	 * @return void
	 */
	/*public function __construct()
	{
		$this->middleware('auth');
	}*/

	/**
	 * Show the application dashboard to the user.
	 *
	 * @return Response
	 */
	public function index()
	{
		

		\Session::put('postal_code',"460092" );

		$sp_info = \DB::table('sp_user_categories')
		->join('sp_service_info','sp_service_info.SP_Users_Id','=','sp_user_categories.SP_User_Id')
		->join('SP_Service_Categories','SP_Service_Categories.id','=','sp_user_categories.Service_Id')
		//->where('SP_Service_Categories.id',29)
		->select()->get();

		return view('index')->with('sp_info',$sp_info);

	}

	public function map()
	{
		
		return view('map');

	}

	public function sp_name($id,$service_id)
	{



		/*$service_info = \DB::table('SP_Service_Categories')
		->where('SP_Service_Categories.id',$service_id)
		->select()->get();*/

		$sp_service_info = \DB::table('sp_service_info')
		->where('sp_service_info.SP_Users_Id',$id)
		->select()->get();

		$sp_other_services = \DB::table('SP_Service_Categories')
		->join('sp_user_categories','sp_user_categories.Service_Id','=','SP_Service_Categories.id')
		->where('sp_user_categories.SP_User_Id',$id)
		->select()->get();


print_R($service_info);
exit;

		return view('sp_name')->with('sp_name_info',$sp_name_info);

	}
	
	public function sign_up()
	{
		
		return view('sign_up');

	}

	public function search()
	{
		return 'search';	
	}
	public function Post_Search()
	{
		return Input::all();	
	}

	public function sign_up_sp()
	{
		

		$All_Categories = \DB::table('SP_Service_Categories')->select()->get();
		return view('sign_up_sp')->with('All_Categories',$All_Categories);

	}

	public function Post_sign_up_sp()
	{


//return \Input::all();

		$All_Categories = \DB::table('SP_Service_Categories')->select()->get();
		if(\Input::get('final_submit')=='sub_category')
		{
			$Sub_Categories = \Input::get('category');
			
			\Session::put('main_category', \Input::get('category'));
			
			return view('sign_up_sp')->with('Sub_Categories',$Sub_Categories)->with('All_Categories',$All_Categories);
		}		
		

		$Insert_users_info = \DB::table('sp_users_info')->insertGetId(array('Name'=>\Input::get('firstName').' '.\Input::get('lastName'),'Email'=>\Input::get('email'),'Password'=>\Hash::make(\Input::get('password')),'Mobile_Number'=>\Input::get('phone'),'SP'=>'1','Active'=>'0','Updated_At'=>\Carbon\Carbon::now()));
		if(\Input::get('service_type'))
		{
			$service_type = \Input::get('service_type');
			foreach($service_type as $key=>$value)
			{
				$Insert_service_info = \DB::table('sp_service_info')->insertGetId(
				array('SP_Users_Id'=>$Insert_users_info,'Service_Type'=>$value,'Address'=>\Input::get('unit').' '.\Input::get('street').' '.\Input::get('city'),'Postal_Code'=>\Input::get('zip'),'Privacy'=>\Input::get('privacy'),'Business_Name'=>\Input::get('Business_Name'),'Business_website'=>\Input::get('Business_website'),'Business_description'=>\Input::get('Business_description'),
				'Updated_At'=>\Carbon\Carbon::now()));

			}
		}
		
		
		if(\Input::get('other_Sub_Categories_values')!='')
		{
			$Service_categories = array('Service'=>\Input::get('other_Sub_Categories_values'),'Description'=>\Input::get('other_Sub_Categories_values'),'Parent_Id'=>\Session::get('main_category'),'User_Created'=>'1');
			$Insert_new_categories = \DB::table('SP_Service_Categories')->insertGetId($Service_categories);	
			$Insert_user_category = \DB::table('sp_user_categories')->insert(array('SP_User_Id'=>$Insert_users_info,'Service_Id'=>$Insert_new_categories,'Price'=>\Input::get('other_Sub_Categories_price')));	
		}
		
		if(\Input::get('Sub_Categories_values'))
		{
			$Sub_Categories_values = \Input::get('Sub_Categories_values');
			$Sub_Categories_price = \Input::get('Sub_Categories_price');
			
			//exit;

			foreach($Sub_Categories_values as $key=>$value)
			{
				$Insert_new_user_category = \DB::table('sp_user_categories')->insert(array('SP_User_Id'=>$Insert_users_info,'Service_Id'=>$value,'Price'=>$Sub_Categories_price[$key]));	
			
			}
			
		}


		return view('login');

	}

	public function sign_up_user()
	{
		
		return view('sign_up_user');

	}

	public function Post_sign_up_user()
	{
		
		$Member = '0';
		$Active_account = '1';	

		if(\Input::get('member')=='sp')
		{
			$Member = '1';
			$Active_account = '0';	
		}

		$Register_data = array(
		'Name'=>(\Input::get('usr_first_name').' '.\Input::get('usr_last_name')),
		'Email'=>strtolower(\Input::get('usr_email')),
		'Password'=>\Hash::make(\Input::get('usr_password')),
		'Mobile_Number'=>\Input::get('usr_number'),
		'SP'=>$Member,
		'Active'=>$Active_account,
		'Updated_At'=>\Carbon\Carbon::now()
		);

		$Insert_data = \DB::table('Sp_Users_Info')->insertGetId($Register_data);
		echo $Insert_data;
		exit;
		if($Insert_data)
		{
			return redirect('sign-up-user')->with('notification','User registration successfull');
		}
		else
		{
			return redirect('sign-up-user')->with('notification','Can not register User at this time');
		}

	}

	public function login()
	{
		return view('login');
	}
	
	public function reset_password()
	{
		return view('reset_password');
	}
	
	public function PostLogin()
	{
		$rules=array(
			'username'=>'required',
			'password'=>'required|alphaNum|min:3'
			);
		$validator=Validator::make(Input::all(),$rules);

		if($validator->fails()){
			return Redirect::to('/')
			->withErrors($validator)
			->withInput(Input::except('password'));	

		}
		else
		{
				$userdata=array(
					'UserID'=>Input::get('username'),
					'password'=>Input::get('password')
					);

			if(Auth::attempt($userdata))
			{

				$id = DB::table('M005_SupervisoryPanelUserInformation')
						 ->where('UserID', '=', Input::get('username'))
						 ->select('id')
						 ->get();	
			
				Session::put('id', $id[0]->id);
				Session::put('log_nick_name', Input::get('username'));
				Log::info('User  '.Session::get('log_nick_name').'  has Logged in');
				return Redirect::intended('sys_config');
			}
				
			else{

				return Redirect::to('login')->with('logout_message','Incorrect username or password,Please check correctly.');
			}

		}
	}

}
