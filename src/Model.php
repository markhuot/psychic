<?php namespace Exchanger;

class Model {
	
	public static function all()
	{
		return \App::make('doctrine.em')
			->createQueryBuilder()
			->select('c')
			->from('Component', 'c')
			->getQuery()
			->getResult();
	}
	
	public static function find($id)
	{
		return \App::make('doctrine.em')
			->find(get_called_class(), $id);
	}
	
}