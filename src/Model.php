<?php namespace Psychic;

class Model {
	
	public static function all()
	{
		return \App::make('psychic.em')
			->createQueryBuilder()
			->select('a')
			->from(get_called_class(), 'a')
			->getQuery()
			->getResult();
	}
	
	public static function find($id)
	{
		return \App::make('psychic.em')
			->find(get_called_class(), $id);
	}
	
}