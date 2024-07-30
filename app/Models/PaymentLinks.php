<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PaymentLinks extends Model
{
    use HasFactory;
	//protected $table="";
	
	public static function getExportData($startDate="", $endDate="")
    {
		$user = DB::table('users')
			->where('id', Auth::id())
			->first();
			
        $query = self::query()
			->select(
				'id', 
				'agent_id', 
				'customer_name',
				'customer_email', 
				'customer_phone', 
				'amount', 
				'currency', 
				'description', 
				'payment_link',
				'payment_id',
				'payment_status',
				'created_at',
				'updated_at'
				)
            ->whereBetween('created_at', [$startDate, $endDate]);
			
		if($user && $user->role =="agent"){
			$query->where('agent_id', $user->id);
		}
			
        $payments = $query->get();
		
		return $payments;
    }
	
	public function formattedCreatedAt()
    {
        return $this->created_at->format('dd-mm-Y H:i:s');
    }
	
	public static function getTodaysAmount(): float
    {
        return self::whereDate('created_at', Carbon::today())->sum('amount');
    }
	
	public static function getCustomerCount($adminId =""){
		if($adminId){
			return DB::table('payment_links')
			->join('users as agents', 'payment_links.agent_id', '=', 'agents.id')
			->where('agents.added_by', $adminId)
			->select('payment_links.*')
			->get()->count();
			
			//return $payment->count();
		}else{
			return null;
		}
	}
	
	public static function getCollection($adminId =""){
		if($adminId){
			return DB::table('payment_links')
			->join('users as agents', 'payment_links.agent_id', '=', 'agents.id')
			->where('agents.added_by', $adminId)
			->select('payment_links.*')
			->get()->sum('amount');
			
			//return $payment->count();
		}else{
			return null;
		}
	}
	
	
	
}
