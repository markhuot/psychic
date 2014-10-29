<?php namespace Exchanger;

use \Illuminate\Support\ServiceProvider;
use \Doctrine\Common\Annotations\AnnotationRegistry;

class LaravelServiceProvider extends ServiceProvider {
	
	/**
	 * Indicates if loading of the provider is deferred.
	 *
	 * @var bool
	 */
	protected $defer = false;

	/**
	 * Bootstrap the application events.
	 *
	 * @return void
	 */
	public function boot()
	{
		
	}

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
		AnnotationRegistry::registerLoader(new AnnotationComposerLoader([
			'Doctrine\ORM\Mapping\\'
		]));
		
		$this->bindConnection();
		$this->bindEntityManager();
		
		if ($this->app['config']->get('exchanger.fluid')) {
			$this->syncSchema();
		}
	}
	
	/**
	 * Connects to the database using the Laravel defaults
	 *
	 * @return void
	 */
	private function bindConnection()
	{
		$this->app->singleton('doctrine.conn', function()
		{
			$defaultConnection = $this->app['config']->get('database.default');
			$connections = $this->app['config']->get('database.connections');
			$connection = $connections[$defaultConnection];
			
			$config = new \Doctrine\DBAL\Configuration();
			$connectionParams = array(
					'dbname' => $connection['database'],
					'user' => $connection['username'],
					'password' => $connection['password'],
					'host' => $connection['host'],
					'driver' => 'pdo_mysql',
			);
			return \Doctrine\DBAL\DriverManager::getConnection($connectionParams, $config);
		});
	}

	/**
	 * Create the entity manager with the associated Laravel models.
	 *
	 * @return void
	 */
	private function bindEntityManager()
	{
		$this->app->singleton('doctrine.em', function()
		{
			$inDebugMode = $this->app->make('config')->get('debug');
			$conn = $this->app->make('doctrine.conn');
			$proxyDir = null;
			$cache = null;
			$useSimpleAnnotationReader = false;
			$config = \Doctrine\ORM\Tools\Setup::createAnnotationMetadataConfiguration([app_path('models')], $inDebugMode, $proxyDir, $cache, $useSimpleAnnotationReader);
			return \Doctrine\ORM\EntityManager::create($conn, $config);
		});
	}
	
	/**
	 * Sync the schema so the database matches the model annotations
	 *
	 * @return void
	 */
	private function syncSchema()
	{
		$schema = new Schema($this->app->make('doctrine.conn'));
		$schema->sync(\app_path('models'));
	}
	
}