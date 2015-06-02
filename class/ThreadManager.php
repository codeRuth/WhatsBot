<?php
	require_once 'Lib/_Loader.php';

	class ThreadManager
	{
		private $Threads = array();

		private $Enabled = false;

		public function LoadThreads()
		{
			$Config = Config::Get('Threads');

			if(is_array($Config) && isset($Config['Enabled']) && isset($Config['Threads']) && is_array($Config['Threads']))
			{
				if($Config['Enabled'])
				{
					Std::Out();
					Std::Out('[Info] [Threads] Loading');

					if(extension_loaded('pthreads'))
					{
						$this->Enabled = true;

						require_once 'Thread.php';

						$Loaded = array();

						foreach($Config['Threads'] as $Thread)
							$Loaded['Thread'] = $this->LoadThread($Thread);

						Std::Out('[Info] [Threads] Ready!');

						return $Loaded;
					}
					else
						Std::Out("[Warning] [Threads] Can't load treads. PThreads extension is not installed");
				}
			}
			else
			{
				Std::Out();
				Std::Out('[Warning] [Threads] Config error');
			}

			return false;
		}

		private function LoadThread($Name)
		{
			$this->Threads[$Name] = new WhatsBotThread($Name);

			$Loaded = $this->Threads[$Name]->IsLoaded();

			if(!$Loaded)
				$this->UnloadThread($Name);

			return $Loaded;
		}

		private function UnloadThread($Name)
		{
			if(isset($this->Threads[$Name]))
			{
				unset($this->Threads[$Name]);

				return true;
			}

			return false;
		}

		public function StartThreads()
		{
			if($this->Enabled)
			{
				Std::Out();
				Std::Out('[Info] [Threads] Starting');
			}

			$Threads = array_keys($this->Threads);

			foreach($Threads as $Thread)
				$this->StartThread($Thread, false);

			if($this->Enabled)
				Std::Out('[Info] [Threads] Running!');
		}

		public function StartThread($Name, $Show = true)
		{
			if($Show)
			{
				Std::Out();
				Std::Out("[Info] [Threads] Starting {$Name}");
			}

			if(!empty($this->Threads[$Name]) && $this->Threads[$Name] instanceof WhatsBotThread)
			{
				$this->Threads[$Name]->Start(PTHREADS_INHERIT_ALL | PTHREADS_ALLOW_HEADERS | PTHREADS_ALLOW_GLOBALS);

				if($Show)
					Std::Out("[Info] [Threads] {$Name} started");

				return true;
			}

			if($Show)
				Std::Out("[Warning] [Threads] Can't start {$Name}. That thread doesn't exists");

			return false;
		}

		public function StopThreads($Code = 'Exiting...')
		{
			Std::Out();
			Std::Out('[Info] [Threads] Stopping');

			$Threads = array_keys($this->Threads);

			foreach($Threads as $Thread)
				$this->StopThread($Thread, $Code);

			Std::Out('[Info] [Threads] Stopped!');
		}

		public function StopThread($Name, $Code = 'Exiting...')
		{
			if(isset($this->Threads[$Name]))
			{
				$this->Threads[$Name]->_Exit($Code);

				return true;
			}

			Std::Out("[Warning] [Threads] Can't stop {$Name}. That thread doesn't exists");

			return false;
		}
	}