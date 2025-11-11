<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\MLForecastService;
use Illuminate\Support\Facades\File;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class SetupMLForecast extends Command
{
    protected $signature = 'ml:setup {--force : Force reinstall even if already set up}';
    protected $description = 'Set up ML forecasting environment and dependencies';

    protected $forecastService;

    public function __construct(MLForecastService $forecastService)
    {
        parent::__construct();
        $this->forecastService = $forecastService;
    }

    public function handle()
    {
        $this->info('🔧 Setting up ML Forecasting Environment...');

        // Check if already set up
        if (!$this->option('force') && $this->isAlreadySetup()) {
            $this->info('✅ ML environment already set up. Use --force to reinstall.');
            return 0;
        }

        try {
            // Step 1: Check Python
            if (!$this->checkPython()) {
                return 1;
            }

            // Step 2: Create directories
            $this->createDirectories();

            // Step 3: Install Python dependencies
            if (!$this->installPythonDependencies()) {
                return 1;
            }

            // Step 4: Test the setup
            if (!$this->testSetup()) {
                return 1;
            }

            // Step 5: Create setup marker
            $this->createSetupMarker();

            $this->info('✅ ML forecasting environment setup complete!');
            $this->info('💡 The model will automatically train when first accessed.');

            return 0;

        } catch (\Exception $e) {
            $this->error('❌ Setup failed: ' . $e->getMessage());
            return 1;
        }
    }

    protected function isAlreadySetup(): bool
    {
        return File::exists(base_path('.ml_setup_complete'));
    }

    protected function checkPython(): bool
    {
        $this->info('🐍 Checking Python installation...');

        $pythonCommands = ['python', 'python3'];
        
        foreach ($pythonCommands as $cmd) {
            $process = new Process([$cmd, '--version']);
            $process->run();

            if ($process->isSuccessful()) {
                $version = trim($process->getOutput());
                $this->info("✅ Found Python: $version");
                
                // Update config to use the working Python command
                $this->updatePythonPath($cmd);
                return true;
            }
        }

        $this->error('❌ Python not found. Please install Python 3.8+ first.');
        $this->error('   Download from: https://www.python.org/downloads/');
        return false;
    }

    protected function createDirectories(): void
    {
        $this->info('📁 Creating directories...');
        
        $dirs = [
            base_path('ml_models'),
            base_path('ml_models/venv'),
        ];

        foreach ($dirs as $dir) {
            if (!File::exists($dir)) {
                File::makeDirectory($dir, 0755, true);
            }
        }
    }

    protected function installPythonDependencies(): bool
    {
        $this->info('📦 Installing Python dependencies...');

        $pythonPath = config('app.python_path', 'python');
        $basePath = base_path();
        $requirementsPath = base_path('ml_models/requirements.txt');

        // Create virtual environment
        $this->info('Creating virtual environment...');
        $venvPath = base_path('ml_models/venv');
        
        $process = new Process([$pythonPath, '-m', 'venv', $venvPath]);
        $process->setTimeout(300); // 5 minutes timeout
        $process->run();

        if (!$process->isSuccessful()) {
            $this->error('Failed to create virtual environment: ' . $process->getErrorOutput());
            return false;
        }

        // Get pip path for the virtual environment
        $pipPath = $this->getVenvPipPath();
        
        // Upgrade pip
        $this->info('Upgrading pip...');
        $process = new Process([$pipPath, 'install', '--upgrade', 'pip']);
        $process->setTimeout(300);
        $process->run();

        if (!$process->isSuccessful()) {
            $this->warn('Warning: Could not upgrade pip: ' . $process->getErrorOutput());
        }

        // Install requirements
        $this->info('Installing ML packages (this may take a few minutes)...');
        $process = new Process([$pipPath, 'install', '-r', $requirementsPath]);
        $process->setTimeout(600); // 10 minutes timeout for Prophet installation
        $process->run();

        if (!$process->isSuccessful()) {
            $this->error('Failed to install requirements: ' . $process->getErrorOutput());
            return false;
        }

        return true;
    }

    protected function testSetup(): bool
    {
        $this->info('🧪 Testing ML model setup...');

        $status = $this->forecastService->getModelStatus();
        
        if ($status['success']) {
            $this->info('✅ ML model script is working correctly.');
            return true;
        } else {
            $this->error('❌ ML model test failed: ' . ($status['error'] ?? 'Unknown error'));
            return false;
        }
    }

    protected function createSetupMarker(): void
    {
        File::put(base_path('.ml_setup_complete'), json_encode([
            'setup_at' => now()->toISOString(),
            'python_path' => config('app.python_path'),
            'version' => '1.0'
        ]));
    }

    protected function updatePythonPath(string $pythonCmd): void
    {
        // Update the runtime config
        config(['app.python_path' => $pythonCmd]);

        // Also try to update .env file if it exists
        $envPath = base_path('.env');
        if (File::exists($envPath)) {
            $envContent = File::get($envPath);
            
            if (strpos($envContent, 'PYTHON_PATH=') !== false) {
                $envContent = preg_replace('/PYTHON_PATH=.*/', "PYTHON_PATH=$pythonCmd", $envContent);
            } else {
                $envContent .= "\nPYTHON_PATH=$pythonCmd\n";
            }
            
            File::put($envPath, $envContent);
        }
    }

    protected function getVenvPipPath(): string
    {
        $venvPath = base_path('ml_models/venv');
        
        if (PHP_OS_FAMILY === 'Windows') {
            return $venvPath . '/Scripts/pip.exe';
        } else {
            return $venvPath . '/bin/pip';
        }
    }
}