<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Artisan;

class MLForecastServiceProvider extends ServiceProvider
{
    public function register()
    {
        // Register the MLForecastService
        $this->app->singleton(\App\Services\MLForecastService::class);
    }

    public function boot()
    {
        // Only auto-setup in local environment to avoid issues in production
        if ($this->app->environment('local')) {
            $this->autoSetupIfNeeded();
        }
    }

    protected function autoSetupIfNeeded()
    {
        // Check if we're in a web request or console command
        if ($this->app->runningInConsole() && $this->isRelevantConsoleCommand()) {
            return; // Skip auto-setup for non-relevant console commands
        }

        // Check if setup is needed
        if (!$this->isMLSetupComplete()) {
            try {
                Log::info('Auto-setting up ML forecasting environment...');
                
                // Run setup in background to avoid blocking the application
                $this->runSetupInBackground();
                
            } catch (\Exception $e) {
                Log::error('Auto ML setup failed: ' . $e->getMessage());
            }
        }
    }

    protected function isMLSetupComplete(): bool
    {
        $markerFile = base_path('.ml_setup_complete');
        
        if (!File::exists($markerFile)) {
            return false;
        }

        // Check if Python dependencies are actually installed
        $venvPath = base_path('ml_models/venv');
        if (!File::exists($venvPath)) {
            return false;
        }

        // Check if requirements are installed
        $pythonPath = $this->getVenvPythonPath();
        if (!File::exists($pythonPath)) {
            return false;
        }

        return true;
    }

    protected function isRelevantConsoleCommand(): bool
    {
        // Only auto-setup for relevant commands
        $relevantCommands = [
            'serve',
            'tinker',
            'queue:work',
            'schedule:run'
        ];

        $currentCommand = $_SERVER['argv'][1] ?? '';
        
        return in_array($currentCommand, $relevantCommands);
    }

    protected function runSetupInBackground(): void
    {
        // Create a flag to prevent multiple simultaneous setups
        $lockFile = base_path('.ml_setup_running');
        
        if (File::exists($lockFile)) {
            return; // Setup already running
        }

        File::put($lockFile, time());

        try {
            // Run the setup command
            Artisan::call('ml:setup');
            
            Log::info('ML setup completed successfully');
            
        } catch (\Exception $e) {
            Log::error('Background ML setup failed: ' . $e->getMessage());
        } finally {
            // Remove lock file
            if (File::exists($lockFile)) {
                File::delete($lockFile);
            }
        }
    }

    protected function getVenvPythonPath(): string
    {
        $venvPath = base_path('ml_models/venv');
        
        if (PHP_OS_FAMILY === 'Windows') {
            return $venvPath . '/Scripts/python.exe';
        } else {
            return $venvPath . '/bin/python';
        }
    }
}