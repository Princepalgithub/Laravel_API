<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Hash;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    public function createUser(Request $request)
    {
    
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'address' => 'required|string',
            'latitude' => 'required|string',
            'longitude' => 'required|string',
        ]);
    
        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 422);
        }
          $email=User::where('email',$request->email)->first();
          if($email){
          return response()->json([
            'status_code' => 422,
            'message' => 'Email Already Exit',
        ]);
    }
        // Create the user
        $user =([
            'name' => $request['name'],
            'email' => $request['email'],
            'password' => Hash::make($request['password']),
            'address' => $request['address'],
            'latitude' => $request['latitude'],
            'longitude' => $request['longitude'],
            'status' => 'active',
            'register_at' => now(),
            'token' => Str::random(60),
        ]);
        User::insert($user);
        return response()->json([
            'status_code' => 200,
            'message' => 'User created successfully',
            'data' => $user
        ]);
    }

    public function changeUserStatus(Request $request)
    {
    
       
        $all_data = User::select(DB::raw('count(status) as count, status'))
        ->groupBy('status')
        ->get();
        
        if(count($all_data) > 0){

            if(($all_data[0]->status=='inactive' && $all_data[0]->count==5)){
                User::where('status', $all_data[0]->status)->update(['status' => 'active']);
            } else if ($all_data[0]->status=='active' && $all_data[0]->count==10){
                User::where('status', $all_data[0]->status)->update(['status' => 'Inactive']);
            }  
        }
        
           if(count($all_data) > 1){
            
            if(($all_data[1]->status=='inactive' && $all_data[1]->count==5)){
                User::where('status', $all_data[1]->status)->update(['status' => 'active']);
            } else if ($all_data[1]->status=='active' && $all_data[1]->count==10){
                User::where('status', $all_data[1]->status)->update(['status' => 'Inactive']);
            }  
        }
    
        return response()->json([
            'status_code' => 200,
            'message' => 'User statuses updated successfully'
        ]);
    }


   
    public function getDistance(Request $request)
    {
       $token= $request->header('Authorization');
          $val=explode(' ',$token);
        //   dd($val[1]);
        // $validatedData = $request->validate([
        //     'destination_latitude' => 'required|numeric',
        //     'destination_longitude' => 'required|numeric',
        // ]);

        $user = User::where('token',  $val[1])->first();
 
        // Calculate the distance
        $distance = $this->calculateDistance($user->latitude, $user->longitude, $user['destination_latitude'], $user['destination_longitude']);

        return response()->json([
            'status_code' => 200,
            'message' => 'Distance calculated successfully',
            'distance' => $distance . ' km'
        ]);
    }

    private function calculateDistance($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371; 

        $latDelta = deg2rad($lat2 - $lat1);
        $lonDelta = deg2rad($lon2 - $lon1);

        $a = sin($latDelta / 2) * sin($latDelta / 2) +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            sin($lonDelta / 2) * sin($lonDelta / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        $distance = $earthRadius * $c;

        return round($distance, 2); 
    }

public function getUserListing(Request $request)
    {
       
        $request->validate([
            'week_number' => 'required|array|min:1',
            'week_number.*' => 'integer|min:0|max:6',
        ]);
        
        $daysOfWeek = [
            0 => 'sunday',
            1 => 'monday',
            2 => 'tuesday',
            3 => 'wednesday',
            4 => 'thursday',
            5 => 'friday',
            6 => 'saturday',
        ];
        
        $data = [];
        
        $count = count($request->week_number);
       
        for ($i = 0; $i < $count; $i++) {
           // if (isset($daysOfWeek[$request->week_number[$i]])) {
                $dayName = $daysOfWeek[$request->week_number[$i]];
                $users = User::whereRaw('EXTRACT(DOW FROM register_at) = ?', [$request->week_number[$i]])
                    ->select('name', 'email')
                    ->get()
                    ->toArray();
                    

                $data[$dayName] = $users;
           // }
        }
        
        return response()->json([
            'status_code' => 200,
            'message' => 'User listing fetched successfully',
            'data' => $data,
        ]);
        
    
    }
}