<?php
namespace parser\models;
use \Illuminate\Database\Eloquent\Model;
class Parse extends Model
{
	protected $table = 'parseSites';
	protected $primaryKey = 'id';
	public $timestamps = false;
	protected $fillable = [
		'id',
		'url',
		'name',
		'uid',
		'visitors',
		'views',
		'popularity',
	];
}