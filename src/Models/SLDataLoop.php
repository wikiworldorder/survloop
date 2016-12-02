<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SLDataLoop extends Model
{
	protected $table 		= 'SL_DataLoop';
	protected $primaryKey 	= 'DataLoopID';
	public $timestamps 		= true;
	protected $fillable 	= 
	[	
		'DataLoopTree', 
		'DataLoopRoot', 
		'DataLoopPlural', 
		'DataLoopSingular', 
		'DataLoopTable', 
		'DataLoopSortFld', 
		'DataLoopDoneFld', 
		'DataLoopMaxLimit', 
		'DataLoopWarnLimit', 
		'DataLoopMinLimit', 
		'DataLoopIsStep', 
		'DataLoopAutoGen', 
	];
	
	
	public $conds = array();
	
	public function loadConds()
	{
		$this->conds = array();
		$getConds = SLConditionsNodes::where('CondNodeLoopID', $this->DataLoopID)
			->get();
		if ($getConds && sizeof($getConds) > 0)
		{
			foreach ($getConds as $c) 
			{
				$cond = SLConditions::find($c->CondNodeCondID);
				$cond->loadVals();
				$this->conds[] = $cond;
			}
		}
		return true;
	}
	
}
